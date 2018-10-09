<h1>Configuration Reference</h1>

## Options

Below you find a detailled reference of all available configuration options for the `init` method.

| Option | Type | Required | Description |
|---------------|----------|----------|-------------------------------------------------------------------------------------------------------------------------------------------------|
| `name` | `string` | Yes | The actual name of the application. This is usually the same as you call your executable file. |
| `version` | `string` | Yes | The version string of your application. We recommend specifying a [semantic version number](https://semver.org/) even if this is not necessary. |
| `displayName` | `string` | No | A readable form of your applications name. |
| `helpText` | `string` | No | The text to describe your application on the help page. |
| `debugMode` | `bool` | No | If the application runs in debug mode. If enabled, stack traces and other detailed information is output. (Defaults to `false`) |
| `commands` | `array` | No | An array of command handler class names that shall be analyzed and added to the application. |
| `options` | `array` | No | An array of global options that shall be available application-wide. |

## Methods