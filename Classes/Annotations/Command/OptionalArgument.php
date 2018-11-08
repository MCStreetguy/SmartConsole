<?php

namespace MCStreetguy\SmartConsole\Annotations\Command;

/**
 * Enforces an parameter to be turned into an argument instead of an option.
 * If the target parameter is not present or not optional this annotation will be silently ignored.
 *
 * @Annotation
 * @Target("METHOD")
 */
class OptionalArgument
{
    /**
     * @Required
     * @var string
     */
    public $argument;

    /**
     * Get the value of argument
     *
     * @return  string
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * Set the value of argument
     *
     * @param  string  $argument
     *
     * @return  self
     */
    public function setArgument(string $argument)
    {
        $this->argument = $argument;

        return $this;
    }
}
