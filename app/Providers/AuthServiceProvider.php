<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Content;
use App\Models\Organization;
use App\Policies\ContentPolicy;
use App\Policies\EventPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ticket::class => TicketPolicy::class,
        Event::class => EventPolicy::class,
        Content::class => ContentPolicy::class,
        Organization::class => OrganizationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
