<?php

namespace MCStreetguy\SmartConsole\Annotations\Command;

/**
 * Defines a set of aliases for a command.
 *
 * @Annotation
 * @Target("METHOD")
 */
class Aliases
{
    /**
     * @Required
     * @var array<string>
     */
    public $names;

    /**
     * Get the value of names
     *
     * @return  array<string>
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Set the value of names
     *
     * @param  array<string>  $names
     *
     * @return  self
     */
    public function setNames(array $names)
    {
        $this->names = $names;

        return $this;
    }
}
