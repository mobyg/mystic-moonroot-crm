<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Event;
use App\Models\Note;
use App\Policies\EventPolicy;
use App\Policies\NotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Event::class => EventPolicy::class,
        Note::class => NotePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}