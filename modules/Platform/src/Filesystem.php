<?php
declare(strict_types=1);

namespace Elephox\Platform;

use Elephox\Platform\Contract\FilesystemPlatform;
use Elephox\Platform\Native\NativeFilesystemPlatform;

/**
 * @method static string basename(string $path, string $suffix = '')
 * @method static bool chgrp(string $filename, string|int $group)
 * @method static bool chmod(string $filename, int $permissions)
 * @method static bool chown(string $filename, string|int $user)
 * @method static void clearstatcache(bool $clear_realpath_cache = false, string $filename = '')
 * @method static bool copy(string $from, string $to, resource|null $context = null)
 * @method static string dirname(string $path, int $levels = 1)
 * @method static float|false disk_free_space(string $directory)
 * @method static float|false disk_total_space(string $directory)
 * @method static float|false diskfreespace(string $directory)
 * @method static bool fclose(resource $stream)
 * @method static bool fdatasync(resource $stream)
 * @method static bool feof(resource $stream)
 * @method static bool fflush(resource $stream)
 * @method static string|false fgetc(resource $stream)
 * @method static array|false fgetcsv(resource $stream, int|null $length = null, string $separator = ',', string $enclosure = '"', string $escape = '\\')
 * @method static string|false fgets(resource $stream, int|null $length = null)
 * @method static bool file_exists(string $filename)
 * @method static string|false file_get_contents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, int|null $length = null)
 * @method static int|false file_put_contents(string $filename, mixed $data, int $flags = 0, mixed $context = null)
 * @method static array|false file(string $filename, int $flags = 0, mixed $context = null)
 * @method static int|false fileatime(string $filename)
 * @method static int|false filectime(string $filename)
 * @method static int|false filegroup(string $filename)
 * @method static int|false fileinode(string $filename)
 * @method static int|false filemtime(string $filename)
 * @method static int|false fileowner(string $filename)
 * @method static int|false fileperms(string $filename)
 * @method static int|false filesize(string $filename)
 * @method static string|false filetype(string $filename)
 * @method static bool flock(resource $stream, int $operation, int &$would_block = null)
 * @method static bool fnmatch(string $pattern, string $filename, int $flags = 0)
 * @method static resource|false fopen(string $filename, string $mode, bool $use_include_path = false, resource|null $context = null)
 * @method static int fpassthru(resource $stream)
 * @method static int|false fputcsv(resource $stream, array $fields, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n")
 * @method static int|false fputs(resource $stream, string $data, int|null $length = null)
 * @method static string|false fread(resource $stream, int $length)
 * @method static array|int|false|null fscanf(resource $stream, string $format, mixed &...$vars)
 * @method static int fseek(resource $stream, int $offset, int $whence = SEEK_SET)
 * @method static array|false fstat(resource $stream)
 * @method static bool fsync(resource $stream)
 * @method static int|false ftell(resource $stream)
 * @method static bool ftruncate(resource $stream, int $size)
 * @method static int|false fwrite(resource $stream, string $data, int|null $length = null)
 * @method static array|false glob(string $pattern, int $flags = 0)
 * @method static bool is_dir(string $filename)
 * @method static bool is_executable(string $filename)
 * @method static bool is_file(string $filename)
 * @method static bool is_link(string $filename)
 * @method static bool is_readable(string $filename)
 * @method static bool is_uploaded_file(string $filename)
 * @method static bool is_writable(string $filename)
 * @method static bool is_writeable(string $filename)
 * @method static bool lchgrp(string $filename, string|int $group)
 * @method static bool lchown(string $filename, string|int $user)
 * @method static bool link(string $target, string $link)
 * @method static int|false linkinfo(string $path)
 * @method static array|false lstat(string $filename)
 * @method static bool mkdir(string $directory, int $permissions = 0o777, bool $recursive = false, resource|null $context = null)
 * @method static bool move_uploaded_file(string $from, string $to)
 * @method static array|false parse_ini_file(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL)
 * @method static array|false parse_ini_string(string $ini, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL)
 * @method static array|string pathinfo(string $path, int $flags = PATHINFO_ALL)
 * @method static int pclose(resource $handle)
 * @method static resource|false popen(string $command, string $mode)
 * @method static int|false readfile(string $filename, bool $use_include_path = false, resource|null $context = null)
 * @method static string|false readlink(string $path)
 * @method static array realpath_cache_get()
 * @method static int realpath_cache_size()
 * @method static string|false realpath(string $path)
 * @method static bool rename(string $from, string $to, resource|null $context = null)
 * @method static bool rewind(resource $stream)
 * @method static bool rmdir(string $directory, resource|null $context = null)
 * @method static int set_file_buffer(resource $stream, int $size)
 * @method static array|false stat(string $filename)
 * @method static bool symlink(string $target, string $link)
 * @method static string|false tempnam(string $directory, string $prefix)
 * @method static resource|false tmpfile()
 * @method static bool touch(string $filename, int|null $mtime = null, int|null $atime = null)
 * @method static int umask(int|null $mask = null)
 * @method static bool unlink(string $filename, mixed $context = null)
 */
abstract class Filesystem implements FilesystemPlatform
{
	/**
	 * @var class-string<FilesystemPlatform>
	 */
	public static string $implementation = NativeFilesystemPlatform::class;

	public static function __callStatic(string $name, array $arguments): mixed
	{
		return call_user_func_array([self::$implementation, $name], $arguments);
	}

	private function __construct()
	{
	}
}
