<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VerificationRequests\Pages;

use App\Enums\VerificationRequestStatus;
use App\Filament\Admin\Resources\VerificationRequests\VerificationRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVerificationRequests extends ListRecords
{
    protected static string $resource = VerificationRequestResource::class;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('قيد المراجعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VerificationRequestStatus::Pending->value))
                ->badge(fn () => $this->countStatus(VerificationRequestStatus::Pending)),
            'approved' => Tab::make('مقبولة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VerificationRequestStatus::Approved->value)),
            'rejected' => Tab::make('مرفوضة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VerificationRequestStatus::Rejected->value)),
            'all' => Tab::make('الكل'),
        ];
    }

    private function countStatus(VerificationRequestStatus $status): ?int
    {
        $count = $this->getModel()::query()->where('status', $status->value)->count();

        return $count > 0 ? $count : null;
    }
}
