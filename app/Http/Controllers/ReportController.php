<?php

namespace App\Http\Controllers;

use App\Models\AppraisalCycle;
use App\Models\ProfessionalDevelopment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
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
