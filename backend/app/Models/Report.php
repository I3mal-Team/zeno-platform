<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $reporter_user_id
 * @property ReportTarget $target_type
 * @property int $target_id
 * @property int $report_reason_id
 * @property string|null $details
 * @property ReportStatus $status
 * @property int|null $resolved_by_admin_id
 * @property string|null $resolution_note
 * @property Carbon|null $resolved_at
 */
final class Report extends Model
{
    protected $fillable = [
        'reporter_user_id', 'target_type', 'target_id', 'report_reason_id',
        'details', 'status', 'resolved_by_admin_id', 'resolution_note', 'resolved_at',
    ];

    protected $attributes = ['status' => 'new'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_type' => ReportTarget::class,
            'status' => ReportStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** @return BelongsTo<ReportReason, $this> */
    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'report_reason_id');
    }

    /** @return BelongsTo<Admin, $this> */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }

    /**
     * The reported thing itself. Not a polymorphic relation: target_type holds
     * a short domain token rather than a class name, so the mapping stays here
     * and the column keeps meaning if a model is ever renamed.
     */
    public function target(): ?Model
    {
        return match ($this->target_type) {
            ReportTarget::Job => Job::query()->find($this->target_id),
            ReportTarget::Organization => Organization::query()->find($this->target_id),
            ReportTarget::User => User::query()->find($this->target_id),
            ReportTarget::Message => Message::query()->find($this->target_id),
        };
    }
}
