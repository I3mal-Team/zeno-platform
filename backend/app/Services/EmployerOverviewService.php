<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\Application;
use App\Models\Organization;
use App\Repositories\ApplicationRepository;
use App\Repositories\JobRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * The numbers behind the employer's overview screen. Deltas compare the last
 * seven days with the seven before them; where the schema keeps no history to
 * compare against — a job's running view counter, for one — the figure is
 * reported without a delta rather than invented.
 */
final class EmployerOverviewService
{
    private const TIMEZONE = 'Asia/Riyadh';

    private const CHART_DAYS = 7;

    public function __construct(
        private readonly ApplicationRepository $applications,
        private readonly JobRepository $jobs,
    ) {}

    /**
     * @return array{
     *     stats: list<array<string, mixed>>,
     *     chart: list<array{day: string, height: int, highlight: bool}>,
     *     chartTotal: int,
     *     chartDelta: int|null,
     *     newApplications: int,
     *     recent: Collection<int, Application>
     * }
     */
    public function for(Organization $organization): array
    {
        $id = $organization->id;
        $weekAgo = Carbon::now()->subDays(self::CHART_DAYS);
        $twoWeeksAgo = Carbon::now()->subDays(self::CHART_DAYS * 2);

        $thisWeek = $this->applications->countForOrganization($id, [], $weekAgo);
        $lastWeek = $this->applications->countForOrganization($id, [], $twoWeeksAgo) - $thisWeek;

        $newApplications = $this->applications->countForOrganization($id, [ApplicationStatus::Submitted->value]);

        ['accepted' => $accepted, 'rejected' => $rejected] = $this->applications->decisionCountsForOrganization($id);
        $decided = $accepted + $rejected;

        $acceptanceShift = $this->acceptanceShift($id, $weekAgo, $twoWeeksAgo);

        $activeJobs = $this->jobs->countForOrganization($id, JobStatus::Active);
        $publishedThisWeek = $this->jobs->countForOrganization($id, JobStatus::Active, $weekAgo);

        return [
            'stats' => [
                [
                    'label' => 'وظائف نشطة', 'value' => (string) $activeJobs, 'icon' => 'briefcase',
                    'bg' => '#FDF1CC', 'fg' => '#8A6D12',
                    'delta' => $publishedThisWeek > 0 ? '+'.$publishedThisWeek : null,
                    'deltaBg' => '#E7F4EC', 'deltaFg' => '#1F8A4D',
                ],
                [
                    'label' => 'طلبات جديدة', 'value' => (string) $newApplications, 'icon' => 'user-1',
                    'bg' => '#E7F4EC', 'fg' => '#1F8A4D',
                    'delta' => $newApplications > 0 ? 'جديد' : null,
                    'deltaBg' => '#FDF3D6', 'deltaFg' => '#8A6D12',
                ],
                [
                    // A view counter only ever moves forward and keeps no
                    // history, so there is nothing honest to compare it with.
                    'label' => 'إجمالي المشاهدات', 'value' => (string) $this->jobs->sumViewsForOrganization($id),
                    'icon' => 'eye', 'bg' => '#E2EEF4', 'fg' => '#2E6E8A', 'delta' => null,
                ],
                [
                    'label' => 'معدل القبول', 'value' => $decided > 0 ? round($accepted / $decided * 100).'%' : '—',
                    'icon' => 'award', 'bg' => '#ECE6F4', 'fg' => '#6A4E8A',
                    'delta' => $acceptanceShift === null ? null : ($acceptanceShift >= 0 ? '+' : '').$acceptanceShift.'%',
                    'deltaBg' => ($acceptanceShift ?? 0) >= 0 ? '#E7F4EC' : '#FBEDEA',
                    'deltaFg' => ($acceptanceShift ?? 0) >= 0 ? '#1F8A4D' : '#B2453A',
                ],
            ],
            'chart' => $this->chart($id),
            'chartTotal' => $thisWeek,
            'chartDelta' => $lastWeek > 0 ? (int) round(($thisWeek - $lastWeek) / $lastWeek * 100) : null,
            'newApplications' => $newApplications,
            'recent' => $this->applications->recentForOrganization($id, 4),
        ];
    }

    /**
     * How many percentage points the acceptance rate moved between the last
     * seven days and the seven before. Null until both windows have a decision
     * to compare — a rate out of nothing is not a rate.
     */
    private function acceptanceShift(int $organizationId, Carbon $weekAgo, Carbon $twoWeeksAgo): ?int
    {
        $current = $this->applications->decisionCountsForOrganization($organizationId, $weekAgo);
        $previous = $this->applications->decisionCountsForOrganization($organizationId, $twoWeeksAgo, $weekAgo);

        $currentTotal = $current['accepted'] + $current['rejected'];
        $previousTotal = $previous['accepted'] + $previous['rejected'];

        if ($currentTotal === 0 || $previousTotal === 0) {
            return null;
        }

        return (int) round(
            $current['accepted'] / $currentTotal * 100 - $previous['accepted'] / $previousTotal * 100,
        );
    }

    /**
     * One bar per day for the last week. Heights are a share of the busiest
     * day so the shape reads the same whether the week saw three applications
     * or three hundred.
     *
     * @return list<array{day: string, height: int, highlight: bool}>
     */
    private function chart(int $organizationId): array
    {
        $start = Carbon::now(self::TIMEZONE)->startOfDay()->subDays(self::CHART_DAYS - 1);
        $counts = $this->applications->dailyCountsForOrganization($organizationId, $start, self::TIMEZONE);
        $peak = max([1, ...array_values($counts)]);

        $bars = [];

        for ($offset = 0; $offset < self::CHART_DAYS; $offset++) {
            $day = $start->copy()->addDays($offset);
            $total = (int) ($counts[$day->format('Y-m-d')] ?? 0);

            $bars[] = [
                'day' => $day->locale('ar')->dayName,
                'height' => (int) round($total / $peak * 100),
                'highlight' => $total > 0 && $total >= $peak * 0.8,
            ];
        }

        return $bars;
    }
}
