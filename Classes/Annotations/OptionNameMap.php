<?php

namespace MCStreetguy\SmartConsole\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class OptionNameMap
{
    /**
     * @Required
     *
     * @var array<ShortName>
     */
    public $shortNames;

    /**
     * Get the value of shortNames
     *
     * @return  array<ShortName>
     */
    public function getShortNames()
    {
        return $this->shortNames;
    }
}
