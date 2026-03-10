<?php

namespace App;

/**
 * Admin is an alias for Agent with admin privileges.
 * This class exists for backward compatibility with tests.
 */
class Admin extends Agent
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agents';

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('admin', function ($builder) {
            $builder->where('admin', true);
        });
    }
}

