<?php

namespace StatamicRadPack\Runway\Support;

class Json
{
    public static function isJson($value): bool
    {
        if (is_array($value) || is_object($value) || (is_string($value) && !str_starts_with('[', $value) && !str_starts_with('{', $value))) {
            return false;
        }

        // TODO: when dropping support for php8.2 implement https://www.php.net/manual/en/function.json-validate.php
        return is_array(json_decode($value, true));
    }
}
