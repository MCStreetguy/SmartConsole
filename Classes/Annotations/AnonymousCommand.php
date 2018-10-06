<?php

namespace MCStreetguy\SmartConsole\Annotations;

/**
 * Marks the default subcommand as anonymous, preventing it from beeing called by it's name.
 * This depends on the MCStreetguy\SmartConsole\Annotations\DefaultCommand annotation.
 *
 * @Annotation
 * @Target("METHOD")
 */
class AnonymousCommand
{
}
