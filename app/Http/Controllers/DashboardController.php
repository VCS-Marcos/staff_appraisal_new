<?php

namespace App\Http\Controllers;

use App\Enums\AppraisalStatus;
use App\Models\Appraisal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $data = [
            'myAppraisals' => Appraisal::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get(),
        ];

        if ($user->isReviewer()) {
            $data['teamAppraisals'] = Appraisal::with('employee')
                ->where('reviewer_id', $user->id)
                ->orderByDesc('created_at')
                ->get();
        }

        if ($user->isAdmin()) {
            $data['stats'] = [
                'users' => User::count(),
                'current_year' => now()->year,
                'draft' => Appraisal::where('status', AppraisalStatus::Draft)->count(),
                'pending_employee' => Appraisal::where('status', AppraisalStatus::PendingEmployee)->count(),
                'pending_reviewer' => Appraisal::where('status', AppraisalStatus::PendingReviewer)->count(),
                'pending_signoff' => Appraisal::where('status', AppraisalStatus::PendingSignoff)->count(),
                'completed' => Appraisal::where('status', AppraisalStatus::Completed)->count(),
            ];
        } elseif ($user->isReviewer()) {
            $statusCounts = $data['teamAppraisals']->countBy(fn ($a) => $a->status->value);

            $data['stats'] = [
                'total' => $data['teamAppraisals']->count(),
                'pending' => $statusCounts->get(AppraisalStatus::PendingReviewer->value, 0),
                'in_progress' => $statusCounts->get(AppraisalStatus::PendingEmployee->value, 0)
                    + $statusCounts->get(AppraisalStatus::PendingSignoff->value, 0),
                'completed' => $statusCounts->get(AppraisalStatus::Completed->value, 0),
            ];
        } else {
            $statusCounts = $data['myAppraisals']->countBy(fn ($a) => $a->status->value);

            $data['stats'] = [
                'total' => $data['myAppraisals']->count(),
                'pending' => $statusCounts->get(AppraisalStatus::PendingEmployee->value, 0),
                'in_progress' => $statusCounts->get(AppraisalStatus::PendingReviewer->value, 0)
                    + $statusCounts->get(AppraisalStatus::PendingSignoff->value, 0),
                'completed' => $statusCounts->get(AppraisalStatus::Completed->value, 0),
            ];
        }

        return view('dashboard', $data);
    }
}
