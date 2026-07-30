<?php

namespace App\Providers;

use App\Models\User;
use App\Shared\Identifiers\Contracts\PublicIdGenerator;
use App\Shared\Identifiers\UlidPublicIdGenerator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PublicIdGenerator::class, UlidPublicIdGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        Vite::prefetch(concurrency: 3);
    }
}
