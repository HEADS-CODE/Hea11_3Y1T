<?php

namespace App\Providers;

use App\Auth\RoleAwareUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\MemberDocument;
use App\Policies\MemberDocumentPolicy;
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
        Auth::provider('role-aware', function ($app, array $config) {
            return new RoleAwareUserProvider(
                $app['hash'],
                $app['config']['auth.providers.users.model'],
            );
        });

        Gate::policy(MemberDocument::class, MemberDocumentPolicy::class);
    }
}
