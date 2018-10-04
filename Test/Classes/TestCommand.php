<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Print 'Hello World!' to the terminal.
 */
class TestCommand extends AbstractCommand
{
    /**
     * Print 'Hello World!' to the terminal.
     */
    public function demoAction()
    {
        $this->io->success('Hello World!');
    }
}
