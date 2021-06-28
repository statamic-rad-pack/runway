<?php

namespace DoubleThreeDigital\Runway\Support;

class Json
{
    public static function isJson($value)
    {
        return is_array(json_decode($value, true));
    }
}
