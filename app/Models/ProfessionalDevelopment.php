<?php

namespace App\Models;

use App\Enums\PdNature;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'appraisal_id', 'activity_name', 'nature', 'provider',
    'impact_on_practice', 'hours', 'activity_date',
])]
class ProfessionalDevelopment extends Model
{
    use HasFactory;

    protected $table = 'professional_development';

    protected function casts(): array
    {
        return [
            'nature' => PdNature::class,
            'hours' => 'decimal:2',
            'activity_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'pd_record_id');
    }
}
