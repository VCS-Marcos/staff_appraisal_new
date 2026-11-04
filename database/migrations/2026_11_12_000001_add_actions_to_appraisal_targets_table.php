<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A target can now have several "action to be completed" + "success criteria" pairs, stored as a
     * JSON list on the target: [{"action_text": "...", "success_criteria": "..."}, ...].
     *
     * Existing single action/success-criteria values are converted into the first pair. The old
     * action_text / success_criteria columns are deliberately left in place (no longer written or
     * read) so nothing is lost and a rollback stays safe; a later migration can drop them.
     */
    public function up(): void
    {
        Schema::table('appraisal_targets', function (Blueprint $table) {
            $table->json('actions')->nullable()->after('success_criteria');
        });

        DB::table('appraisal_targets')
            ->where(fn ($q) => $q->whereNotNull('action_text')->orWhereNotNull('success_criteria'))
            ->orderBy('id')
            ->each(function ($row) {
                $action = trim((string) $row->action_text);
                $criteria = trim((string) $row->success_criteria);

                if ($action === '' && $criteria === '') {
                    return;
                }

                DB::table('appraisal_targets')->where('id', $row->id)->update([
                    'actions' => json_encode([['action_text' => $action, 'success_criteria' => $criteria]], JSON_UNESCAPED_UNICODE),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('appraisal_targets', function (Blueprint $table) {
            $table->dropColumn('actions');
        });
    }
};
