<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Dashboard/DashboardPage');
    }
}
