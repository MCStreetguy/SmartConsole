<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Print 'Hello World!' to the terminal.
 */
class HelloWorldCommand extends AbstractCommand
{
    public function indexAction()
    {
        $this->io->out('Hello World!');
    }
}
