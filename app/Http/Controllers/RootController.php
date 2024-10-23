<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class RootController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Root/RootPage');
    }
}
