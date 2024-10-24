<?php

namespace StatamicRadPack\Runway\Support;

class Json
{
    public static function isJson($value): bool
    {
        if (
            $value === null
            || is_array($value)
            || is_object($value)
            || (is_string($value) && ! str_starts_with($value, '[') && ! str_starts_with($value, '{'))
        ) {
            return false;
        }

        // TODO: Replace this with json_validate when dropping support for PHP 8.2.
        return is_array(json_decode($value, true));
    }
}
