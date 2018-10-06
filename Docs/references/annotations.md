<h1>Annotation Reference</h1>

Below you find a list of all available annotations.

!!! note "Developers Note"
    The following use-statement applies in the list.

    ``` php
    use MCStreetguy\SmartConsole\Annotations as CLI;
    ```

## [`CLI\DefaultCommand`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/DefaultCommand.php)

Marks an action-method as the default command of the handler.
This means it gets executed when no sub-command identifier has been specified.

This annotation gets ignored if only one action-method is present in the handler.

## [`CLI\AnonymousCommand`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/AnonymousCommand.php)

Marks the default subcommand as anonymous, preventing it from beeing called by it's name.

This annotation depends on the [`CLI\DefaultCommand`](#clidefaultcommand) and gets silently ignored if the dependency is not present.

## [`CLI\ShortName`](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Annotations/ShortName.php)

Defines a short name for an option.

This annotation gets silently ignored if the given target name is not defined or not an option.