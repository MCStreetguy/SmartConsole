<?php

namespace MCStreetguy\SmartConsole\Utility\Misc;

use function DI\get;
use function DI\factory;

use DI\Container;
use MCStreetguy\SmartConsole\Console;
use phpDocumentor\Reflection\DocBlockFactory;
use Psr\Container\ContainerInterface;
use Monolog\Logger;

/**
 * Static factory definitions.
 * Allows for complex dependency injection through defining injection rules.
 * @see http://php-di.org/doc/php-definitions.html
 */
return [
    ContainerInterface::class => factory(function (ContainerInterface $container) {
        return $container;
    }),

    Container::class => get(ContainerInterface::class),

    DocBlockFactory::class => factory(function (ContainerInterface $container) {
        return DocBlockFactory::createInstance();
    }),
];
