<?php

namespace MCStreetguy\SmartConsole\Utility\Helper;

class StringHelper
{
    public static function camelToSnakeCase(string $input) : string
    {
        return strtolower(preg_replace('/(?<=.)([A-Z])/', '-$1', $input));
    }

    public static function snakeToCamelCase(string $input) : string
    {
        return preg_replace_callback('/(?<=.)(-[a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $input);
    }
}
