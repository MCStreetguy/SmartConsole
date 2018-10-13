<?php

namespace MCStreetguy\SmartConsole\Annotations\Application;

/**
 * Defines the display name of the application.
 *
 * @Annotation
 * @Target("CLASS")
 */
class DisplayName
{
    /**
     * @Required
     * @var string
     */
    public $name;

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
}
