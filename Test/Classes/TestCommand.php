<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

class TestCommand extends AbstractCommand
{
    public function demoAction()
    {
        $this->io->success('Hello World!');
    }

    public function listAction(string $filter, bool $someOption = false)
    {
        if ($someOption === true) {
            $this->io->warning($filter);
        } else {
            $this->io->notice($filter);
        }
    }
}
