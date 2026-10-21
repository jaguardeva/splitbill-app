<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Calculation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Dashboard overview with KPIs and recent activity.
     */
    public function index(Request $request): Response
    {
        $sevenDaysAgo = now()->subDays(7);

        $stats = [
            'total_users' => User::query()->count(),
            'total_admins' => User::query()->where('role', Role::Admin->value)->count(),
            'total_regular_users' => User::query()->where('role', Role::User->value)->count(),
            'verified_users_count' => User::query()->whereNotNull('email_verified_at')->count(),
            'total_calculations' => Calculation::query()->count(),
            'total_amount_minor' => (int) Calculation::query()->sum('grand_total_minor'),
            'completed_calculations' => Calculation::query()->where('status', 'completed')->count(),
            'draft_calculations' => Calculation::query()->where('status', 'draft')->count(),
            'new_users_last_7_days' => User::query()->where('created_at', '>=', $sevenDaysAgo)->count(),
            'new_calculations_last_7_days' => Calculation::query()->where('created_at', '>=', $sevenDaysAgo)->count(),
        ];

        $recentCalculations = Calculation::query()
            ->with(['user:id,name,email'])
            ->withCount(['participants', 'items'])
            ->latest('created_at')
            ->limit(6)
            ->get();

        $recentUsers = User::query()
            ->withCount('calculations')
            ->latest('created_at')
            ->limit(6)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentCalculations' => $recentCalculations,
            'recentUsers' => $recentUsers,
        ]);
    }
}
