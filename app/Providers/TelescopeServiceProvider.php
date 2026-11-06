<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();
        Telescope::$hiddenRequestParameters = [];
    }
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($entry->isRequest()) {
                $requestMethod = $entry->content['method'];
                if ($requestMethod === 'OPTIONS' || $requestMethod === 'GET') {
                    return false;
                }
            }
            return true;
        });

        Telescope::tag(function () {
            if (request()->is('api/v1/checkout/*')) {
                return ['checkout'];
            }
            return [];
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'lyu@athleticjunction.com'
            ]);
        });
    }
}
