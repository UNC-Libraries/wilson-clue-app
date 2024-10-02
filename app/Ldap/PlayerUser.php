<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User;

class PlayerUser extends User
{
    protected ?string $connection = 'people';
}