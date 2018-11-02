<h1>Annotation Reference</h1>

Below you find a list of all available annotations.

!!! note "Developers Note"
    The following use-statements apply throughout the list.

    ``` php
    <?php
    use MCStreetguy\SmartConsole\Annotations\Application as App;
    use MCStreetguy\SmartConsole\Annotations\Command as CLI;
    ```

---------------------------

## Command Annotations

The following annotations apply to command handler action methods only.

### [`CLI\DefaultCommand`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Command/DefaultCommand.php)

Marks an action-method as the default command of the handler.
This means it gets executed when no sub-command identifier has been specified.

This annotation gets ignored if only one action-method is present in the handler.

### [`CLI\AnonymousCommand`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Command/AnonymousCommand.php)

Marks the default subcommand as anonymous, preventing it from beeing called by it's name.

This annotation depends on the [`CLI\DefaultCommand`](#clidefaultcommand) and gets silently ignored if the dependency is not present.

### [`CLI\ShortName`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Command/ShortName.php)

Defines a short name for an option.

This annotation gets silently ignored if the given target name is not defined or not an option.

---------------------------

## Global Annotations

The following annotations apply to the configuration class instance only.

### [`App\Version`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Application/Version.php)

Sets the application version string.

### [`App\DebugMode`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Application/DebugMode.php)

Run the application in debug-mode.
This results in more detailled error output including stack traces and some more debugging-related features.

### [`App\DisplayName`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/Application/DisplayName.php)

Sets the readable form of the application name.
This gets used on the help page for example.
