<h1>IO Component - Method Reference</h1>

In the following you find a detailled explanation of the IO component.

---------------------------

## [RawIO](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Utility/RawIO.php) methods

_(to be written)_

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
- `RawIO` can be instantiated directly, whereas the `IO` class requires additional dependencies to be handed over.

!!! tip "Developers Note"
    It will be extremely rare for you to have to use `RawIO` directly.
    Normally, an `IO` instance is available via the `io` attribute of your command handler anyway, that you can hand around to other classes if really needed.
    **However, it is considered bad practice to let other classes than the command handlers take care about outputting anything.**