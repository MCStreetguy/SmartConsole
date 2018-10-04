<h1>Usage Guide</h1>

This page covers the basic usage of this toolkit, just like a step-by-step guide.
If you've never used SmartConsole before, you've come to the right place.
If you are looking for a fully detailled listing of features instead, head on to the [References](/references).

## Installation

Require the toolkit via composer:

```bash
$ composer require mcstreetguy/smart-console
```

## Basic Setup

To get started with SmartConsole we need to do some basic configuration (_yes, we can't completely avoid this step_) and tell our application what it's called, which version it's on and - especially - what commands are available.
There are several ways to achieve this but for now we'll just stick to the best practices to keep it short and simple.

!!! tip "Developers Note"
    In exceptional cases it may happen that this approach does not fit your project setup. In this case you should of course choose a different solution. On the examples page you will find several alternatives but it is strongly discouraged to use them prematurely.

To do so, we simply create a class that inherits from the base class `MCStreetguy\SmartConsole\Console`:

```php
<?php
namespace Vendor\MyCLI;

use MCStreetguy\SmartConsole\Console;
use Vendor\MyCLI\Commands\HelloWorldCommand;

class MyApplication extends Console
{
    protected function configure()
    {
        parent::configure();

        $this->init([
            'name' => 'my-application',
            'version' => '0.0.0-alpha',
            'displayName' => 'My Application',
            'commands' => [
                HelloWorldCommand::class
            ]
        ]);
    }
}
```

Just by looking at the code example, it should be clear what happens. The configure method is called automatically on execution, so this is normally the place where we define our settings.

!!! info "Please note..."
    Since there are also configure-methods in the parent classes of our CLI you need to ensure that `parent::configure()` is called before you start with your own config!