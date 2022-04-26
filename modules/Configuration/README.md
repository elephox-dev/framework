# Elephox Configuration Module

This module is used by [Elephox] to load configuration files and provide easy access to their values.
It was inspired by [.NETs Configuration package].

## Example

```php
<?php

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Json\JsonFileConfigurationSource;

$configurationManager = new ConfigurationManager();
$configurationManager->add(new JsonFileConfigurationSource('config.json'));
$configurationManager->add(new JsonFileConfigurationSource('config.local.json', optional: true));
$configurationRoot = $configurationManager->build();

echo $configurationRoot['database:host']; // 'localhost'
echo $configurationRoot['database:port']; // 3306
echo $configurationRoot['env']; // 'local'
```

`config.json`
```json
{
    "env": "production",
    "database": {
        "host": "production-server",
        "port": 3306
    }
}
```

`config.local.json`

```json
{
    "env": "local",
    "database": {
        "host": "localhost"
    }
}
```

[Elephox]: https://github.com/elephox-dev/framework
[.NETs Configuration package]: https://docs.microsoft.com/en-us/dotnet/core/extensions/configuration
