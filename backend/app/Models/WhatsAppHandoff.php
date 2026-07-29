<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WhatsAppDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A record that one side left the in-app thread for WhatsApp. Kept because
 * once the conversation moves to WhatsApp the platform can no longer see it —
 * this row is the last thing that ties the two together.
 *
 * @property int $id
 * @property int $application_id
 * @property int $initiated_by_user_id
 * @property WhatsAppDirection $direction
 * @property Carbon|null $consented_at
 * @property Carbon $created_at
 */
final class WhatsAppHandoff extends Model
{
    protected $table = 'whatsapp_handoffs';

    /** The table carries only created_at (DB default); there is no updated_at. */
    public $timestamps = false;

    protected $fillable = ['application_id', 'initiated_by_user_id', 'direction', 'consented_at', 'created_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'direction' => WhatsAppDirection::class,
            'consented_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<User, $this> */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }
}
