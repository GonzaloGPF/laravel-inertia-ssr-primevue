<?php

use App\Models\User;
use App\Services\DashboardService;

beforeEach(function () {
    $this->travelTo('2026-11-15 12:00:00');
});

it('lists the last 12 months, the current one included, from the oldest to the newest', function () {
    $months = array_column(app(DashboardService::class)->usersByMonth(), 'month');

    expect($months)->toHaveCount(12)
        ->and($months[0])->toBe('2025-12')
        ->and($months[11])->toBe('2026-11')
        ->and($months)->toBe(array_values(array_unique($months)));
});

it('counts the users created in each month, with a 0 for the months without users', function () {
    User::factory(2)->create(['created_at' => '2026-11-02']);
    User::factory()->create(['created_at' => '2026-09-30 23:59:59']);
    User::factory(3)->create(['created_at' => '2025-12-20']);

    $totals = collect(app(DashboardService::class)->usersByMonth())->pluck('total', 'month');

    expect($totals['2026-11'])->toBe(2)
        ->and($totals['2026-10'])->toBe(0)
        ->and($totals['2026-09'])->toBe(1)
        ->and($totals['2025-12'])->toBe(3)
        ->and($totals->sum())->toBe(6);
});

it('leaves out the users created before the last 12 months', function () {
    User::factory()->create(['created_at' => '2025-12-01 00:00:00']); // The first moment of the oldest month
    User::factory()->create(['created_at' => '2025-11-30 23:59:59']); // The last moment before it

    $users = collect(app(DashboardService::class)->usersByMonth())->sum('total');

    expect($users)->toBe(1);
});

it('does not count the deleted users', function () {
    User::factory()->create(['created_at' => '2026-11-02']);
    User::factory()->create(['created_at' => '2026-11-03'])->delete();

    $totals = collect(app(DashboardService::class)->usersByMonth())->pluck('total', 'month');

    expect($totals['2026-11'])->toBe(1);
});

it('lists as many months as asked', function () {
    $months = app(DashboardService::class)->usersByMonth(3);

    expect(array_column($months, 'month'))->toBe(['2026-09', '2026-10', '2026-11']);
});
