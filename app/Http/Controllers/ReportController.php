<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AuditLog;
use App\Models\ProfessionalDevelopment;
use App\Models\User;
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
        $years = $this->availableYears();
        $rows = $this->completionQuery($request)->get();

        return view('reports.completion', compact('years', 'rows'));
    }

    public function exportCompletion(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->completionQuery($request)->get();

        $filename = 'appraisal-completion-report-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Year', 'Total', 'Draft', 'Awaiting Employee', 'Awaiting Reviewer', 'Awaiting Sign-off', 'Completed', 'Completion %']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->year,
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
            ->when($request->filled('year'), fn ($q) => $q->where('appraisals.year', $request->integer('year')))
            ->selectRaw("appraisals.year as year,
                COUNT(*) as total,
                SUM(CASE WHEN appraisals.status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN appraisals.status = 'pending_employee' THEN 1 ELSE 0 END) as pending_employee,
                SUM(CASE WHEN appraisals.status = 'pending_reviewer' THEN 1 ELSE 0 END) as pending_reviewer,
                SUM(CASE WHEN appraisals.status = 'pending_signoff' THEN 1 ELSE 0 END) as pending_signoff,
                SUM(CASE WHEN appraisals.status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('appraisals.year')
            ->orderByDesc('appraisals.year');
    }

    public function ratings(Request $request): View
    {
        $years = $this->availableYears();

        $appraisals = $this->ratingsQuery($request)
            ->with(['employee', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $distribution = $this->ratingsQuery($request)
            ->selectRaw('overall_rating, count(*) as total')
            ->groupBy('overall_rating')
            ->pluck('total', 'overall_rating');

        return view('reports.ratings', compact('years', 'appraisals', 'distribution'));
    }

    public function exportRatings(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $appraisals = $this->ratingsQuery($request)
            ->with(['employee', 'reviewer'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'staff-ratings-report-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($appraisals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Position', 'Reviewer', 'Year', 'Overall Rating']);

            foreach ($appraisals as $appraisal) {
                fputcsv($out, [
                    $appraisal->employee->name,
                    $appraisal->employee->position,
                    $appraisal->reviewer->name,
                    $appraisal->year,
                    $appraisal->overall_rating?->value,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function ratingsQuery(Request $request): Builder
    {
        return Appraisal::query()
            ->whereNotNull('overall_rating')
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')));
    }

    /**
     * Years to offer in report filters, derived from appraisal data actually on file.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function availableYears(): \Illuminate\Support\Collection
    {
        return Appraisal::query()->distinct()->orderByDesc('year')->pluck('year');
    }

    public function audit(Request $request): View
    {
        $logs = $this->auditQuery($request)
            ->with('user')
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('reports.audit', compact('logs', 'users', 'actions'));
    }

    public function exportAudit(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $logs = $this->auditQuery($request)->with('user')->get();

        $filename = 'activity-log-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['When', 'User', 'Action', 'Entity', 'Entity ID', 'Description', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function auditQuery(Request $request): Builder
    {
        return AuditLog::query()
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('entity_type'), fn ($q) => $q->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->orderByDesc('created_at');
    }

    public function pdHours(Request $request): View
    {
        $years = $this->availableYears();
        $summary = $this->pdHoursQuery($request)->get();

        return view('reports.pd-hours', compact('years', 'summary'));
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
            ->when($request->filled('year'), fn ($q) => $q->where('appraisals.year', $request->integer('year')))
            ->when(! $user->isAdmin(), fn ($q) => $q->where(function ($q2) use ($user) {
                $q2->where('users.line_manager_id', $user->id)->orWhere('users.id', $user->id);
            }))
            ->selectRaw('users.id as user_id, users.name as user_name, COUNT(professional_development.id) as activity_count, SUM(professional_development.hours) as total_hours')
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name');
    }
}
