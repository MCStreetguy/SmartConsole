#!/usr/bin/env php
<?php

include_once __DIR__ . '/../vendor/autoload.php';

use League\CLImate\CLImate;

$climate = new CLImate;

$progress = $climate->progress();

\Kint::dump($progress);

exit;
