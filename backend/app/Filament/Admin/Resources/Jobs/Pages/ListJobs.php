<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Jobs\Pages;

use App\Filament\Admin\Resources\Jobs\JobResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('قيد المراجعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_review'))
                ->badge(fn () => $this->countStatus('pending_review')),
            'active' => Tab::make('نشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'all' => Tab::make('الكل'),
        ];
    }

    private function countStatus(string $status): ?int
    {
        $count = $this->getModel()::query()->where('status', $status)->count();

        return $count > 0 ? $count : null;
    }
}
