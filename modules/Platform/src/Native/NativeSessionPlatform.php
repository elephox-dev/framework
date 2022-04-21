<?php
declare(strict_types=1);

namespace Elephox\Platform\Native;

use Elephox\Platform\Contract\SessionPlatform;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use SessionHandlerInterface;
use function session_abort;
use function session_cache_expire;
use function session_cache_limiter;
use function session_commit;
use function session_create_id;
use function session_decode;
use function session_destroy;
use function session_encode;
use function session_gc;
use function session_get_cookie_params;
use function session_id;
use function session_module_name;
use function session_name;
use function session_regenerate_id;
use function session_register_shutdown;
use function session_reset;
use function session_save_path;
use function session_set_cookie_params;
use function session_set_save_handler;
use function session_start;
use function session_status;
use function session_unset;
use function session_write_close;

class NativeSessionPlatform implements SessionPlatform
{
	/**
	 * @param-out array $_sessionRef
	 *
	 * @param ?array $_sessionRef
	 */
	public function globals(?array &$_sessionRef): void
	{
		$_sessionRef = $_SESSION;
	}

	public function abort(): bool
	{
		return session_abort();
	}

	public function cache_expire(?int $value = null): int|false
	{
		return session_cache_expire($value);
	}

	public function cache_limiter(?string $value = null): string|false
	{
		return session_cache_limiter($value);
	}

	public function commit(): bool
	{
		return session_commit();
	}

	public function create_id(string $prefix = ''): string|false
	{
		return session_create_id($prefix);
	}

	public function decode(string $data): bool
	{
		return session_decode($data);
	}

	public function destroy(): bool
	{
		return session_destroy();
	}

	public function encode(): string|false
	{
		return session_encode();
	}

	public function gc(): int|false
	{
		return session_gc();
	}

	#[ArrayShape([
		'lifetime' => 'int',
		'path' => 'string',
		'domain' => 'string',
		'secure' => 'bool',
		'httponly' => 'bool',
		'samesite' => 'string',
	])]
	public function get_cookie_params(): array
	{
		return session_get_cookie_params();
	}

	public function id(?string $id = null): string|false
	{
		return session_id($id);
	}

	public function module_name(?string $module = null): string|false
	{
		return session_module_name($module);
	}

	public function name(?string $name = null): string|false
	{
		return session_name($name);
	}

	public function regenerate_id(bool $delete_old_session = false): bool
	{
		return session_regenerate_id($delete_old_session);
	}

	public function register_shutdown(): void
	{
		session_register_shutdown();
	}

	public function reset(): bool
	{
		return session_reset();
	}

	public function save_path(?string $path = null): string|false
	{
		return session_save_path($path);
	}

	public function set_cookie_params(array|int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool
	{
		return session_set_cookie_params($lifetime_or_options, $path, $domain, $secure, $httponly);
	}

	public function set_save_handler(callable|SessionHandlerInterface $sessionhandlerOrOpen, callable|bool $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool
	{
		if (is_a($sessionhandlerOrOpen, SessionHandlerInterface::class) && is_bool($register_shutdownOrClose)) {
			return session_set_save_handler($sessionhandlerOrOpen, $register_shutdownOrClose);
		}
		if (is_callable($sessionhandlerOrOpen) && is_callable($register_shutdownOrClose)) {
			return session_set_save_handler($sessionhandlerOrOpen, $register_shutdownOrClose, $read, $write, $destroy, $gc, $create_sid, $validate_sid, $update_timestamp);
		}

		throw new InvalidArgumentException('Invalid combination of arguments');
	}

	public function start(array $options = []): bool
	{
		return session_start($options);
	}

	public function status(): int
	{
		return session_status();
	}

	public function unset(): bool
	{
		return session_unset();
	}

	public function write_close(): bool
	{
		return session_write_close();
	}
}
