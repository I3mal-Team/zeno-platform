<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContactRequests\Pages;

use App\Filament\Admin\Resources\ContactRequests\ContactRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactRequests extends ListRecords
{
    protected static string $resource = ContactRequestResource::class;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('جديدة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge(fn () => $this->countNew()),
            'handled' => Tab::make('مُعالَجة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'handled')),
            'all' => Tab::make('الكل'),
        ];
    }

    private function countNew(): ?int
    {
        $count = $this->getModel()::query()->where('status', 'new')->count();

        return $count > 0 ? $count : null;
    }
}
