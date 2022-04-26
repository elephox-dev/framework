<?php
declare(strict_types=1);
/** @psalm-suppress all */

namespace Elephox\Platform\Native;

use Elephox\Platform\Contract\FilesystemPlatform;
use JetBrains\PhpStorm\Deprecated;
use function basename;
use function chgrp;
use function chmod;
use function chown;
use function clearstatcache;
use function copy;
use function dirname;
use function disk_free_space;
use function disk_total_space;
use function diskfreespace;
use function fclose;
use function fdatasync;
use function feof;
use function fflush;
use function fgetc;
use function fgetcsv;
use function fgets;
use function file;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fileatime;
use function filectime;
use function filegroup;
use function fileinode;
use function filemtime;
use function fileowner;
use function fileperms;
use function filesize;
use function filetype;
use function flock;
use function fnmatch;
use function fopen;
use function fpassthru;
use function fputcsv;
use function fputs;
use function glob;
use function is_dir;
use function is_executable;
use function is_file;
use function is_link;
use function is_readable;
use function is_uploaded_file;
use function is_writable;
use function is_writeable;
use function lchgrp;
use function lchown;
use function link;
use function linkinfo;
use function lstat;
use function mkdir;
use function move_uploaded_file;
use function parse_ini_file;
use function parse_ini_string;
use function pathinfo;
use function pclose;
use function popen;
use function readfile;
use function readlink;
use function realpath;
use function realpath_cache_get;
use function realpath_cache_size;
use function rename;
use function rewind;
use function rmdir;
use function set_file_buffer;
use function stat;
use function symlink;
use function tempnam;
use function tmpfile;
use function touch;
use function umask;
use function unlink;

class NativeFilesystemPlatform implements FilesystemPlatform
{
	public static function basename(string $path, string $suffix = ''): string
	{
		return basename($path, $suffix);
	}

	public static function chgrp(string $filename, int|string $group): bool
	{
		return chgrp($filename, $group);
	}

	public static function chmod(string $filename, int $permissions): bool
	{
		return chmod($filename, $permissions);
	}

	public static function chown(string $filename, int|string $user): bool
	{
		return chown($filename, $user);
	}

	public static function clearstatcache(bool $clear_realpath_cache = false, string $filename = ''): void
	{
		clearstatcache($clear_realpath_cache, $filename);
	}

	public static function copy(string $from, string $to, mixed $context = null): bool
	{
		return copy($from, $to, $context);
	}

	public static function dirname(string $path, int $levels = 1): string
	{
		return dirname($path, $levels);
	}

	public static function disk_free_space(string $directory): float|false
	{
		return disk_free_space($directory);
	}

	public static function disk_total_space(string $directory): float|false
	{
		return disk_total_space($directory);
	}

	#[Deprecated(reason: 'Alias of disk_free_space()', replacement: 'disk_free_space(%parametersList%)')]
	public static function diskfreespace(string $directory): float|false
	{
		/** @noinspection AliasFunctionsUsageInspection */
		return diskfreespace($directory);
	}

	public static function fclose(mixed $stream): bool
	{
		return fclose($stream);
	}

	public static function fdatasync(mixed $stream): bool
	{
		return fdatasync($stream);
	}

	public static function feof(mixed $stream): bool
	{
		return feof($stream);
	}

	public static function fflush(mixed $stream): bool
	{
		return fflush($stream);
	}

	public static function fgetc(mixed $stream): string|false
	{
		return fgetc($stream);
	}

	public static function fgetcsv(mixed $stream, ?int $length = null, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array|false
	{
		return fgetcsv($stream, $length, $separator, $enclosure, $escape);
	}

	public static function fgets(mixed $stream, ?int $length = null): string|false
	{
		return fgets($stream, $length);
	}

	public static function file_exists(string $filename): bool
	{
		return file_exists($filename);
	}

	public static function file_get_contents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, ?int $length = null): string|false
	{
		return file_get_contents($filename, $use_include_path, $context, $offset, $length);
	}

	public static function file_put_contents(string $filename, mixed $data, int $flags = 0, mixed $context = null): int|false
	{
		return file_put_contents($filename, $data, $flags, $context);
	}

	public static function file(string $filename, int $flags = 0, mixed $context = null): array|false
	{
		return file($filename, $flags, $context);
	}

	public static function fileatime(string $filename): int|false
	{
		return fileatime($filename);
	}

	public static function filectime(string $filename): int|false
	{
		return filectime($filename);
	}

	public static function filegroup(string $filename): int|false
	{
		return filegroup($filename);
	}

	public static function fileinode(string $filename): int|false
	{
		return fileinode($filename);
	}

	public static function filemtime(string $filename): int|false
	{
		return filemtime($filename);
	}

	public static function fileowner(string $filename): int|false
	{
		return fileowner($filename);
	}

	public static function fileperms(string $filename): int|false
	{
		return fileperms($filename);
	}

	public static function filesize(string $filename): int|false
	{
		return filesize($filename);
	}

	public static function filetype(string $filename): string|false
	{
		return filetype($filename);
	}

	public static function flock(mixed $stream, int $operation, ?int &$would_block = null): bool
	{
		return flock($stream, $operation, $would_block);
	}

	public static function fnmatch(string $pattern, string $filename, int $flags = 0): bool
	{
		return fnmatch($pattern, $filename, $flags);
	}

