<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\NotifySuperAdminsOnJobFailed;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        \App\Listeners\LogAuthActivity::class,
    ];

    protected $listen = [
        JobFailed::class => [
            NotifySuperAdminsOnJobFailed::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
