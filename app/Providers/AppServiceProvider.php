<?php

namespace App\Providers;

use App\Models\User;
use App\Shared\Identifiers\Contracts\PublicIdGenerator;
use App\Shared\Identifiers\UlidPublicIdGenerator;
use App\Tenancy\ConfigTenantDatabaseSecretProvider;
use App\Tenancy\Contracts\TenantDatabaseSecretProvider;
use App\Tenancy\TenantDatabaseConnectionManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
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
        $this->app->singleton(TenantDatabaseSecretProvider::class, ConfigTenantDatabaseSecretProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        Queue::before(function (JobProcessing $event): void {
            app(TenantDatabaseConnectionManager::class)->reset();
        });

        Queue::after(function (JobProcessed $event): void {
            app(TenantDatabaseConnectionManager::class)->reset();
        });

        Queue::failing(function (JobFailed $event): void {
            app(TenantDatabaseConnectionManager::class)->reset();
        });

        Vite::prefetch(concurrency: 3);
    }
}
