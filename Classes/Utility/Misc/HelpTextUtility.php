<?php

namespace MCStreetguy\SmartConsole\Utility\Misc;

use const PHP_EOL as EOL;

abstract class HelpTextUtility
{
    public static function convertToHelpText(string $input)
    {
        return preg_replace([
            '/(?<!\n)\n(?!\n)/',
            '/\n\n/'
        ], [
            ' ',
            EOL
        ], $input);
    }
}
