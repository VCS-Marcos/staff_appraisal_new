<?php

namespace App\Models;

use App\Enums\CycleTerm;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'term', 'start_date', 'end_date', 'is_active'])]
class AppraisalCycle extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'term' => CycleTerm::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class, 'cycle_id');
    }

    public function professionalDevelopment(): HasMany
    {
        return $this->hasMany(ProfessionalDevelopment::class, 'cycle_id');
    }
}
