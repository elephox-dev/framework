# Elephox Collection Module

This library (or rather module) was inspired by [C#s LINQ library](https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/concepts/linq/).

However, it is not a full-featured LINQ library. It is only a small subset of the functionality since user-land PHP code cannot fully support all the syntactic sugar.
For example, the main feature of LINQ, SQL-like syntax directly in source, is not supported since it would require you to compile/transpile your code, which is not in the scope of this library.

The main idea, however, is to provide a way to iterate over a collection of objects in a more natural/handy way like you can do with `IEnumerable`s in C#.

## Examples

```php
<?php
declare(strict_types=1);

use Elephox\Collection\Enumerable;

$data = [5, 2, 1, 4, 3];
$arr = Enumerable::from($data);

$arr->orderBy(fn (int $item) => $item)
    ->select(function (int $item) {
      echo $item;
    });

// output: 12345


$arr->where(fn (int $item) => $item % 2 === 0)
    ->select(function (int $item) {
        echo $item;
    });

// output: 24

echo $arr->aggregate(fn (int $acc, int $item) => $acc + $item, 0);

// output: 15
```

## Differences to C# `IEnumerable`

- PHP doesn't support generics. For now, only static analyzers like [Psalm](https://psalm.dev) can provide full type safety when working with generic collections.
- No extension methods from the `System.Data` namespace are implemented (`CopyToDataTable`)
- `Cast` wouldn't make sense in a dynamically typed language, so it is not implemented either. You can use `Enumerable::select` to change the type of the values.
- `GroupJoin` is not implemented (yet?)
- Methods ending with `OrDefault` always has `null` as the default value. You can, of course, pass your own default value.
- PHP has no default comparer for types, so we provide a `DefaultEqualityComparer` class that implements some methods to compare two values. Depending on if an order is required, `DefaultEqualityComparer::same` or `DefaultEqualityComparer::compare` is used if you don't provide a comparer function yourself.
- `LongCount` is not implemented since PHP only has one integer type
- `OfType` is not implemented since PHP doesn't have generics
- `ToHashSet`, `ToDictionary` and `ToLookup` are not implemented. Instead, you can convert an `Enumerable` to native types via `toList` and `toArray` (whereas `toArray` keeps the keys or allows you to pass a key selector function)
- `AsParallel` and `AsQueryable` are not implemented
- None of the `System.Xml` extension methods are implemented (`Ancestors`, `Descendants`, `Elements`, etc.)
- No read only or immutable interfaces or methods to get them exist (yet?)
