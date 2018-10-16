<h1>IO Component - Method Reference</h1>

In the following you find a detailled explanation of the IO component.

---------------------------

## [RawIO](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Utility/RawIO.php) methods

- **`emergency(string $message, array $context = [])`**
    
    The system is unusable, e.g. a fatal startup error happened.

    _This method is part of the PSR `LoggerInterface`._

    -------

- **`alert(string $message, array $context = [])`**
    
    Action must be taken immediately, e.g. database is unavailable.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`critical(string $message, array $context = [])`**
    
    Critical conditions, e.g. an app component is unavailable.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`error(string $message, array $context = [])`**
    
    Runtime errors that do not require immediate action but should be monitored.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`warning(string $message, array $context = [])`**
    
    Exceptional occurrences that are not errors, e.g. use of deprecated APIs.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`notice(string $message, array $context = [])`**
    
    Normal but significant events.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`info(string $message, array $context = [])`**
    
    Interesting events, e.g. an SQL log.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`debug(string $message, array $context = [])`**
    
    Detailed debug information.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`log($level, string $message, array $context = [])`**
    
    Logs with an arbitrary level.
    
    _This method is part of the PSR `LoggerInterface`._

    -------

- **`interpolate(string $message, array $context = [])`**
    
    Interpolates message placeholders with the corresponding context values supplied.

    A placeholder is in the form of: `{MyPlaceholder}`

    -------

- **`out(string $message, array $context = [], string $color = null, string $background = null)`**
    
    Output an optionally colored message to the terminal.

    -------

- **`success(string $message, array $context = [])`**
    
    Output a success-message.

    -------

- **`newline(int $count = 1)`**
    
    Output a linebreak.

    -------

- **`clear()`**
    
    Clear the terminal screen

---------------------------

## [IO](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Utility/IO.php) methods

_(to be written)_

---------------------------

## `RawIO` vs. `IO`

It may occur, that you need to output something during startup of your application.
This is done by using a preliminary (or raw) IO, which lacks most of the dynamic features but is capable of basic output.
The `IO` class is built on top of the `RawIO` and includes the more complex features.

The main differences are as following:

- `RawIO` ignores any verbosity or quiet flag. Any message will always appear in the terminal unless you mute the application through [piping](https://en.wikipedia.org/wiki/Pipeline_(Unix)) to a [null-device](https://en.wikipedia.org/wiki/Null_device).
- `RawIO` does not allow any input or dynamic output to ensure functionality even in case of a fatal startup error.
- `RawIO` could be instantiated directly, whereas the `IO` class requires additional dependencies to be handed over.

!!! tip "Developers Note"
    It will be extremely rare for you to have to use `RawIO` directly.
    Normally, an `IO` instance is available via the `io` attribute of your command handler anyway, that you can hand around to other classes if really needed.
    **However, it is considered bad practice to let other classes than the command handlers take care about outputting anything.**