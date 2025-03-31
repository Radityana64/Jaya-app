<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-admin', function ($user) {
            return !empty($user->role) && $user->role === 'admin';
        });

        Gate::define('access-pelanggan', function ($user) {
            return !empty($user->role) && $user->role === 'pelanggan';
        });

        Gate::define('access-pemilik_toko', function ($user) {
            return !empty($user->role) && $user->role === 'pemilik_toko';
        });
    }
}
