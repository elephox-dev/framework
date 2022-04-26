# Elephox Support Module

This module is used by [Elephox] for commonly re-used functionality like `DeepCloneable` or `TransparentGetterSetter` or to hold generally useful classes.

## Examples

```php
<?php

use Elephox\Support\TransparentGetterSetter;
use Elephox\Support\DeepCloneable;

class MyClass {
    use TransparentGetterSetter;
    use DeepCloneable;
    
    private int $foo = 1;
}

$instance = new MyClass();
$instance->setFoo(2); // uses __set implicitly

$clone = $instance->deepClone();
$clone->getFoo(); // 2
$clone->setFoo(3); // doesn't affect $instance
```

[Elephox]: https://github.com/elephox-dev/framework
