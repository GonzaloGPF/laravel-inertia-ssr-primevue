<?php

use App\Enums\MessageTypes;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => HasRole::class,
        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // The errors of a page are shown with a page of the application: the JSON ones, and the server errors when debugging, are left as they are
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if ($response->request->expectsJson()) {
                return null;
            }

            $status = $response->statusCode();

            if ($status === 419) {
                return back()->with('flash_message_data', [
                    'message' => trans('exceptions.page_expired'),
                    'type' => MessageTypes::WARNING->value,
                ]);
            }

            if (! in_array($status, [403, 404, 429, 500, 503], true) || ($status >= 500 && config('app.debug'))) {
                return null;
            }

            try {
                return $response->render('Error/ErrorPage', ['status' => $status])
                    ->withSharedData()
                    ->toResponse($response->request);
            } catch (Throwable) {
                return null;
            }
        });
    })->create();
