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
     * Apply the language before the request is handled, so validation messages, flash messages, and the
     * rendered `<html lang>` all match it: the preferred one of the authenticated user or, if they are not
     * logged, the one chosen in the session. Without any, it is the one of the application.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $language = $user
            ? ($user->language->value ?? Languages::ES->value)
            : $request->session()->get('language');

        if ($language) {
            app()->setLocale($language);
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
