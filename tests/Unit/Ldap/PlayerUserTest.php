<?php

namespace Tests\Unit\Ldap;

use App\Ldap\PlayerUser;
use LdapRecord\Models\OpenLDAP\User;
use PHPUnit\Framework\TestCase;

class PlayerUserTest extends TestCase
{
    private PlayerUser $playerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->playerUser = new PlayerUser();
    }

    // -------------------------------------------------------------------------
    // Class structure
    // -------------------------------------------------------------------------

    
    public function test_it_extends_ldap_record_open_ldap_user(): void
    {
        $this->assertInstanceOf(User::class, $this->playerUser);
    }

    
    public function test_it_has_empty_object_classes_by_default(): void
    {
        $this->assertIsArray(PlayerUser::$objectClasses);
        $this->assertEmpty(PlayerUser::$objectClasses);
    }

    // -------------------------------------------------------------------------
    // Connection
    // -------------------------------------------------------------------------

    
    public function test_it_uses_the_people_connection(): void
    {
        $this->assertEquals('people', $this->playerUser->getConnectionName());
    }

    
    public function test_it_does_not_use_the_default_connection(): void
    {
        $this->assertNotEquals('default', $this->playerUser->getConnectionName());
    }

    // -------------------------------------------------------------------------
    // Attributes
    // -------------------------------------------------------------------------

    
    public function test_it_can_set_and_get_uid_attribute(): void
    {
        $this->playerUser->uid = ['testuser'];

        $this->assertEquals('testuser', $this->playerUser->getFirstAttribute('uid'));
    }

    
    public function test_it_can_set_and_get_cn_attribute(): void
    {
        $this->playerUser->cn = ['Test User'];

        $this->assertEquals('Test User', $this->playerUser->getFirstAttribute('cn'));
    }

    
    public function test_it_can_set_and_get_mail_attribute(): void
    {
        $this->playerUser->mail = ['testuser@example.com'];

        $this->assertEquals('testuser@example.com', $this->playerUser->getFirstAttribute('mail'));
    }

    
    public function test_it_returns_null_for_unset_attribute(): void
    {
        $this->assertNull($this->playerUser->getFirstAttribute('nonexistent'));
    }

    
    public function test_it_can_set_and_get_multiple_attributes(): void
    {
        $this->playerUser->uid  = ['testuser'];
        $this->playerUser->cn   = ['Test User'];
        $this->playerUser->mail = ['testuser@example.com'];

        $this->assertEquals('testuser', $this->playerUser->getFirstAttribute('uid'));
        $this->assertEquals('Test User', $this->playerUser->getFirstAttribute('cn'));
        $this->assertEquals('testuser@example.com', $this->playerUser->getFirstAttribute('mail'));
    }
}

