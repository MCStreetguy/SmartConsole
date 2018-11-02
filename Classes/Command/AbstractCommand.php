<?php

namespace MCStreetguy\SmartConsole\Command;

use Webmozart\Console\Api\Args\Args as ArgsApi;
use Webmozart\Console\Api\IO\IO as IOApi;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Utility\Args;
use Webmozart\Console\Api\Command\Command;
use MCStreetguy\SmartConsole\Utility\IO;

/**
 * The base class for command handlers.
 */
abstract class AbstractCommand
{
    /**
     * @var IO
     */
    protected $logger;

    /**
     * @var Args
     */
    protected $args;

    /**
     * Magic function for calls to undefined methods.
     * Used to intercept invokation of command handler methods and redirecting of the call through the 'invoke' method.
     *
     * @see http://php.net/manual/de/language.oop5.overloading.php#object.call
     * @param string $name The method name
     * @param array $arguments Method call arguments
     * @return mixed
     */
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

    /**
     * Prepare the command handler class and invoke the desired handler method.
     *
     * @param ArgsApi $args The underlying Args instance from webmozart/console
     * @param IOApi $io The underlying IO instance from webmozart/console
     * @param Command $cmd The invoked command
     * @param string $name The name of the command handler method
     * @return int
     */
    final public function invoke(ArgsApi $args, IOApi $io, Command $cmd, string $name) : int
    {
        $this->args = new Args($args);
        $this->logger = new IO($io, $this->args);

        if (method_exists($this, 'prepare')) {
            call_user_func_array([$this, 'prepare'], []);
        }

        $reflector = new \ReflectionMethod(static::class . '::' . $name);

        $params = $reflector->getParameters();
        $arguments = [];

        foreach ($params as $parameter) {
            $param = $parameter->getName();

            if ($parameter->isOptional()) {
                $optionName = preg_replace_callback('/(?<=.)([A-Z])/', function ($matches) {
                    return '-' . strtolower($matches[1]);
                }, $param);

                $arguments[] = $args->getOption($optionName);
            } else {
                $arguments[] = $args->getArgument($param);
            }
        }

        $result = call_user_func_array([$this, $name], $arguments);

        if (is_int($result)) {
            if ($result > 255) {
                $result = 255;
            }

            return $result;
        }

        return 0;
    }
}
