<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class PlayerUser extends Model
{
    protected ?string $connection = 'people';
}