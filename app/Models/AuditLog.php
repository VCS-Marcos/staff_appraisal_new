<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'action', 'entity_type', 'entity_id', 'description', 'ip_address'])]
class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit-trail entry for the currently authenticated user.
     * No-ops for unauthenticated contexts (console, seeders, queue workers).
     */
    public static function record(string $action, Model $entity, ?string $description = null): void
    {
        if (! auth()->check()) {
            return;
        }

        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
