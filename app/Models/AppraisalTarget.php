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
    'target_met', 'comments', 'reviewer_comments', 'actions',
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
            // List of ['action_text' => string, 'success_criteria' => string] pairs.
            'actions' => 'array',
        ];
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    /**
     * The target's action / success-criteria pairs, always as a clean list.
     *
     * Falls back to the legacy single action_text / success_criteria columns for any row that
     * has not been converted yet, so older data still displays.
     *
     * @return list<array{action_text: string, success_criteria: string}>
     */
    public function actionPairs(): array
    {
        if (is_array($this->actions)) {
            return collect($this->actions)
                ->filter(fn ($pair) => is_array($pair))
                ->map(fn ($pair) => [
                    'action_text' => (string) ($pair['action_text'] ?? ''),
                    'success_criteria' => (string) ($pair['success_criteria'] ?? ''),
                ])
                ->values()
                ->all();
        }

        $action = (string) ($this->attributes['action_text'] ?? '');
        $criteria = (string) ($this->attributes['success_criteria'] ?? '');

        return trim($action) === '' && trim($criteria) === ''
            ? []
            : [['action_text' => $action, 'success_criteria' => $criteria]];
    }

    /**
     * Clean the action pairs from a submitted target row: drop pairs where both fields are blank and
     * trim the rest. Also accepts the legacy single action_text / success_criteria fields, so a form
     * that was open before the multi-action update still saves correctly.
     *
     * @param  array<string, mixed>  $input  one target's submitted fields
     * @return list<array{action_text: string, success_criteria: string}>
     */
    public static function cleanPairs(array $input): array
    {
        $raw = $input['actions'] ?? null;

        if (! is_array($raw)) {
            $raw = [['action_text' => $input['action_text'] ?? null, 'success_criteria' => $input['success_criteria'] ?? null]];
        }

        return collect($raw)
            ->filter(fn ($pair) => is_array($pair))
            ->map(fn ($pair) => [
                'action_text' => trim((string) ($pair['action_text'] ?? '')),
                'success_criteria' => trim((string) ($pair['success_criteria'] ?? '')),
            ])
            ->filter(fn ($pair) => $pair['action_text'] !== '' || $pair['success_criteria'] !== '')
            ->values()
            ->all();
    }
}
