<?php

namespace App\Http\Middleware;

use App\Enums\Languages;
use App\Services\ConstantsService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Apply the authenticated user's preferred language before the request is handled,
     * so validation messages, flash messages, and the rendered `<html lang>` all match it.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            app()->setLocale($request->user()->language?->value ?? Languages::ES->value);
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'appName' => config('app.name'),
            'flash_message_data' => session('flash_message_data'),
            'constants' => resolve(ConstantsService::class)->getConstants(),
        ];
    }
}
