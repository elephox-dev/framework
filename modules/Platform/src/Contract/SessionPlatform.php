<?php
declare(strict_types=1);

namespace Elephox\Platform\Contract;

use JetBrains\PhpStorm\ArrayShape;
use SessionHandlerInterface;

interface SessionPlatform extends PlatformInterface
{
	public function globals(?array &$_sessionRef): void;

	public function abort(): bool;

	public function cache_expire(?int $value = null): int|false;

	public function cache_limiter(?string $value = null): string|false;

	public function commit(): bool;

	public function create_id(string $prefix = ''): string|false;

	public function decode(string $data): bool;

	public function destroy(): bool;

	public function encode(): string|false;

	public function gc(): int|false;

	#[ArrayShape([
		'lifetime' => 'int',
		'path' => 'string',
		'domain' => 'string',
		'secure' => 'bool',
		'httponly' => 'bool',
		'samesite' => 'string',
	])]
	public function get_cookie_params(): array;

	public function id(?string $id = null): string|false;

	public function module_name(?string $module = null): string|false;

	public function name(?string $name = null): string|false;

	public function regenerate_id(bool $delete_old_session = false): bool;

	public function register_shutdown(): void;

	public function reset(): bool;

	public function save_path(?string $path = null): string|false;

	public function set_cookie_params(array|int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool;

	public function set_save_handler(SessionHandlerInterface|callable $sessionhandlerOrOpen, bool|callable $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool;

	public function start(array $options = []): bool;

	public function status(): int;

	public function unset(): bool;

	public function write_close(): bool;
}
