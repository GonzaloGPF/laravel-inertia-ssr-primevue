<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Everybody that logs in lands here, but only the admins get the statistics: they are deferred props, so the page
     * is shown right away and each group is loaded on its own. The rest of the users get the page without them.
     * Statistics are not a listing of entities, so they are not behind the Filter System.
     */
    public function show(DashboardService $dashboard): Response
    {
        if (! hasRole(Roles::ADMIN)) {
            return Inertia::render('Dashboard/DashboardPage');
        }

        return Inertia::render('Dashboard/DashboardPage', [
            'stats' => Inertia::defer(fn () => $dashboard->stats(), 'stats'),
            'usersByMonth' => Inertia::defer(fn () => $dashboard->usersByMonth(), 'charts'),
        ]);
    }
}
