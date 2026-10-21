<?php

use App\Enums\Role;
use App\Models\Calculation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest cannot access admin dashboard and gets redirected to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('unverified admin cannot access admin dashboard and gets redirected to email verification notice', function () {
    $admin = User::factory()->admin()->unverified()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

test('regular user with role user is forbidden from accessing admin panel', function () {
    $user = User::factory()->create([
        'role' => Role::User,
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('admin user can access admin dashboard successfully', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recentCalculations')
            ->has('recentUsers')
        );
});

test('admin user can view all users list and filter', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create(['role' => Role::User]);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data')
            ->has('filters')
        );
});

test('admin can update another user role to admin or user', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create(['role' => Role::User]);

    $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser), [
        'role' => Role::Admin->value,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($targetUser->fresh()->role)->toBe(Role::Admin);
});

test('admin cannot demote their own account to prevent lockouts', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $admin), [
        'role' => Role::User->value,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect($admin->fresh()->role)->toBe(Role::Admin);
});

test('admin can view all calculations and inspect calculation detail', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create();
    $calculation = Calculation::factory()->for($otherUser)->create([
        'title' => 'Tagihan Kantor Bulanan',
    ]);

    // Admin index list
    $this->actingAs($admin)->get(route('admin.calculations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Calculations/Index')
            ->has('calculations.data')
        );

    // Admin inspect detail
    $this->actingAs($admin)->get(route('admin.calculations.show', $calculation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Calculations/Show')
            ->where('calculation.id', $calculation->id)
            ->where('calculation.title', 'Tagihan Kantor Bulanan')
        );
});

test('admin can sort users by name ascending and descending', function () {
    $admin = User::factory()->admin()->create(['name' => 'Charlie Admin']);
    $userA = User::factory()->create(['name' => 'Alpha User']);
    $userZ = User::factory()->create(['name' => 'Zeta User']);

    // Ascending sort
    $responseAsc = $this->actingAs($admin)->get(route('admin.users.index', [
        'sort' => 'name',
        'direction' => 'asc',
    ]));

    $responseAsc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->where('filters.sort', 'name')
            ->where('filters.direction', 'asc')
            ->where('users.data.0.name', 'Alpha User')
        );

    // Descending sort
    $responseDesc = $this->actingAs($admin)->get(route('admin.users.index', [
        'sort' => 'name',
        'direction' => 'desc',
    ]));

    $responseDesc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->where('filters.sort', 'name')
            ->where('filters.direction', 'desc')
            ->where('users.data.0.name', 'Zeta User')
        );
});

test('admin can sort calculations by grand total minor ascending and descending', function () {
    $admin = User::factory()->admin()->create();

    $cheapCalc = Calculation::factory()->create([
        'title' => 'Tagihan Murah',
        'grand_total_minor' => 15000,
    ]);

    $expensiveCalc = Calculation::factory()->create([
        'title' => 'Tagihan Mahal',
        'grand_total_minor' => 850000,
    ]);

    // Ascending sort
    $this->actingAs($admin)->get(route('admin.calculations.index', [
        'sort' => 'grand_total_minor',
        'direction' => 'asc',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Calculations/Index')
            ->where('filters.sort', 'grand_total_minor')
            ->where('filters.direction', 'asc')
            ->where('calculations.data.0.id', $cheapCalc->id)
        );

    // Descending sort
    $this->actingAs($admin)->get(route('admin.calculations.index', [
        'sort' => 'grand_total_minor',
        'direction' => 'desc',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Calculations/Index')
            ->where('filters.sort', 'grand_total_minor')
            ->where('filters.direction', 'desc')
            ->where('calculations.data.0.id', $expensiveCalc->id)
        );
});
