PIE - PHP Iterables Enhanced
===

This library (or rather module) was inspired by [C#s LINQ library](https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/concepts/linq/).

However, it is not a full-featured LINQ library. It is only a small subset of the functionality since PHP cannot fully support all the syntactic sugar.
For example the main feature of LINQ, SQL-like syntax directly in source, is not supported since it would require you to compile/transpile your code.

The main idea however is to provide a way to iterate over a collection of objects in a more natural way like you can do with `IEnumerable`s in C#.

## Examples

```php
<?php
declare(strict_types=1);

use Elephox\Collection\Enumerable;

$array = [5, 2, 1, 4, 3];
$pie = Enumerable::from($array);

$pie->orderBy(fn (int $item) => $item)
    ->select(function (int $item) {
      echo $item;
    });

// output: 12345


$pie->where(fn (int $item) => $item % 2 === 0)
    ->select(function (int $item) {
        echo $item;
    });

// output: 24

```
