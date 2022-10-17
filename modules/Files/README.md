# Elephox Files Module

This module is used by [Elephox] to provide an abstraction layer for directories and files.
It also includes some helpers for file operations and working with paths.

## Examples

```php
<?php

use Elephox\Files\File;
use Elephox\Files\Directory;
use Elephox\Files\Path;

$file = new File('/var/tmp/file.txt');

$file->exists(); // false
$file->writeContents('Hello world!');
$file->exists(); // true
$file->contents(); // 'Hello world!'
$file->extension(); // 'txt'

$newParent = new Directory('/home/user/');

$newFile = $file->moveTo($newParent);

$file->exists(); // false
$newFile->exists(); // true

$newParent->files()->select(fn (File $f) => $f->path())->toArray(); // ['/home/user/file.txt']
$newParent->relativePathTo($file->parent()); // '../../var/tmp'

Path::join("/home/user/", "file.txt"); // '/home/user/file.txt'
Path::join("/home/user/", "../../var/tmp/file.txt"); // '/home/user/../../var/tmp/file.txt'

Path::canonicalize("/home/user\\\\../test\\dir/another//folder"); // '/home/user/../test/dir/another/folder'

Path::isRoot("/"); // true
Path::isRoot("/home/user"); // false
Path::isRoot("C:\\") // true
Path::isRoot("C:\\Windows\\System32"); // false

Path::isRooted("/home/user"); // true
Path::isRooted("home/user"); // false
Path::isRooted("C:\\Windows\\System32"); // true
Path::isRooted("..\\Users\\user\\"); // false
```

[Elephox]: https://github.com/elephox-dev/framework
