<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('lineManager')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($q2) => $q2->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('position', 'like', $term));
            })
            ->when($request->filled('line_manager_id'), fn ($q) => $q->where('line_manager_id', $request->integer('line_manager_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $managers = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'managers'));
    }

    public function create(): View
    {
        $managers = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('managers'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        AuditLog::record('user.created', $user, "Created staff account: {$user->name} ({$user->role->value})");

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        $managers = User::where('is_active', true)->where('id', '!=', $user->id)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'managers'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $changed = array_values(array_diff(array_keys($user->getChanges()), ['updated_at']));

        AuditLog::record('user.updated', $user, "Updated staff account: {$user->name}"
            .($changed ? ' — fields: '.implode(', ', $changed) : ''));

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = "{$user->name} ({$user->email})";
        $user->delete();

        AuditLog::record('user.deleted', $user, "Deleted staff account: {$name}");

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
