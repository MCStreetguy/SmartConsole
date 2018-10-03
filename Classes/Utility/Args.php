<?php

namespace MCStreetguy\SmartConsole\Utility;

use Webmozart\Console\Api\Args\Args as ArgsApi;

class Args
{
    /**
     * @var ArgsApi
     */
    protected $args;

    public function __construct(ArgsApi $args)
    {
        $this->args = $args;
    }

    public function __call($name, $arguments)
    {
        if (method_exists([$this->args, $name])) {
            return call_user_func_array([$this->args, $name], $arguments);
        }

        throw new \BadMethodCallException("Call to undefined method '$name' on '" . static::class . "'!", 1538601445);
    }

    public function __callStatic($name, $arguments)
    {
        if (method_exists([ArgsApi::class, $name])) {
            return call_user_func_array([ArgsApi::class, $name], $arguments);
        }

        throw new \BadMethodCallException("Call to undefined static method '$name' on '" . static::class . "'!", 1538601445);
    }

    public function getScriptName() : string
    {
        return $this->args->getScriptName();
    }

    public function getCommandNames() : array
    {
        return $this->args->getCommandNames();
    }

    public function getCommandOptions() : array
    {
        return $this->args->getCommandOptions();
    }

    public function getOption(string $name)
    {
        return $this->args->getOption($name);
    }

    public function getOptions(bool $includeDefaults = true) : array
    {
        return $this->args->getOptions($includeDefaults);
    }

    public function setOption(string $name, $value = true)
    {
        $this->args->setOption($name, $value);
    }

    public function addOptions(array $options)
    {
        $this->args->addOptions($options);
    }

    public function setOptions(array $options)
    {
        $this->args->setOptions($options);
    }

    public function isOptionSet(string $name) : bool
    {
        return $this->args->isOptionSet($name);
    }

    public function isOptionDefined(string $name) : bool
    {
        return $this->args->isOptionDefined($name);
    }

    public function getArgument(string $name)
    {
        return $this->args->getArgument($name);
    }

    public function getArguments(bool $includeDefaults = true) : array
    {
        return $this->args->getArguments($includeDefaults);
    }

    public function setArgument(string $name, $value)
    {
        $this->args->setArgument($name, $value);
    }

    public function addArguments(array $arguments)
    {
        $this->args->addArguments($arguments);
    }

    public function setArguments(array $arguments)
    {
        $this->args->setArguments($arguments);
    }

    public function isArgumentSet(string $name) : bool
    {
        return $this->args->isArgumentSet($name);
    }

    public function isArgumentDefined(string $name) : bool
    {
        return $this->args->isArgumentDefined($name);
    }
}
