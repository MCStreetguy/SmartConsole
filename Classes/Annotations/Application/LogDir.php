<?php

namespace MCStreetguy\SmartConsole\Annotations\Application;

/**
 * Defines the logging-directory of the application.
 *
 * @Annotation
 * @Target("CLASS")
 */
class LogDir
{
    /**
     * @Required
     * @var string
     */
    public $path;

    /**
     * Get the value of path
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @param  string  $path
     *
     * @return  self
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }
}
