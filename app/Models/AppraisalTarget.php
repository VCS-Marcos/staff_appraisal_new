<?php

namespace App\Models;

use App\Enums\TargetMet;
use App\Enums\TargetType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appraisal_id', 'target_type', 'target_number', 'target_text',
    'target_met', 'comments', 'action_text', 'success_criteria',
])]
class AppraisalTarget extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'target_type' => TargetType::class,
            'target_number' => 'integer',
            'target_met' => TargetMet::class,
        ];
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }
}