	public static function fopen(string $filename, string $mode, bool $use_include_path = false, mixed $context = null): mixed
	{
		return fopen($filename, $mode, $use_include_path, $context);
	}

	public static function fpassthru(mixed $stream): int
	{
		return fpassthru($stream);
	}

	public static function fputcsv(mixed $stream, array $fields, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): int|false
	{
		return fputcsv($stream, $fields, $separator, $enclosure, $escape, $eol);
	}

	public static function fputs(mixed $stream, string $data, ?int $length = null): int|false
	{
		return fputs($stream, $data, $length);
	}

	public static function fread(mixed $stream, int $length): string|false
	{
		return fread($stream, $length);
	}

	public static function fscanf(mixed $stream, string $format, &...$vars): array|int|false|null
	{
		return fscanf($stream, $format, ...$vars);
	}

	public static function fseek(mixed $stream, int $offset, int $whence = SEEK_SET): int
	{
		return fseek($stream, $offset, $whence);
	}

	public static function fstat(mixed $stream): array|false
	{
		return fstat($stream);
	}

	public static function fsync(mixed $stream): bool
	{
		return fsync($stream);
	}

	public static function ftell(mixed $stream): int|false
	{
		return ftell($stream);
	}

	public static function ftruncate(mixed $stream, int $size): bool
	{
		return ftruncate($stream, $size);
	}

	public static function fwrite(mixed $stream, string $data, ?int $length = null): int|false
	{
		return fwrite($stream, $data, $length);
	}

	public static function glob(string $pattern, int $flags = 0): array|false
	{
		return glob($pattern, $flags);
	}

	public static function is_dir(string $filename): bool
	{
		return is_dir($filename);
	}

	public static function is_executable(string $filename): bool
	{
		return is_executable($filename);
	}

	public static function is_file(string $filename): bool
	{
		return is_file($filename);
	}

	public static function is_link(string $filename): bool
	{
		return is_link($filename);
	}

	public static function is_readable(string $filename): bool
	{
		return is_readable($filename);
	}

	public static function is_uploaded_file(string $filename): bool
	{
		return is_uploaded_file($filename);
	}

	public static function is_writable(string $filename): bool
	{
		return is_writable($filename);
	}

	public static function is_writeable(string $filename): bool
	{
		return is_writeable($filename);
	}

	public static function lchgrp(string $filename, int|string $group): bool
	{
		return lchgrp($filename, $group);
	}

	public static function lchown(string $filename, int|string $user): bool
	{
		return lchown($filename, $user);
	}

	public static function link(string $target, string $link): bool
	{
		return link($target, $link);
	}

	public static function linkinfo(string $path): int|false
	{
		return linkinfo($path);
	}

	public static function lstat(string $filename): array|false
	{
		return lstat($filename);
	}

	public static function mkdir(string $directory, int $permissions = 0o777, bool $recursive = false, mixed $context = null): bool
	{
		return mkdir($directory, $permissions, $recursive, $context);
	}

	public static function move_uploaded_file(string $from, string $to): bool
	{
		return move_uploaded_file($from, $to);
	}

	public static function parse_ini_file(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false
	{
		return parse_ini_file($filename, $process_sections, $scanner_mode);
	}

	public static function parse_ini_string(string $ini, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false
	{
		return parse_ini_string($ini, $process_sections, $scanner_mode);
	}

	public static function pathinfo(string $path, int $flags = PATHINFO_ALL): array|string
	{
		return pathinfo($path, $flags);
	}

	public static function pclose(mixed $handle): int
	{
		return pclose($handle);
	}

	public static function popen(string $command, string $mode): mixed
	{
		return popen($command, $mode);
	}

	public static function readfile(string $filename, bool $use_include_path = false, mixed $context = null): int|false
	{
		return readfile($filename, $use_include_path, $context);
	}

	public static function readlink(string $path): string|false
	{
		return readlink($path);
	}

	public static function realpath_cache_get(): array
	{
		return realpath_cache_get();
	}

	public static function realpath_cache_size(): int
	{
		return realpath_cache_size();
	}

	public static function realpath(string $path): string|false
	{
		return realpath($path);
	}

	public static function rename(string $from, string $to, mixed $context = null): bool
	{
		return rename($from, $to, $context);
	}

	public static function rewind(mixed $stream): bool
	{
		return rewind($stream);
	}

	public static function rmdir(string $directory, mixed $context = null): bool
	{
		return rmdir($directory, $context);
	}

	public static function set_file_buffer(mixed $stream, int $size): int
	{
		return set_file_buffer($stream, $size);
	}

	public static function stat(string $filename): array|false
	{
		return stat($filename);
	}

	public static function symlink(string $target, string $link): bool
	{
		return symlink($target, $link);
	}

	public static function tempnam(string $directory, string $prefix): string|false
	{
		return tempnam($directory, $prefix);
	}

	public static function tmpfile(): mixed
	{
		return tmpfile();
	}

	public static function touch(string $filename, ?int $mtime = null, ?int $atime = null): bool
	{
		return touch($filename, $mtime, $atime);
	}

	public static function umask(?int $mask = null): int
	{
		return umask($mask);
	}

	public static function unlink(string $filename, mixed $context = null): bool
	{
		return unlink($filename, $context);
	}
}
