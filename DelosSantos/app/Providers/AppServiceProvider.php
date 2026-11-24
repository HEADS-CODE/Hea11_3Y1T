<?php

namespace App\Providers;

use App\Auth\RoleAwareUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\MemberDocument;
use App\Policies\MemberDocumentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        if ($trustedProxies = config('app.trusted_proxies')) {
            TrustProxies::at($trustedProxies);
        }

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('auth-login', function (Request $request) {
            return Limit::perMinute(10)->by(
                Str::lower(trim((string) $request->input('email'))) . '|' . $request->ip()
            );
        });

        RateLimiter::for('auth-register', fn (Request $request) =>
            Limit::perMinute(5)->by((string) $request->ip())
        );

        RateLimiter::for('verification-resend', fn (Request $request) =>
            Limit::perMinute(5)->by(
                ($request->user()?->getAuthIdentifier() ?? 'guest') . '|' . $request->ip()
            )
        );

        RateLimiter::for('password-reset', fn (Request $request) =>
            Limit::perMinute(5)->by($request->ip() . '|' . $request->path())
        );

        Auth::provider('role-aware', function ($app, array $config) {
            return new RoleAwareUserProvider(
                $app['hash'],
                $app['config']['auth.providers.users.model'],
            );
        });

        Gate::policy(MemberDocument::class, MemberDocumentPolicy::class);
    }
}
