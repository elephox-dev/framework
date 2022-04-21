<?php
declare(strict_types=1);

namespace Elephox\Http\Platform;

use Elephox\Platform\Contract\SessionPlatform;
use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;
use SessionHandlerInterface;

class FakeSessionPlatform implements SessionPlatform
{
	private int $status = PHP_SESSION_NONE;
	private array $data = [];

	public function globals(?array &$_sessionRef): void
	{
		$_sessionRef = $this->data;
	}

	public function abort(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function cache_expire(?int $value = null): int|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function cache_limiter(?string $value = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function commit(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function create_id(string $prefix = ''): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function decode(string $data): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function destroy(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function encode(): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function gc(): int|false
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
	])] public function get_cookie_params(): array
	{
		throw new RuntimeException('Not implemented');
	}

	public function id(?string $id = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function module_name(?string $module = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function name(?string $name = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function regenerate_id(bool $delete_old_session = false): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function register_shutdown(): void
	{
		throw new RuntimeException('Not implemented');
	}

	public function reset(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function save_path(?string $path = null): string|false
	{
		throw new RuntimeException('Not implemented');
	}

	public function set_cookie_params(array|int $lifetime_or_options, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httponly = null): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function set_save_handler(callable|SessionHandlerInterface $sessionhandlerOrOpen, callable|bool $register_shutdownOrClose = true, ?callable $read = null, ?callable $write = null, ?callable $destroy = null, ?callable $gc = null, ?callable $create_sid = null, ?callable $validate_sid = null, ?callable $update_timestamp = null): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function start(array $options = []): bool
	{
		$this->status = PHP_SESSION_ACTIVE;

		return true;
	}

	public function status(): int
	{
		return $this->status;
	}

	public function unset(): bool
	{
		throw new RuntimeException('Not implemented');
	}

	public function write_close(): bool
	{
		throw new RuntimeException('Not implemented');
	}
}
