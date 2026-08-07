<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCycleRequest;
use App\Http\Requests\Admin\UpdateCycleRequest;
use App\Models\AppraisalCycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppraisalCycleController extends Controller
{
    public function index(): View
    {
        $cycles = AppraisalCycle::withCount('appraisals')->orderByDesc('start_date')->paginate(20);

        return view('admin.cycles.index', compact('cycles'));
    }

    public function create(): View
    {
        return view('admin.cycles.create');
    }

    public function store(StoreCycleRequest $request): RedirectResponse
    {
        AppraisalCycle::create($request->validated());

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle created.');
    }

    public function edit(AppraisalCycle $cycle): View
    {
        return view('admin.cycles.edit', compact('cycle'));
    }

    public function update(UpdateCycleRequest $request, AppraisalCycle $cycle): RedirectResponse
    {
        $cycle->update($request->validated());

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle updated.');
    }

    public function destroy(AppraisalCycle $cycle): RedirectResponse
    {
        $this->authorize('delete', $cycle);

        $cycle->delete();

        return redirect()->route('admin.cycles.index')->with('status', 'Cycle deleted.');
    }
}
