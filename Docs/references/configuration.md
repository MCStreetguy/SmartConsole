<h1>Configuration Reference</h1>

## Options

Below you find a detailled reference of all available configuration options for the `initFromConfig` method.

| Option | Type | Required | Description |
|---------------|----------|----------|-------------------------------------------------------------------------------------------------------------------------------------------------|
| `name` | `string` | Yes | The actual name of the application. This is usually the same as you call your executable file. |
| `version` | `string` | Yes | The version string of your application. We recommend specifying a [semantic version number](https://semver.org/) even if this is not necessary. |
| `displayName` | `string` | No | A readable form of your applications name. |
| `helpText` | `string` | No | The text to describe your application on the help page. |
| `debugMode` | `bool` | No | If the application runs in debug mode. If enabled, stack traces and other detailed information is output. (Defaults to `false`) |
| `logDir` | `string` | No | The path of the global logfile directory to use by the logging component. |
| `commands` | `array` | No | An array of command handler class names that shall be analyzed and added to the application. |
| `options` | `array` | No | An array of global options that shall be available application-wide. |

## Methods

Below you find a detailled reference of all available configuration methods.

**`Console::init()`**

Initialize the configuration by analyzing the main class and retrieving the needed information from it directly.

**`Console::initFromConfig(array $config)`**

Initialize the configuration from the given config-array.

**Underlying methods**

All configuration methods from the underlying webmozart/console package are still available throughout SmartConsole.
Thus you may refer to it's [corresponding documentation](https://github.com/webmozart/console/blob/master/README.md#basic-configuration) to learn more about these _rudimentary_ configuration methods.
It it recommended though to rely on the `init` and `initFromConfig` methods as mentioned above as these are far easier to use and ensure consistency through additional validations.