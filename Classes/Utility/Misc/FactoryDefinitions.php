<?php

namespace MCStreetguy\SmartConsole\Utility\Misc;

use DI\Container;
use MCStreetguy\SmartConsole\Console;
use phpDocumentor\Reflection\DocBlockFactory;
use Psr\Container\ContainerInterface;

/**
 * Static factory definitions.
 * Allows for complex dependency injection through defining injection rules.
 * @see http://php-di.org/doc/php-definitions.html
 */
return [
    ContainerInterface::class => function () {
        return Console::getContainer();
    },

    Container::class => DI\get(ContainerInterface::class),

    DocBlockFactory::class => DI\factory([DocBlockFactory::class, 'createInstance']),
];
