<?php
declare(strict_types=1);

namespace Elephox\Platform;

use Elephox\Platform\Contract\FilesystemPlatform;
use Elephox\Platform\Native\NativeFilesystemPlatform;
use JetBrains\PhpStorm\Deprecated;

final class Filesystem implements FilesystemPlatform
{
	private function __construct()
	{
	}

	/**
	 * @var class-string<FilesystemPlatform>
	 */
	public static string $implementation = NativeFilesystemPlatform::class;

	public static function basename(string $path, string $suffix = ''): string
	{
		return self::$implementation::basename($path, $suffix);
	}

	public static function chgrp(string $filename, int|string $group): bool
	{
		return self::$implementation::chgrp($filename, $group);
	}

	public static function chmod(string $filename, int $permissions): bool
	{
		return self::$implementation::chmod($filename, $permissions);
	}

	public static function chown(string $filename, int|string $user): bool
	{
		return self::$implementation::chown($filename, $user);
	}

	public static function clearstatcache(bool $clear_realpath_cache = false, string $filename = ''): void
	{
		self::$implementation::clearstatcache($clear_realpath_cache, $filename);
	}

	public static function copy(string $from, string $to, mixed $context = null): bool
	{
		return self::$implementation::copy($from, $to, $context);
	}

	public static function dirname(string $path, int $levels = 1): string
	{
		return self::$implementation::dirname($path, $levels);
	}

	public static function disk_free_space(string $directory): float|false
	{
		return self::$implementation::disk_free_space($directory);
	}

	public static function disk_total_space(string $directory): float|false
	{
		return self::$implementation::disk_total_space($directory);
	}

	#[Deprecated(reason: 'Alias of disk_free_space()', replacement: 'disk_free_space(%parametersList%)')]
	public static function diskfreespace(string $directory): float|false
	{
		return self::$implementation::diskfreespace($directory);
	}

	public static function fclose(mixed $stream): bool
	{
		return self::$implementation::fclose($stream);
	}

	public static function fdatasync(mixed $stream): bool
	{
		return self::$implementation::fdatasync($stream);
	}

	public static function feof(mixed $stream): bool
	{
		return self::$implementation::feof($stream);
	}

	public static function fflush(mixed $stream): bool
	{
		return self::$implementation::fflush($stream);
	}

	public static function fgetc(mixed $stream): string|false
	{
		return self::$implementation::fgetc($stream);
	}

	public static function fgetcsv(mixed $stream, ?int $length = null, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array|false
	{
		return self::$implementation::fgetcsv($stream, $length, $separator, $enclosure, $escape);
	}

	public static function fgets(mixed $stream, ?int $length = null): string|false
	{
		return self::$implementation::fgets($stream, $length);
	}

	public static function file_exists(string $filename): bool
	{
		return self::$implementation::file_exists($filename);
	}

	public static function file_get_contents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, ?int $length = null): string|false
	{
		return self::$implementation::file_get_contents($filename, $use_include_path, $context, $offset, $length);
	}

	public static function file_put_contents(string $filename, mixed $data, int $flags = 0, mixed $context = null): int|false
	{
		return self::$implementation::file_put_contents($filename, $data, $flags, $context);
	}

	public static function file(string $filename, int $flags = 0, mixed $context = null): array|false
	{
		return self::$implementation::file($filename, $flags, $context);
	}

	public static function fileatime(string $filename): int|false
	{
		return self::$implementation::fileatime($filename);
	}

	public static function filectime(string $filename): int|false
	{
		return self::$implementation::filectime($filename);
	}

	public static function filegroup(string $filename): int|false
	{
		return self::$implementation::filegroup($filename);
	}

	public static function fileinode(string $filename): int|false
	{
		return self::$implementation::fileinode($filename);
	}

	public static function filemtime(string $filename): int|false
	{
		return self::$implementation::filemtime($filename);
	}

	public static function fileowner(string $filename): int|false
	{
		return self::$implementation::fileowner($filename);
	}

	public static function fileperms(string $filename): int|false
	{
		return self::$implementation::fileperms($filename);
	}

	public static function filesize(string $filename): int|false
	{
		return self::$implementation::filesize($filename);
	}

	public static function filetype(string $filename): string|false
	{
		return self::$implementation::filetype($filename);
	}

	public static function flock(mixed $stream, int $operation, ?int &$would_block = null): bool
	{
		return self::$implementation::flock($stream, $operation, $would_block);
	}

	public static function fnmatch(string $pattern, string $filename, int $flags = 0): bool
	{
		return self::$implementation::fnmatch($pattern, $filename, $flags);
	}

	public static function fopen(string $filename, string $mode, bool $use_include_path = false, mixed $context = null): mixed
	{
		return self::$implementation::fopen($filename, $mode, $use_include_path, $context);
	}

	public static function fpassthru(mixed $stream): int
	{
		return self::$implementation::fpassthru($stream);
	}

	public static function fputcsv(mixed $stream, array $fields, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): int|false
	{
		return self::$implementation::fputcsv($stream, $fields, $separator, $enclosure, $escape, $eol);
	}

	public static function fputs(mixed $stream, string $data, ?int $length = null): int|false
	{
		return self::$implementation::fputs($stream, $data, $length);
	}

	public static function fread(mixed $stream, int $length): string|false
	{
		return self::$implementation::fread($stream, $length);
	}

