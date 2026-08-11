<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\ProfessionalDevelopment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index');
    }

    public function completion(Request $request): View
    {
        $cycles = AppraisalCycle::orderByDesc('start_date')->get();
        $rows = $this->completionQuery($request)->get();

        return view('reports.completion', compact('cycles', 'rows'));
    }

    public function exportCompletion(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->completionQuery($request)->get();

        $filename = 'appraisal-completion-report-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Cycle', 'Term', 'Total', 'Draft', 'Awaiting Employee', 'Awaiting Reviewer', 'Awaiting Sign-off', 'Completed', 'Completion %']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->cycle_name,
                    $row->cycle_term,
                    $row->total,
                    $row->draft,
                    $row->pending_employee,
                    $row->pending_reviewer,
                    $row->pending_signoff,
                    $row->completed,
                    $row->total > 0 ? round($row->completed / $row->total * 100).'%' : '0%',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function completionQuery(Request $request): \Illuminate\Database\Query\Builder
    {
        return DB::table('appraisals')
            ->join('appraisal_cycles', 'appraisal_cycles.id', '=', 'appraisals.cycle_id')
            ->when($request->filled('cycle_id'), fn ($q) => $q->where('appraisals.cycle_id', $request->integer('cycle_id')))
            ->selectRaw("appraisal_cycles.id as cycle_id, appraisal_cycles.name as cycle_name, appraisal_cycles.term as cycle_term,
                COUNT(*) as total,
                SUM(CASE WHEN appraisals.status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN appraisals.status = 'pending_employee' THEN 1 ELSE 0 END) as pending_employee,
                SUM(CASE WHEN appraisals.status = 'pending_reviewer' THEN 1 ELSE 0 END) as pending_reviewer,
                SUM(CASE WHEN appraisals.status = 'pending_signoff' THEN 1 ELSE 0 END) as pending_signoff,
                SUM(CASE WHEN appraisals.status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('appraisal_cycles.id', 'appraisal_cycles.name', 'appraisal_cycles.term')
            ->orderByDesc('appraisal_cycles.id');
    }

    public function ratings(Request $request): View
    {
        $cycles = AppraisalCycle::orderByDesc('start_date')->get();
        $appraisals = $this->ratingsQuery($request)->paginate(20)->withQueryString();
        $distribution = $this->ratingsQuery($request)
            ->selectRaw('overall_rating, count(*) as total')
            ->groupBy('overall_rating')
            ->pluck('total', 'overall_rating');

        return view('reports.ratings', compact('cycles', 'appraisals', 'distribution'));
    }

    public function exportRatings(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $appraisals = $this->ratingsQuery($request)->get();

        $filename = 'staff-ratings-report-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($appraisals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Position', 'Reviewer', 'Cycle', 'Term', 'Overall Rating']);

            foreach ($appraisals as $appraisal) {
                fputcsv($out, [
                    $appraisal->employee->name,
                    $appraisal->employee->position,
                    $appraisal->reviewer->name,
                    $appraisal->cycle->name,
                    $appraisal->cycle->term->value,
                    $appraisal->overall_rating?->value,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function ratingsQuery(Request $request): Builder
    {
        return Appraisal::query()
            ->with(['employee', 'reviewer', 'cycle'])
            ->whereNotNull('overall_rating')
            ->when($request->filled('cycle_id'), fn ($q) => $q->where('cycle_id', $request->integer('cycle_id')))
            ->orderByDesc('created_at');
    }

    public function pdHours(Request $request): View
    {
        $cycles = AppraisalCycle::orderByDesc('start_date')->get();
        $summary = $this->pdHoursQuery($request)->get();

        return view('reports.pd-hours', compact('cycles', 'summary'));
    }

    public function exportPdHours(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $summary = $this->pdHoursQuery($request)->get();

        $filename = 'pd-hours-report-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($summary) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff', 'Activities Logged', 'Total Hours']);

            foreach ($summary as $row) {
                fputcsv($out, [$row->user_name, $row->activity_count, $row->total_hours]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function pdHoursQuery(Request $request): Builder
    {
        $user = $request->user();

        return ProfessionalDevelopment::query()
            ->join('users', 'users.id', '=', 'professional_development.user_id')
            ->join('appraisals', 'appraisals.id', '=', 'professional_development.appraisal_id')
            ->when($request->filled('cycle_id'), fn ($q) => $q->where('appraisals.cycle_id', $request->integer('cycle_id')))
            ->when(! $user->isAdmin(), fn ($q) => $q->where(function ($q2) use ($user) {
                $q2->where('users.line_manager_id', $user->id)->orWhere('users.id', $user->id);
            }))
            ->selectRaw('users.id as user_id, users.name as user_name, COUNT(professional_development.id) as activity_count, SUM(professional_development.hours) as total_hours')
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name');
    }
}
