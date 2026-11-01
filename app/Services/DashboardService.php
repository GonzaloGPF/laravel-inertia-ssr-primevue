<?php

namespace App\Services;

use App\Models\User;

/**
 * The numbers of the dashboard of the admins.
 */
class DashboardService
{
    /**
     * The cards of statistics. Only the first one is real: the rest are placeholders with a random number,
     * to lay out the page until there is data to show.
     *
     * @return array{users: int, placeholders: list<int>}
     */
    public function stats(): array
    {
        return [
            'users' => User::query()->count(),
            'placeholders' => array_map(fn () => random_int(0, 1000), range(1, 3)),
        ];
    }

    /**
     * The users created in each of the last months, the current one included, oldest first.
     * The months without users are there too, with a total of 0, so a chart gets a point for every month.
     *
     * @return list<array{month: string, total: int}> The month as Y-m
     */
    public function usersByMonth(int $months = 12): array
    {
        $first = now()->startOfMonth()->subMonths($months - 1);

        $totals = User::query()
            ->where('created_at', '>=', $first)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return array_map(function (int $offset) use ($first, $totals) {
            $month = $first->copy()->addMonths($offset)->format('Y-m');

            return ['month' => $month, 'total' => (int) ($totals[$month] ?? 0)];
        }, range(0, $months - 1));
    }
}
