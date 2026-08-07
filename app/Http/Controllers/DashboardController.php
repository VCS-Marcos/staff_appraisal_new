<?php

namespace App\Http\Controllers;

use App\Enums\AppraisalStatus;
use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $data = [
            'myAppraisals' => Appraisal::with('cycle')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get(),
        ];

        if ($user->isReviewer()) {
            $data['teamAppraisals'] = Appraisal::with(['employee', 'cycle'])
                ->where('reviewer_id', $user->id)
                ->orderByDesc('created_at')
                ->get();
        }

        if ($user->isAdmin()) {
            $data['stats'] = [
                'users' => User::count(),
                'active_cycles' => AppraisalCycle::where('is_active', true)->count(),
                'draft' => Appraisal::where('status', AppraisalStatus::Draft)->count(),
                'pending_employee' => Appraisal::where('status', AppraisalStatus::PendingEmployee)->count(),
                'pending_reviewer' => Appraisal::where('status', AppraisalStatus::PendingReviewer)->count(),
                'pending_signoff' => Appraisal::where('status', AppraisalStatus::PendingSignoff)->count(),
                'completed' => Appraisal::where('status', AppraisalStatus::Completed)->count(),
            ];
        }

        return view('dashboard', $data);
    }
}
