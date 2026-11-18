<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Adapter bindings (8 contracts) akan didaftarkan di Step 0.2
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('appMeta', [
            'name'           => config('app.name'),
            'env'            => app()->environment(),
            'deploy_env'     => config('app.deploy_env'),
            'version'        => config('app.version'),
            'git_commit'     => config('app.git_commit'),
            'is_production'  => app()->environment('production'),
            'demo_mode'      => config('app.demo_mode', false),
            'show_env_badge' => ! app()->environment('production') || request()->is('admin/*'),
        ]);
    }
}
