<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calculation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCalculationController extends Controller
{
    /**
     * Display a listing of all calculations across all users.
     */
    public function index(Request $request): Response
    {
        $search = is_string($request->query('search')) ? trim($request->query('search')) : null;
        $statusFilter = is_string($request->query('status')) ? trim($request->query('status')) : null;
        $modeFilter = is_string($request->query('mode')) ? trim($request->query('mode')) : null;
        $sort = is_string($request->query('sort')) ? trim($request->query('sort')) : 'created_at';
        $direction = strtolower((string) $request->query('direction')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['title', 'created_at', 'grand_total_minor', 'status'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $calculations = Calculation::query()
            ->with(['user:id,name,email'])
            ->withCount(['participants', 'items'])
            ->when($search !== null && $search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($statusFilter !== null && in_array($statusFilter, ['completed', 'draft'], true), function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($modeFilter !== null && in_array($modeFilter, ['itemized', 'equal'], true), function ($query) use ($modeFilter) {
                $query->where('split_mode', $modeFilter);
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Calculations/Index', [
            'calculations' => $calculations,
            'filters' => [
                'search' => $search ?? '',
                'status' => $statusFilter ?? '',
                'mode' => $modeFilter ?? '',
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);

    }

    /**
     * Display the full details of a specific calculation for administrative inspection.
     */
    public function show(Calculation $calculation): Response
    {
        $calculation->load([
            'user:id,name,email',
            'participants',
            'items.assignments.participant',
            'adjustments.participantShares.participant',
        ]);

        return Inertia::render('Admin/Calculations/Show', [
            'calculation' => $calculation,
        ]);
    }
}
