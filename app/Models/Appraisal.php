<?php

namespace App\Models;

use App\Enums\AppraisalStatus;
use App\Enums\CompletionMode;
use App\Enums\OverallRating;
use App\Enums\TargetType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'reviewer_id', 'year', 'appraisal_date',
    'self_reflection', 'reviewer_comments', 'overall_rating',
    'next_review_date', 'status', 'completion_mode', 'employee_signed_at', 'reviewer_signed_at',
])]
class Appraisal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'appraisal_date' => 'date',
            'overall_rating' => OverallRating::class,
            'next_review_date' => 'date',
            'status' => AppraisalStatus::class,
            'completion_mode' => CompletionMode::class,
            'employee_signed_at' => 'datetime',
            'reviewer_signed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(AppraisalTarget::class);
    }

    public function currentTargets(): HasMany
    {
        return $this->targets()->where('target_type', TargetType::Current)->orderBy('target_number');
    }

    public function nextYearTargets(): HasMany
    {
        return $this->targets()->where('target_type', TargetType::NextYear)->orderBy('target_number');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function professionalDevelopment(): HasMany
    {
        return $this->hasMany(ProfessionalDevelopment::class)->orderBy('activity_date');
    }

    public function isFullySigned(): bool
    {
        return $this->employee_signed_at !== null && $this->reviewer_signed_at !== null;
    }
}
