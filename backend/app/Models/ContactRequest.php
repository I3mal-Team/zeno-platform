<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $topic
 * @property string $message
 * @property string|null $ip_address
 * @property string $status
 * @property int|null $handled_by_admin_id
 * @property Carbon|null $handled_at
 * @property Carbon $created_at
 */
class ContactRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'topic', 'message', 'ip_address',
        'status', 'handled_by_admin_id', 'handled_at',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    /** @return BelongsTo<Admin, $this> */
    public function handledByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by_admin_id');
    }
}
