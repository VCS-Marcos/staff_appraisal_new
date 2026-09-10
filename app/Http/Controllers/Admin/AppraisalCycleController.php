<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCycleRequest;
use App\Http\Requests\Admin\UpdateCycleRequest;
use App\Models\AppraisalCycle;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppraisalCycleController extends Controller
{
    public function index(Request $request): View
    {
        $cycles = AppraisalCycle::withCount('appraisals')
            ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cycles.index', compact('cycles'));
    }

    public function create(): View
    {
        return view('admin.cycles.create');
    }

    public function store(StoreCycleRequest $request): RedirectResponse
    {
        $cycle = AppraisalCycle::create($request->validated());

        AuditLog::record('cycle.created', $cycle, "Created appraisal cycle: {$cycle->name} ({$cycle->term->value})");

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle created.');
    }

    public function edit(AppraisalCycle $cycle): View
    {
        return view('admin.cycles.edit', compact('cycle'));
    }

    public function update(UpdateCycleRequest $request, AppraisalCycle $cycle): RedirectResponse
    {
        $cycle->update($request->validated());

        AuditLog::record('cycle.updated', $cycle, "Updated appraisal cycle: {$cycle->name} ({$cycle->term->value})");

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle updated.');
    }

    public function destroy(AppraisalCycle $cycle): RedirectResponse
    {
        $this->authorize('delete', $cycle);

        $name = "{$cycle->name} ({$cycle->term->value})";
        $cycle->delete();

        AuditLog::record('cycle.deleted', $cycle, "Deleted appraisal cycle: {$name}");

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle deleted.');
    }
}
