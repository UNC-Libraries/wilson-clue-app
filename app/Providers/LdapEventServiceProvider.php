<?php

namespace App\Providers;

use LdapRecord\Container;
use Illuminate\Support\ServiceProvider;

class LdapEventServiceProvider extends ServiceProvider
{
    /**
     * The LDAP event listener mappings for the application.
     *
     * @return array
     */
    protected $listen = [
        \LdapRecord\Models\Events\Saved::class => [
            \App\Ldap\ObjectModified::class
        ],
    ];

    /**
     * Register the application LDAP event listeners.
     *
     * @return void
     */
    public function boot()
    {
        $dispatcher = Container::getDispatcher();

        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
}