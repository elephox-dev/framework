<?php
declare(strict_types=1);

namespace Elephox\Http\Platform;

use Elephox\Platform\Contract\SessionPlatform;
use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;
use SessionHandlerInterface;

class FakeSessionPlatform implements SessionPlatform
{
	private static int $status = PHP_SESSION_NONE;
	private static array $data = [];

	public static function globals(?array &$_sessionRef): void
	{
		$_sessionRef = self::$data;
	}

	public static function abort(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function cache_expire(?int $value = null): int|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function cache_limiter(?string $value = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function commit(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function create_id(string $prefix = ''): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function decode(string $data): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function destroy(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function encode(): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function gc(): int|false
	{
		throw new RuntimeException('Not implemented');
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
		throw new RuntimeException('Not implemented');
	}

	public static function id(?string $id = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function module_name(?string $module = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function name(?string $name = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function regenerate_id(bool $delete_old_session = false): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function register_shutdown(): void
	{
		throw new RuntimeException('Not implemented');
	}

	public static function reset(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function save_path(?string $path = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public static function set_cookie_params(array|int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function set_save_handler(callable|SessionHandlerInterface $sessionhandlerOrOpen, callable|bool $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function start(array $options = []): bool
	{
		self::$status = PHP_SESSION_ACTIVE;

		return true;
	}

	public static function status(): int
	{
		return self::$status;
	}

	public static function unset(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public static function write_close(): bool
	{
		throw new RuntimeException('Not implemented');
	}
}
