<?php

namespace MCStreetguy\SmartConsole\Exceptions;

class UnsupportedFeatureException extends BaseException
{
    public static function forFeatureName(string $name)
    {
        throw static::create("You tried to rely on a non-existent feature: '$name'!", 1539640560);
    }
}
