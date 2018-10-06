<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Console;

class TestCLI extends Console
{
    protected function configure()
    {
        parent::configure();

        $this->init([
            'name' => 'my-application',
            'version' => '0.0.0-alpha',
            'displayName' => 'My Application',
            'commands' => [
                HelloWorldCommand::class,
                RemoteCommand::class
            ]
        ]);
    }
}
