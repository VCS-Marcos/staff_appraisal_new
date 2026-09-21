<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportUsersRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\StaffPhotoProcessor;
use App\Services\UserCsvImporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

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

    public function store(StoreUserRequest $request, StaffPhotoProcessor $photos): RedirectResponse
    {
        $data = $request->safe()->except(['photo']);
        $data['password'] = Hash::make($data['password']);

        $jpeg = $this->processPhoto($request, $photos);

        $user = DB::transaction(function () use ($data, $jpeg) {
            $user = User::create($data);

            if ($jpeg !== null) {
                $this->storePhoto($user, $jpeg);
            }

            return $user;
        });

        AuditLog::record('user.created', $user, "Created staff account: {$user->name} ({$user->role->value})"
            .($jpeg !== null ? ' — with photo' : ''));

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        $managers = User::where('is_active', true)->where('id', '!=', $user->id)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'managers'));
    }

    public function update(UpdateUserRequest $request, User $user, StaffPhotoProcessor $photos): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);
        $jpeg = $this->processPhoto($request, $photos);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $changed = array_values(array_diff(array_keys($user->getChanges()), ['updated_at']));

        if ($jpeg !== null) {
            $this->storePhoto($user, $jpeg);
            $changed[] = 'photo';
        } elseif ($request->boolean('remove_photo') && $user->hasPhoto()) {
            $user->photo()->delete();
            $user->forceFill(['photo_updated_at' => null])->save();
            $changed[] = 'photo (removed)';
        }

        AuditLog::record('user.updated', $user, "Updated staff account: {$user->name}"
            .($changed ? ' — fields: '.implode(', ', $changed) : ''));

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    /** Decode + re-encode the uploaded photo, or null when none was sent. */
    private function processPhoto(FormRequest $request, StaffPhotoProcessor $photos): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        try {
            return $photos->process($request->file('photo'));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['photo' => $e->getMessage()]);
        }
    }

    private function storePhoto(User $user, string $jpeg): void
    {
        $user->photo()->updateOrCreate(['user_id' => $user->id], ['data' => $jpeg]);
        $user->forceFill(['photo_updated_at' => now()])->save();
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = "{$user->name} ({$user->email})";
        $user->delete();

        AuditLog::record('user.deleted', $user, "Deleted staff account: {$name}");

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    public function showImport(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.import');
    }

    public function import(ImportUsersRequest $request, UserCsvImporter $importer): View
    {
        $results = $importer->import($request->file('file')->getRealPath());

        return view('admin.users.import-results', compact('results'));
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('create', User::class);

        return Response::streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, UserCsvImporter::EXPECTED_HEADERS);
            fputcsv($out, ['Jane Doe', 'jane.doe@example.com', 'employee', 'Class Teacher', 'john.smith@example.com', 'yes']);
            fclose($out);
        }, 'staff-import-template.csv', ['Content-Type' => 'text/csv']);
    }
}
