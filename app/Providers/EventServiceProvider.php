<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CustomerRegistered;
use App\Listeners\SendCustomerNotifications;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CustomerRegistered::class => [
            SendCustomerNotifications::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
