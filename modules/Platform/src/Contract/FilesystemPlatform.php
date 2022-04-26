<?php
declare(strict_types=1);

namespace Elephox\Platform\Contract;

use JetBrains\PhpStorm\Deprecated;

interface FilesystemPlatform extends PlatformInterface
{
	public static function basename(string $path, string $suffix = ''): string;

	public static function chgrp(string $filename, string|int $group): bool;

	public static function chmod(string $filename, int $permissions): bool;

	public static function chown(string $filename, string|int $user): bool;

	public static function clearstatcache(bool $clear_realpath_cache = false, string $filename = ''): void;

	/**
	 * @param resource|null $context
	 * @param string $from
	 * @param string $to
	 */
	public static function copy(string $from, string $to, mixed $context = null): bool;

	public static function dirname(string $path, int $levels = 1): string;

	public static function disk_free_space(string $directory): float|false;

	public static function disk_total_space(string $directory): float|false;

	#[Deprecated(reason: 'Alias of disk_free_space()', replacement: 'disk_free_space(%parametersList%)')]
	public static function diskfreespace(string $directory): float|false;

	/**
	 * @param resource $stream
	 */
	public static function fclose(mixed $stream): bool;

	/**
	 * @param resource $stream
	 */
	public static function fdatasync(mixed $stream): bool;

	/**
	 * @param resource $stream
	 */
	public static function feof(mixed $stream): bool;

	/**
	 * @param resource $stream
	 */
	public static function fflush(mixed $stream): bool;

	/**
	 * @param resource $stream
	 */
	public static function fgetc(mixed $stream): string|false;

	/**
	 * @param resource $stream
	 * @param ?int $length
	 * @param string $separator
	 * @param string $enclosure
	 * @param string $escape
	 */
	public static function fgetcsv(mixed $stream, ?int $length = null, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array|false;

	/**
	 * @param resource $stream
	 * @param ?int $length
	 */
	public static function fgets(mixed $stream, ?int $length = null): string|false;

	public static function file_exists(string $filename): bool;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 * @param bool $use_include_path
	 * @param int $offset
	 * @param ?int $length
	 */
	public static function file_get_contents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, ?int $length = null): string|false;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 * @param mixed $data
	 * @param int $flags
	 */
	public static function file_put_contents(string $filename, mixed $data, int $flags = 0, mixed $context = null): int|false;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 * @param int $flags
	 */
	public static function file(string $filename, int $flags = 0, mixed $context = null): array|false;

	public static function fileatime(string $filename): int|false;

	public static function filectime(string $filename): int|false;

	public static function filegroup(string $filename): int|false;

	public static function fileinode(string $filename): int|false;

	public static function filemtime(string $filename): int|false;

	public static function fileowner(string $filename): int|false;

	public static function fileperms(string $filename): int|false;

	public static function filesize(string $filename): int|false;

	public static function filetype(string $filename): string|false;

	/**
	 * @param resource $stream
	 * @param int $operation
	 * @param null|int $would_block
	 */
	public static function flock(mixed $stream, int $operation, ?int &$would_block = null): bool;

	public static function fnmatch(string $pattern, string $filename, int $flags = 0): bool;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 * @param string $mode
	 * @param bool $use_include_path
	 *
	 * @return resource|false
	 */
	public static function fopen(string $filename, string $mode, bool $use_include_path = false, mixed $context = null): mixed;

	/**
	 * @param resource $stream
	 */
	public static function fpassthru(mixed $stream): int;

	/**
	 * @param resource $stream
	 * @param array $fields
	 * @param string $separator
	 * @param string $enclosure
	 * @param string $escape
	 * @param string $eol
	 */
	public static function fputcsv(mixed $stream, array $fields, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): int|false;

	/**
	 * @param resource $stream
	 * @param string $data
	 * @param ?int $length
	 */
	public static function fputs(mixed $stream, string $data, ?int $length = null): int|false;

