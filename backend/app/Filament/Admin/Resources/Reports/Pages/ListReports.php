<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Reports\Pages;

use App\Enums\ReportStatus;
use App\Filament\Admin\Resources\Reports\ReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'open' => Tab::make('مفتوحة')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    ReportStatus::New->value,
                    ReportStatus::Reviewing->value,
                ]))
                ->badge(fn () => $this->countOpen()),
            'resolved' => Tab::make('مُعالَجة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportStatus::Resolved->value)),
            'dismissed' => Tab::make('مرفوضة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportStatus::Dismissed->value)),
            'all' => Tab::make('الكل'),
        ];
    }

    private function countOpen(): ?int
    {
        $count = $this->getModel()::query()
            ->whereIn('status', [ReportStatus::New->value, ReportStatus::Reviewing->value])
            ->count();

        return $count > 0 ? $count : null;
    }
}
