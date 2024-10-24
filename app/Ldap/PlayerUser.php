<?php

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\User;

class PlayerUser extends User
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [];

    protected ?string $connection = 'people';
}