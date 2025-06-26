<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Map models naar policies.
     */
    protected $policies = [
        Event::class => EventPolicy::class,
    ];

    /**
     * Registreer de policies.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
