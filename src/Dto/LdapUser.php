<?php

namespace App\Dto;

use App\Behavior\LdapValueHandlerTrait;

class LdapUser
{
    use LdapValueHandlerTrait;

    public ?string $cn;

    public ?string $displayedName;

    public ?string $email;
    
    public ?string $phone;

    public static function create(array $ldapEntryValues): LdapUser
    {
        $ldapUser = new self;
        $ldapUser->cn = $ldapUser->handleLdapStringValue($ldapEntryValues, "cn");
        $ldapUser->displayedName = $ldapUser->handleLdapStringValue($ldapEntryValues, "displayName");
        $ldapUser->email = $ldapUser->handleLdapStringValue($ldapEntryValues, "mail");
        $ldapUser->phone = $ldapUser->handleLdapStringValue($ldapEntryValues, "telephoneNumber");
        return $ldapUser;
    }
}