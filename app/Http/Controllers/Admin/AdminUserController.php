<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users in the system.
     */
    public function index(Request $request): Response
    {
        $search = is_string($request->query('search')) ? trim($request->query('search')) : null;
        $roleFilter = is_string($request->query('role')) ? trim($request->query('role')) : null;
        $verifiedFilter = is_string($request->query('verified')) ? trim($request->query('verified')) : null;
        $sort = is_string($request->query('sort')) ? trim($request->query('sort')) : 'created_at';
        $direction = strtolower((string) $request->query('direction')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'created_at', 'calculations_count', 'role'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $users = User::query()
            ->when($search !== null && $search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter !== null && in_array($roleFilter, [Role::Admin->value, Role::User->value], true), function ($query) use ($roleFilter) {
                $query->where('role', $roleFilter);
            })
            ->when($verifiedFilter === 'verified', function ($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->when($verifiedFilter === 'unverified', function ($query) {
                $query->whereNull('email_verified_at');
            })
            ->withCount('calculations')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search ?? '',
                'role' => $roleFilter ?? '',
                'verified' => $verifiedFilter ?? '',
                'sort' => $sort,
                'direction' => $direction,
            ],
            'availableRoles' => [
                ['value' => Role::Admin->value, 'label' => Role::Admin->label()],
                ['value' => Role::User->value, 'label' => Role::User->label()],
            ],
        ]);

    }

    /**
     * Update the role of a specified user.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(Role::class)],
        ]);

        $newRole = Role::from($validated['role']);
        $currentUser = $request->user();

        // Safeguard: Current admin cannot demote themselves to prevent accidental lockout
        if ($currentUser && $currentUser->id === $user->id && $newRole !== Role::Admin) {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun Anda sendiri demi keamanan.');
        }

        $user->role = $newRole;
        $user->save();

        return back()->with('success', "Role untuk pengguna {$user->name} berhasil diperbarui menjadi {$newRole->label()}.");
    }
}
