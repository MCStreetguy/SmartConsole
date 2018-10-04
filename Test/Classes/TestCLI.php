<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Console;

class TestCLI extends Console
{
    protected function configure()
    {
        parent::configure();

        $this->init([
            'name' => 'test-app',
            'version' => '0.0.0-alpha',
            'displayName' => 'Test App',
            'debugMode' => true,
            'commands' => [
                TestCommand::class
            ]
        ]);
    }
}
