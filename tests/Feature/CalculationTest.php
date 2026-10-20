<?php

use App\Models\BillItem;
use App\Models\Calculation;
use App\Models\CalculationParticipant;
use App\Models\User;
use App\Services\Calculation\CalculationPersistenceService;

test('verified user can save a calculation', function () {
    $user = User::factory()->create();

    $payload = [
        'title' => 'Makan Siang Bareng',
        'currency' => 'IDR',
        'split_mode' => 'itemized',
        'status' => 'completed',
        'notes' => 'Makan di Warung Padang',
        'participants' => [
            ['id' => 'p1', 'name' => 'Budi'],
            ['id' => 'p2', 'name' => 'Siti'],
        ],
        'items' => [
            [
                'description' => 'Rendang',
                'quantity' => 2,
                'unit_price_minor' => 20000,
                'assigned_participant_ids' => ['p1', 'p2'],
            ],
        ],
        'adjustments' => [
            [
                'type' => 'tax',
                'label' => 'PPN 10%',
                'value_type' => 'percentage',
                'percentage_rate' => 10,
                'is_enabled' => true,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post('/calculations', $payload);

    $calculation = Calculation::where('user_id', $user->id)->first();
    expect($calculation)->not->toBeNull();

    $response->assertRedirect("/calculations/{$calculation->id}");

    // Verify relations and math
    expect($calculation->subtotal_minor)->toBe(40000)
        ->and($calculation->tax_total_minor)->toBe(4000)
        ->and($calculation->grand_total_minor)->toBe(44000)
        ->and($calculation->participants)->toHaveCount(2)
        ->and($calculation->items)->toHaveCount(1)
        ->and($calculation->adjustments)->toHaveCount(1);
});

test('IDOR protection: user cannot view or mutate another user calculation', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $persistence = app(CalculationPersistenceService::class);
    $calculation = $persistence->save($userA, [
        'title' => 'Perhitungan Rahasia User A',
        'split_mode' => 'equal',
        'equal_subtotal_minor' => 50000,
        'participants' => [
            ['id' => 'p1', 'name' => 'Alice'],
        ],
    ]);

    // User B tries to view User A's calculation
    $this->actingAs($userB)->get("/calculations/{$calculation->id}")
        ->assertForbidden();

    // User B tries to edit User A's calculation
    $this->actingAs($userB)->get("/calculations/{$calculation->id}/edit")
        ->assertForbidden();

    // User B tries to update User A's calculation
    $this->actingAs($userB)->put("/calculations/{$calculation->id}", [
        'title' => 'Hacked',
        'split_mode' => 'equal',
        'equal_subtotal_minor' => 10000,
        'participants' => [['id' => 'p1', 'name' => 'Hacker']],
    ])->assertForbidden();

    // User B tries to duplicate User A's calculation
    $this->actingAs($userB)->post("/calculations/{$calculation->id}/duplicate")
        ->assertForbidden();

    // User B tries to delete User A's calculation
    $this->actingAs($userB)->delete("/calculations/{$calculation->id}")
        ->assertForbidden();
});

test('user can duplicate their own calculation', function () {
    $user = User::factory()->create();

    $persistence = app(CalculationPersistenceService::class);
    $calculation = $persistence->save($user, [
        'title' => 'Liburan Bali',
        'split_mode' => 'equal',
        'equal_subtotal_minor' => 150000,
        'participants' => [
            ['id' => 'p1', 'name' => 'Budi'],
            ['id' => 'p2', 'name' => 'Siti'],
        ],
    ]);

    $response = $this->actingAs($user)->post("/calculations/{$calculation->id}/duplicate");

    $cloned = Calculation::where('id', '!=', $calculation->id)
        ->where('user_id', $user->id)
        ->first();

    expect($cloned)->not->toBeNull()
        ->and($cloned->title)->toBe('Liburan Bali (Salinan)')
        ->and($cloned->status)->toBe('draft')
        ->and($cloned->grand_total_minor)->toBe(150000)
        ->and($cloned->participants)->toHaveCount(2);

    $response->assertRedirect("/calculations/{$cloned->id}/edit");
});

test('user can delete their calculation and cascades remove children', function () {
    $user = User::factory()->create();

    $persistence = app(CalculationPersistenceService::class);
    $calculation = $persistence->save($user, [
        'title' => 'Mau Dihapus',
        'split_mode' => 'itemized',
        'participants' => [
            ['id' => 'p1', 'name' => 'Budi'],
        ],
        'items' => [
            [
                'description' => 'Kopi',
                'quantity' => 1,
                'unit_price_minor' => 18000,
                'assigned_participant_ids' => ['p1'],
            ],
        ],
    ]);

    $calcId = $calculation->id;

    $this->actingAs($user)->delete("/calculations/{$calcId}")
        ->assertRedirect('/calculations');

    expect(Calculation::find($calcId))->toBeNull()
        ->and(CalculationParticipant::where('calculation_id', $calcId)->count())->toBe(0)
        ->and(BillItem::where('calculation_id', $calcId)->count())->toBe(0);
});

test('guests can preview calculation without logging in', function () {
    $response = $this->postJson('/calculator/preview', [
        'split_mode' => 'equal',
        'equal_subtotal_minor' => 60000,
        'participants' => [
            ['id' => 'g1', 'name' => 'Guest 1'],
            ['id' => 'g2', 'name' => 'Guest 2'],
        ],
    ]);

    $response->assertOk()
        ->assertJson([
            'subtotal_minor' => 60000,
            'grand_total_minor' => 60000,
        ]);
});
