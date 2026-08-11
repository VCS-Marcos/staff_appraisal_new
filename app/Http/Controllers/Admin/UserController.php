<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
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

        User::create($data);

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

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
