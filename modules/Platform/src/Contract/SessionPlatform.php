<?php
declare(strict_types=1);

namespace Elephox\Platform\Contract;

use JetBrains\PhpStorm\ArrayShape;
use SessionHandlerInterface;

interface SessionPlatform extends PlatformInterface
{
	public static function globals(?array &$_sessionRef): void;

	public static function abort(): bool;

	public static function cache_expire(?int $value = null): int|false;

	public static function cache_limiter(?string $value = null): string|false;

	public static function commit(): bool;

	public static function create_id(string $prefix = ''): string|false;

	public static function decode(string $data): bool;

	public static function destroy(): bool;

	public static function encode(): string|false;

	public static function gc(): int|false;

	#[ArrayShape([
		'lifetime' => 'int',
		'path' => 'string',
		'domain' => 'string',
		'secure' => 'bool',
		'httponly' => 'bool',
		'samesite' => 'string',
	])]
	public static function get_cookie_params(): array;

	public static function id(?string $id = null): string|false;

	public static function module_name(?string $module = null): string|false;

	public static function name(?string $name = null): string|false;

	public static function regenerate_id(bool $delete_old_session = false): bool;

	public static function register_shutdown(): void;

	public static function reset(): bool;

	public static function save_path(?string $path = null): string|false;

	public static function set_cookie_params(array|int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool;

	public static function set_save_handler(SessionHandlerInterface|callable $sessionhandlerOrOpen, bool|callable $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool;

	public static function start(array $options = []): bool;

	public static function status(): int;

	public static function unset(): bool;

	public static function write_close(): bool;
}
