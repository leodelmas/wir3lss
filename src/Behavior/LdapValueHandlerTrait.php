<?php

namespace App\Behavior;

trait LdapValueHandlerTrait
{
    /**
     * From an array and a key, returns a string
     * @param array $array
     * @param string $key
     * @return string
     */
    public function handleLdapStringValue(array $array, string $key): ?string
    {
        // Is the value defined in the array?
        if (!isset($array[$key])) {
            return null;
        }
        $value = $array[$key];
        // Sometimes the value is an array
        if (is_array($value)) {
            // The array with a single value? We return it
            if (1 === count($value)) {
                return (string) current($value);
            }
            return null;
        }
        return (string) $value;
    }
}