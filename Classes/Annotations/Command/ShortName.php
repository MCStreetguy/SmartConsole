<?php

namespace MCStreetguy\SmartConsole\Annotations\Command;

/**
 * Defines a short name for an option.
 * If the target name is not present or not an option the annotation will be silently ignored.
 *
 * @Annotation
 * @Target("METHOD")
 */
class ShortName
{
    /**
     * @Required
     * @var string
     */
    public $option;

    /**
     * @Required
     * @var string
     */
    public $short;

    /**
     * Get the value of option
     *
     * @return  string
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Set the value of option
     *
     * @param  string  $option
     *
     * @return  self
     */
    public function setOption(string $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get the value of short
     *
     * @return  string
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set the value of short
     *
     * @param  string  $short
     *
     * @return  self
     */
    public function setShort(string $short)
    {
        $this->short = $short;

        return $this;
    }
}
