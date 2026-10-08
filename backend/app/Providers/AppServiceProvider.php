<?php

namespace App\Providers;

use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
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
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by(Str::transliterate($email).'|'.$request->ip());
        });

        Gate::before(function (User $user): ?bool {
            return $user->hasRole(SystemRole::Admin) ? true : null;
        });

        foreach (PermissionName::cases() as $permission) {
            Gate::define(
                $permission->value,
                fn (User $user): bool => $user->hasPermissionTo($permission),
            );
        }
    }
}