	/**
	 * @param resource $stream
	 * @param int $length
	 */
	public static function fread(mixed $stream, int $length): string|false;

	/**
	 * @param resource $stream
	 * @param string $format
	 * @param mixed[] $vars
	 */
	public static function fscanf(mixed $stream, string $format, mixed &...$vars): array|int|false|null;

	/**
	 * @param resource $stream
	 * @param int $offset
	 * @param int $whence
	 */
	public static function fseek(mixed $stream, int $offset, int $whence = SEEK_SET): int;

	/**
	 * @param resource $stream
	 */
	public static function fstat(mixed $stream): array|false;

	/**
	 * @param resource $stream
	 */
	public static function fsync(mixed $stream): bool;

	/**
	 * @param resource $stream
	 */
	public static function ftell(mixed $stream): int|false;

	/**
	 * @param resource $stream
	 * @param int $size
	 */
	public static function ftruncate(mixed $stream, int $size): bool;

	/**
	 * @param resource $stream
	 * @param string $data
	 * @param ?int $length
	 */
	public static function fwrite(mixed $stream, string $data, ?int $length = null): int|false;

	public static function glob(string $pattern, int $flags = 0): array|false;

	public static function is_dir(string $filename): bool;

	public static function is_executable(string $filename): bool;

	public static function is_file(string $filename): bool;

	public static function is_link(string $filename): bool;

	public static function is_readable(string $filename): bool;

	public static function is_uploaded_file(string $filename): bool;

	public static function is_writable(string $filename): bool;

	public static function is_writeable(string $filename): bool;

	public static function lchgrp(string $filename, string|int $group): bool;

	public static function lchown(string $filename, string|int $user): bool;

	public static function link(string $target, string $link): bool;

	public static function linkinfo(string $path): int|false;

	public static function lstat(string $filename): array|false;

	/**
	 * @param resource|null $context
	 * @param string $directory
	 * @param int $permissions
	 * @param bool $recursive
	 */
	public static function mkdir(string $directory, int $permissions = 0o777, bool $recursive = false, mixed $context = null): bool;

	public static function move_uploaded_file(string $from, string $to): bool;

	public static function parse_ini_file(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false;

	public static function parse_ini_string(string $ini, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array|false;

	public static function pathinfo(string $path, int $flags = PATHINFO_ALL): array|string;

	/**
	 * @param resource $handle
	 */
	public static function pclose(mixed $handle): int;

	/**
	 * @return resource|false
	 *
	 * @param string $command
	 * @param string $mode
	 */
	public static function popen(string $command, string $mode): mixed;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 * @param bool $use_include_path
	 */
	public static function readfile(string $filename, bool $use_include_path = false, mixed $context = null): int|false;

	public static function readlink(string $path): string|false;

	public static function realpath_cache_get(): array;

	public static function realpath_cache_size(): int;

	public static function realpath(string $path): string|false;

	/**
	 * @param resource|null $context
	 * @param string $from
	 * @param string $to
	 */
	public static function rename(string $from, string $to, mixed $context = null): bool;

	/**
	 * @param resource $stream
	 */
	public static function rewind(mixed $stream): bool;

	/**
	 * @param resource|null $context
	 * @param string $directory
	 */
	public static function rmdir(string $directory, mixed $context = null): bool;

	/**
	 * @param resource $stream
	 * @param int $size
	 */
	public static function set_file_buffer(mixed $stream, int $size): int;

	public static function stat(string $filename): array|false;

	public static function symlink(string $target, string $link): bool;

	public static function tempnam(string $directory, string $prefix): string|false;

	/**
	 * @return resource|false
	 */
	public static function tmpfile(): mixed;

	public static function touch(string $filename, ?int $mtime = null, ?int $atime = null): bool;

	public static function umask(?int $mask = null): int;

	/**
	 * @param resource|null $context
	 * @param string $filename
	 */
	public static function unlink(string $filename, mixed $context = null): bool;
}
