<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Applications\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Admin\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('جديدة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::Submitted->value))
                ->badge(fn () => $this->countStatus(ApplicationStatus::Submitted->value)),
            'review' => Tab::make('قيد المراجعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::Review->value)),
            'accepted' => Tab::make('مقبولة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::Accepted->value)),
            'rejected' => Tab::make('مرفوضة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::Rejected->value)),
            'all' => Tab::make('الكل'),
        ];
    }

    private function countStatus(string $status): ?int
    {
        $count = $this->getModel()::query()->where('status', $status)->count();

        return $count > 0 ? $count : null;
    }
}
