<?php

namespace App\Providers;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The polymorphic relations keep an alias (user) instead of the class name
        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        $this->ignoreStaleViteHotFile();

        // Who sees the statistics of the dashboard
        Gate::define('view-statistics', fn (User $user) => hasRole(Roles::ADMIN, $user));

        // Fix https
        //        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        //            URL::forceScheme('https');
        //        }
    }

    /**
     * A dev server that was not stopped cleanly (the computer was turned off, the container was killed) leaves
     * its hot file behind, and then every page asks for its assets to a server that is not there. When nobody
     * answers at the address in the file, it is ignored, and the built assets are used. The dev server and
     * the application run in the same container with Sail, so the address is the same for both.
     */
    private function ignoreStaleViteHotFile(): void
    {
        if ($this->app->isProduction() || ! Vite::isRunningHot()) {
            return;
        }

        $address = parse_url(trim((string) file_get_contents(Vite::hotFile())));

        if (isset($address['host']) && @fsockopen($address['host'], $address['port'] ?? 80, timeout: 0.25)) {
            return;
        }

        Vite::useHotFile(storage_path('framework/vite.hot'));
    }
}
