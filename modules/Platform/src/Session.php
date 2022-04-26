<?php
declare(strict_types=1);

namespace Elephox\Platform;

use Elephox\Platform\Contract\SessionPlatform;
use Elephox\Platform\Native\NativeSessionPlatform;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use SessionHandlerInterface;

final class Session implements SessionPlatform
{
	private function __construct()
	{
	}

	/**
	 * @var class-string<SessionPlatform>
	 */
	public static string $implementation = NativeSessionPlatform::class;

	public static function globals(?array &$_sessionRef): void
	{
		self::$implementation::globals($_sessionRef);
	}

	public static function abort(): bool
	{
		return self::$implementation::abort();
	}

	public static function cache_expire(?int $value = null): int|false
	{
		return self::$implementation::cache_expire($value);
	}

	public static function cache_limiter(?string $value = null): string|false
	{
		return self::$implementation::cache_limiter($value);
	}

	#[Deprecated(reason: 'Alias of session_write_close()', replacement: 'write_close(%parametersList%)')]
	public static function commit(): bool
	{
		/** @psalm-suppress DeprecatedMethod */
		return self::$implementation::commit();
	}

	public static function create_id(string $prefix = ''): string|false
	{
		return self::$implementation::create_id($prefix);
	}

	public static function decode(string $data): bool
	{
		return self::$implementation::decode($data);
	}

	public static function destroy(): bool
	{
		return self::$implementation::destroy();
	}

	public static function encode(): string|false
	{
		return self::$implementation::encode();
	}

	public static function gc(): int|false
	{
		return self::$implementation::gc();
	}

	#[ArrayShape([
		'lifetime' => 'int',
		'path' => 'string',
		'domain' => 'string',
		'secure' => 'bool',
		'httponly' => 'bool',
		'samesite' => 'string',
	])] public static function get_cookie_params(): array
	{
		return self::$implementation::get_cookie_params();
	}

	public static function id(?string $id = null): string|false
	{
		return self::$implementation::id($id);
	}

	public static function module_name(?string $module = null): string|false
	{
		return self::$implementation::module_name($module);
	}

	public static function name(?string $name = null): string|false
	{
		return self::$implementation::name($name);
	}

	public static function regenerate_id(bool $delete_old_session = false): bool
	{
		return self::$implementation::regenerate_id($delete_old_session);
	}

	public static function register_shutdown(): void
	{
		self::$implementation::register_shutdown();
	}

	public static function reset(): bool
	{
		return self::$implementation::reset();
	}

	public static function save_path(?string $path = null): string|false
	{
		return self::$implementation::save_path($path);
	}

	public static function set_cookie_params(int|array $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool
	{
		return self::$implementation::set_cookie_params($lifetime_or_options, $path, $domain, $secure, $httponly);
	}

	public static function set_save_handler(callable|SessionHandlerInterface $sessionhandlerOrOpen, callable|bool $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool
	{
		return self::$implementation::set_save_handler($sessionhandlerOrOpen, $register_shutdownOrClose, $read, $write, $destroy, $gc, $create_sid, $validate_sid, $update_timestamp);
	}

	public static function start(array $options = []): bool
	{
		return self::$implementation::start($options);
	}

	public static function status(): int
	{
		return self::$implementation::status();
	}

	public static function unset(): bool
	{
		return self::$implementation::unset();
	}

	public static function write_close(): bool
	{
		return self::$implementation::write_close();
	}
}