	public static function fscanf(mixed $stream, string $format, &...$vars): array|int|false|null
	{
		return self::$implementation::fscanf($stream, $format, ...$vars);
	}

	public static function fseek(mixed $stream, int $offset, int $whence = SEEK_SET): int
	{
		return self::$implementation::fseek($stream, $offset, $whence);
	}

	public static function fstat(mixed $stream): array|false
	{
		return self::$implementation::fstat($stream);
	}

	public static function fsync(mixed $stream): bool
	{
		return self::$implementation::fsync($stream);
	}

	public static function ftell(mixed $stream): int|false
	{
		return self::$implementation::ftell($stream);
	}

	public static function ftruncate(mixed $stream, int $size): bool
	{
		return self::$implementation::ftruncate($stream, $size);
	}

	public static function fwrite(mixed $stream, string $data, ?int $length = null): int|false
	{
		return self::$implementation::fwrite($stream, $data, $length);
	}

	public static function glob(string $pattern, int $flags = 0): array|false
	{
		return self::$implementation::glob($pattern, $flags);
	}

	public static function is_dir(string $filename): bool
	{
		return self::$implementation::is_dir($filename);
	}

	public static function is_executable(string $filename): bool
	{
		return self::$implementation::is_executable($filename);
	}

	public static function is_file(string $filename): bool
	{
		return self::$implementation::is_file($filename);
	}

	public static function is_link(string $filename): bool
	{
		return self::$implementation::is_link($filename);
	}

	public static function is_readable(string $filename): bool
	{
		return self::$implementation::is_readable($filename);
	}

	public static function is_uploaded_file(string $filename): bool
	{
		return self::$implementation::is_uploaded_file($filename);
	}

	public static function is_writable(string $filename): bool
	{
		return self::$implementation::is_writable($filename);
	}

	public static function is_writeable(string $filename): bool
	{
		return self::$implementation::is_writeable($filename);
	}

	public static function lchgrp(string $filename, int|string $group): bool
	{
		return self::$implementation::lchgrp($filename, $group);
	}

	public static function lchown(string $filename, int|string $user): bool
	{
		return self::$implementation::lchown($filename, $user);
	}

	public static function link(string $target, string $link): bool
	{
		return self::$implementation::link($target, $link);
	}

	public static function linkinfo(string $path): int|false
	{
		return self::$implementation::linkinfo($path);
	}

	public static function lstat(string $filename): array|false
	{
		return self::$implementation::lstat($filename);
	}

	public static function mkdir(string $directory, int $permissions = 0o777, bool $recursive = false, mixed $context = null): bool
	{
		return self::$implementation::mkdir($directory, $permissions, $recursive, $context);
	}

	public static function move_uploaded_file(string $from, string $to): bool
	{
		return self::$implementation::move_uploaded_file($from, $to);
	}

	public static function parse_ini_file(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false
	{
		return self::$implementation::parse_ini_file($filename, $process_sections, $scanner_mode);
	}

	public static function parse_ini_string(string $ini, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false
	{
		return self::$implementation::parse_ini_string($ini, $process_sections, $scanner_mode);
	}

	public static function pathinfo(string $path, int $flags = PATHINFO_ALL): array|string
	{
		return self::$implementation::pathinfo($path, $flags);
	}

	public static function pclose(mixed $handle): int
	{
		return self::$implementation::pclose($handle);
	}

	public static function popen(string $command, string $mode): mixed
	{
		return self::$implementation::popen($command, $mode);
	}

	public static function readfile(string $filename, bool $use_include_path = false, mixed $context = null): int|false
	{
		return self::$implementation::readfile($filename, $use_include_path, $context);
	}

	public static function readlink(string $path): string|false
	{
		return self::$implementation::readlink($path);
	}

	public static function realpath_cache_get(): array
	{
		return self::$implementation::realpath_cache_get();
	}

	public static function realpath_cache_size(): int
	{
		return self::$implementation::realpath_cache_size();
	}

	public static function realpath(string $path): string|false
	{
		return self::$implementation::realpath($path);
	}

	public static function rename(string $from, string $to, mixed $context = null): bool
	{
		return self::$implementation::rename($from, $to, $context);
	}

	public static function rewind(mixed $stream): bool
	{
		return self::$implementation::rewind($stream);
	}

	public static function rmdir(string $directory, mixed $context = null): bool
	{
		return self::$implementation::rmdir($directory, $context);
	}

	public static function set_file_buffer(mixed $stream, int $size): int
	{
		return self::$implementation::set_file_buffer($stream, $size);
	}

	public static function stat(string $filename): array|false
	{
		return self::$implementation::stat($filename);
	}

	public static function symlink(string $target, string $link): bool
	{
		return self::$implementation::symlink($target, $link);
	}

	public static function tempnam(string $directory, string $prefix): string|false
	{
		return self::$implementation::tempnam($directory, $prefix);
	}

	public static function tmpfile(): mixed
	{
		return self::$implementation::tmpfile();
	}

	public static function touch(string $filename, ?int $mtime = null, ?int $atime = null): bool
	{
		return self::$implementation::touch($filename, $mtime, $atime);
	}

	public static function umask(?int $mask = null): int
	{
		return self::$implementation::umask($mask);
	}

	public static function unlink(string $filename, mixed $context = null): bool
	{
		return self::$implementation::unlink($filename, $context);
	}
}
