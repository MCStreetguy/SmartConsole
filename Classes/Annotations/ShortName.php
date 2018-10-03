<?php

namespace MCStreetguy\SmartConsole\Annotations;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class ShortName
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
    public $shortName;

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
     * Get the value of shortName
     *
     * @return  string
     */
    public function getShortName()
    {
        return $this->shortName;
    }
}
