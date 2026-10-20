<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCalculationRequest;
use App\Models\Calculation;
use App\Models\User;
use App\Services\Calculation\CalculationPersistenceService;
use App\Services\Calculation\SplitBillCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SavedCalculationController extends Controller
{
    /**
     * Display a listing of the user's saved calculations.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $search = is_string($request->query('search')) ? trim($request->query('search')) : null;

        $calculations = Calculation::query()
            ->where('user_id', $user->id)
            ->when($search !== null && $search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->withCount(['participants', 'items'])
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Calculations/Index', [
            'calculations' => $calculations,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new calculation.
     */
    public function create(): Response
    {
        return Inertia::render('Calculations/Edit', [
            'calculation' => null,
        ]);
    }

    /**
     * Store a newly created calculation in storage.
     */
    public function store(SaveCalculationRequest $request, CalculationPersistenceService $persistenceService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $calculation = $persistenceService->save($user, $request->validated());

        return redirect()->route('calculations.show', $calculation->id)
            ->with('success', 'Perhitungan berhasil disimpan!');
    }

    /**
     * Display the specified calculation.
     */
    public function show(Calculation $calculation, SplitBillCalculatorService $calculatorService): Response
    {
        Gate::authorize('view', $calculation);

        $calculation->load([
            'participants',
            'items.participants',
            'adjustments.shares',
        ]);

        // Transform into domain format to compute per-person breakdown
        $itemsData = [];
        foreach ($calculation->items as $item) {
            $itemsData[] = [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price_minor' => $item->unit_price_minor,
                'assigned_participant_ids' => $item->participants->pluck('id')->all(),
                'position' => $item->position,
            ];
        }

        $adjustmentsData = [];
        foreach ($calculation->adjustments as $adj) {
            $adjustmentsData[] = [
                'id' => $adj->id,
                'type' => $adj->type,
                'label' => $adj->label,
                'value_type' => $adj->value_type,
                'value_minor' => $adj->value_minor,
                'percentage_rate' => $adj->percentage_rate,
                'base_type' => $adj->base_type,
                'is_enabled' => $adj->is_enabled,
                'position' => $adj->position,
            ];
        }

        $breakdown = $calculatorService->calculate([
            'split_mode' => $calculation->split_mode,
            'participants' => $calculation->participants->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'position' => $p->position,
            ])->all(),
            'items' => $itemsData,
            'adjustments' => $adjustmentsData,
            'equal_subtotal_minor' => $calculation->subtotal_minor,
        ]);

        return Inertia::render('Calculations/Show', [
            'calculation' => $calculation,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Show the form for editing the specified calculation.
     */
    public function edit(Calculation $calculation): Response
    {
        Gate::authorize('update', $calculation);

        $calculation->load([
            'participants',
            'items.participants',
            'adjustments',
        ]);

        return Inertia::render('Calculations/Edit', [
            'calculation' => [
                'id' => $calculation->id,
                'title' => $calculation->title,
                'currency' => $calculation->currency,
                'split_mode' => $calculation->split_mode,
                'status' => $calculation->status,
                'notes' => $calculation->notes,
                'participants' => $calculation->participants->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'position' => $p->position,
                ]),
                'items' => $calculation->items->map(fn ($i) => [
                    'id' => $i->id,
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price_minor' => $i->unit_price_minor,
                    'assigned_participant_ids' => $i->participants->pluck('id')->all(),
                    'position' => $i->position,
                ]),
                'adjustments' => $calculation->adjustments->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'label' => $a->label,
                    'value_type' => $a->value_type,
                    'value_minor' => $a->value_minor,
                    'percentage_rate' => $a->percentage_rate ? (float) $a->percentage_rate : null,
                    'base_type' => $a->base_type,
                    'is_enabled' => (bool) $a->is_enabled,
                    'position' => $a->position,
                ]),
                'subtotal_minor' => $calculation->subtotal_minor,
            ],
        ]);
    }

    /**
     * Update the specified calculation in storage.
     */
    public function update(SaveCalculationRequest $request, Calculation $calculation, CalculationPersistenceService $persistenceService): RedirectResponse
    {
        Gate::authorize('update', $calculation);

        /** @var User $user */
        $user = $request->user();

        $persistenceService->save($user, $request->validated(), $calculation);

        return redirect()->route('calculations.show', $calculation->id)
            ->with('success', 'Perhitungan berhasil diperbarui!');
    }

    /**
     * Remove the specified calculation from storage.
     */
    public function destroy(Calculation $calculation): RedirectResponse
    {
        Gate::authorize('delete', $calculation);

        $calculation->delete();

        return redirect()->route('calculations.index')
            ->with('success', 'Perhitungan berhasil dihapus.');
    }

    /**
     * Duplicate the specified calculation.
     */
    public function duplicate(Calculation $calculation, CalculationPersistenceService $persistenceService, Request $request): RedirectResponse
    {
        Gate::authorize('duplicate', $calculation);

        /** @var User $user */
        $user = $request->user();

        $calculation->load(['participants', 'items.assignments', 'adjustments.shares']);
        $cloned = $persistenceService->duplicate($calculation, $user);

        return redirect()->route('calculations.edit', $cloned->id)
            ->with('success', 'Perhitungan berhasil diduplikasi sebagai draf.');
    }
}
