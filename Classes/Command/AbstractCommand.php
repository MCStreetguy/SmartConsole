<?php

namespace MCStreetguy\SmartConsole\Command;

use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

abstract class AbstractCommand
{
    public function __construct()
    {
    }

    final public function __call($name, $arguments)
    {
        if (preg_match('/Cmd$/', $name)) {
            $arguments[] = str_replace('Cmd', '', $name) . 'Action';
            $name = 'invoke';
        } elseif (!method_exists($this, $name)) {
            throw new \BadMethodCallException(
                "Call to undefined method '$name' on '" . static::class . "'!",
                1538596566
            );
        }

        return call_user_func_array([$this, $name], $arguments);
    }

    public function invoke(Args $args, IO $io, string $name)
    {
    }
}
