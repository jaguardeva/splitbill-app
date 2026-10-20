<?php

use App\Services\Calculation\SplitBillCalculatorService;

test('calculates equal split with exact remainder allocation', function () {
    $service = new SplitBillCalculatorService;

    $result = $service->calculate([
        'split_mode' => 'equal',
        'equal_subtotal_minor' => 100000,
        'participants' => [
            ['id' => 'p1', 'name' => 'Alice', 'position' => 0],
            ['id' => 'p2', 'name' => 'Bob', 'position' => 1],
            ['id' => 'p3', 'name' => 'Charlie', 'position' => 2],
        ],
    ]);

    expect($result['subtotal_minor'])->toBe(100000)
        ->and($result['grand_total_minor'])->toBe(100000);

    // 100000 / 3 = 33333 with remainder 1. First participant gets 33334
    expect($result['participants']['p1']['final_total_minor'])->toBe(33334)
        ->and($result['participants']['p2']['final_total_minor'])->toBe(33333)
        ->and($result['participants']['p3']['final_total_minor'])->toBe(33333);

    $sum = array_sum(array_column($result['participants'], 'final_total_minor'));
    expect($sum)->toBe(100000);
});

test('calculates itemized split with shared items and ensures exact reconciliation', function () {
    $service = new SplitBillCalculatorService;

    $result = $service->calculate([
        'split_mode' => 'itemized',
        'participants' => [
            ['id' => 'p1', 'name' => 'Budi'],
            ['id' => 'p2', 'name' => 'Siti'],
            ['id' => 'p3', 'name' => 'Andi'],
        ],
        'items' => [
            [
                'description' => 'Nasi Goreng',
                'quantity' => 2,
                'unit_price_minor' => 25000, // 50000
                'assigned_participant_ids' => ['p1', 'p2'],
            ],
            [
                'description' => 'Ayam Bakar',
                'quantity' => 1,
                'unit_price_minor' => 30000, // 30000
                'assigned_participant_ids' => ['p3'],
            ],
            [
                'description' => 'Es Teh Manis',
                'quantity' => 3,
                'unit_price_minor' => 5000, // 15000
                'assigned_participant_ids' => ['p1', 'p2', 'p3'],
            ],
        ],
        'adjustments' => [
            [
                'type' => 'discount',
                'label' => 'Voucher Diskon',
                'value_type' => 'percentage',
                'percentage_rate' => 10, // 10% of 95000 = 9500
                'is_enabled' => true,
            ],
            [
                'type' => 'tax',
                'label' => 'PB1 10%',
                'value_type' => 'percentage',
                'percentage_rate' => 10, // 10% of (95000 - 9500 = 85500) = 8550
                'base_type' => 'after_discount',
                'is_enabled' => true,
            ],
            [
                'type' => 'service_charge',
                'label' => 'Service Charge 5%',
                'value_type' => 'percentage',
                'percentage_rate' => 5, // 5% of 85500 = 4275
                'base_type' => 'after_discount',
                'is_enabled' => true,
            ],
            [
                'type' => 'tip',
                'label' => 'Uang Tip',
                'value_type' => 'fixed',
                'value_minor' => 10000,
                'is_enabled' => true,
            ],
        ],
    ]);

    expect($result['subtotal_minor'])->toBe(95000)
        ->and($result['discount_total_minor'])->toBe(9500)
        ->and($result['tax_total_minor'])->toBe(8550)
        ->and($result['service_charge_minor'])->toBe(4275)
        ->and($result['tip_total_minor'])->toBe(10000);

    $expectedGrandTotal = 95000 - 9500 + 8550 + 4275 + 10000; // 108325
    expect($result['grand_total_minor'])->toBe($expectedGrandTotal);

    // Invariant test: sum of all participant final totals must equal grand total exactly!
    $sumFinalTotals = 0;
    $sumDiscounts = 0;
    $sumTaxes = 0;
    $sumServices = 0;
    $sumTips = 0;
    $sumSubtotals = 0;

    foreach ($result['participants'] as $p) {
        $sumSubtotals += $p['subtotal_minor'];
        $sumDiscounts += $p['discount_minor'];
        $sumTaxes += $p['tax_minor'];
        $sumServices += $p['service_charge_minor'];
        $sumTips += $p['tip_minor'];
        $sumFinalTotals += $p['final_total_minor'];

        // Each individual math must match: subtotal - discount + tax + service + tip == final
        expect($p['final_total_minor'])->toBe(
            $p['subtotal_minor'] - $p['discount_minor'] + $p['tax_minor'] + $p['service_charge_minor'] + $p['tip_minor']
        );
    }

    expect($sumSubtotals)->toBe($result['subtotal_minor'])
        ->and($sumDiscounts)->toBe($result['discount_total_minor'])
        ->and($sumTaxes)->toBe($result['tax_total_minor'])
        ->and($sumServices)->toBe($result['service_charge_minor'])
        ->and($sumTips)->toBe($result['tip_total_minor'])
        ->and($sumFinalTotals)->toBe($result['grand_total_minor']);
});

test('throws exception when participants list is empty', function () {
    $service = new SplitBillCalculatorService;

    $service->calculate([
        'split_mode' => 'equal',
        'participants' => [],
        'equal_subtotal_minor' => 50000,
    ]);
})->throws(InvalidArgumentException::class);

test('throws exception when an item has no assigned participants in itemized mode', function () {
    $service = new SplitBillCalculatorService;

    $service->calculate([
        'split_mode' => 'itemized',
        'participants' => [
            ['id' => 'p1', 'name' => 'Andi'],
        ],
        'items' => [
            [
                'description' => 'Unassigned Item',
                'quantity' => 1,
                'unit_price_minor' => 10000,
                'assigned_participant_ids' => [],
            ],
        ],
    ]);
})->throws(InvalidArgumentException::class);
