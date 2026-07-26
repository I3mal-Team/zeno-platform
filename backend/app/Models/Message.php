<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $conversation_id
 * @property int $sender_id
 * @property string $type
 * @property string|null $body
 * @property string $client_uuid
 * @property Carbon $created_at
 */
final class Message extends Model
{
    /** The table carries only created_at (DB default); there is no updated_at. */
    public $timestamps = false;

    protected $fillable = [
        'uuid', 'conversation_id', 'sender_id', 'type', 'body', 'client_uuid',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        // With timestamps off, stamp it ourselves so the model carries the
        // value the DB default would otherwise apply only server-side.
        self::creating(function (Message $message): void {
            $message->created_at ??= now();
        });
    }

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
