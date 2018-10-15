<?php

namespace MCStreetguy\SmartConsole\Exceptions;

class ConfigurationException extends BaseException
{
    public static function forConfigurationKey(string $key)
    {
        throw static::create("Invalid configuration value encountered at key '$key'!", 1539640651);
    }

    public static function forInvalidOptionType(string $option, string $source)
    {
        throw static::create("Option '$name' for component '$source' has an invalid type!", 1539640829);
    }
}
