<?php

namespace MCStreetguy\SmartConsole\Utility\Misc;

abstract class HelpTextUtility
{
    public static function convertToHelpText(string $input)
    {
        return preg_replace([
            '/(?<!\n)\n(?!\n)/',
            '/\n\n/'
        ], [
            ' ',
            '\n'
        ], $input);
    }
}
