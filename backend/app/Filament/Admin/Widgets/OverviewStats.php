<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Application;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected ?string $heading = 'نظرة عامة';

    protected static ?int $sort = -3;

    // Render with the page rather than lazy-loading, so the numbers are visible
    // the instant the dashboard opens.
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $newApplications = Application::query()->where('status', 'submitted')->count();

        return [
            Stat::make('الباحثون عن عمل', (string) User::query()->where('role', 'candidate')->count())
                ->description('حسابات باحثين')
                ->descriptionIcon('heroicon-o-user')
                ->color('warning'),

            Stat::make('المنشآت', (string) Organization::query()->count())
                ->description('أصحاب العمل')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('info'),

            Stat::make('الوظائف النشطة', (string) Job::query()->where('status', 'active')->count())
                ->description('منشورة الآن')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('success'),

            Stat::make('التقديمات', (string) Application::query()->count())
                ->description($newApplications > 0 ? "{$newApplications} جديدة" : 'إجمالي التقديمات')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
        ];
    }
}
