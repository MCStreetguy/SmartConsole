<?php

namespace MCStreetguy\SmartConsole\Command;

use Webmozart\Console\Api\Args\Args as ArgsApi;
use Webmozart\Console\Api\IO\IO as IOApi;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Utility\IO;
use MCStreetguy\SmartConsole\Utility\Args;

abstract class AbstractCommand
{
    /**
     * @var IO
     */
    protected $io;

    /**
     * @var Args
     */
    protected $args;

    final public function __construct()
    {
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    final public function __call($name, $arguments)
    {
        if ($name === 'handle') {
            throw new ConfigurationException(
                "No default subcommand has been specified for command '" . static::class . "'!",
                1538601023
            );
        }

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

    final public function invoke(ArgsApi $args, IOApi $io, string $name) : int
    {
        $this->io = new IO($io);
        $this->args = new Args($args);

        return 0;
    }
}
