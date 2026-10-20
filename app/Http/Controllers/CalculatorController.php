<?php

namespace App\Http\Controllers;

use App\Services\Calculation\SplitBillCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController extends Controller
{
    /**
     * Show public calculator workspace.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Calculator/Index');
    }

    /**
     * Preview calculation computation via JSON endpoint.
     */
    public function preview(Request $request, SplitBillCalculatorService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'split_mode' => ['required', 'string', 'in:equal,itemized'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.id' => ['required', 'string'],
            'participants.*.name' => ['required', 'string'],
            'items' => ['nullable', 'array'],
            'adjustments' => ['nullable', 'array'],
            'equal_subtotal_minor' => ['nullable', 'integer'],
        ]);

        $result = $calculator->calculate($validated);

        return response()->json($result);
    }
}
