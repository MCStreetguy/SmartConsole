<?php

namespace MCStreetguy\SmartConsole\Annotations\App;

/**
 * Defines the application version.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Version
{
    /**
     * @Required
     * @var string
     */
    public $version;

    /**
     * Get the value of version
     *
     * @return  string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @param  string  $version
     *
     * @return  self
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }
}
