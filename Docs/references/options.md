<h1>Global Options Reference</h1>

The following options are automatically made available through SmartConsole in the global context:

| Name | Short | Description |
|:----:|:-----:|:------------|
| `--help` | `-h` | Display help about the command |
| `--quiet` | `-q` | Do not output any message |
| `--verbose` | `-v` / `-vv` / `-vvv` | Increase the verbosity of messages |
| `--version` | `-V` | Display the application version |
| `--ansi` |  | Force ANSI output |
| `--no-ansi` |  | Disable ANSI output |
| `--no-interaction` | `-n` | Do not ask any interactive question |
| `--assume-yes` | `-y` | Assume 'yes' as answer for all confirmations. |

!!! note "Developers Note"
    Normally, all of these options get processed in the background, so you don't have to worry about them.
    If you still want to access it, you can do so via the inherited `args` property in your command controller.