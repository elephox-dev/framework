<?php
declare(strict_types=1);

namespace Elephox\Http;

use ArrayIterator;
use Elephox\Collection\IsKeyedEnumerable;
use Elephox\Platform\Contract\SessionPlatform;
use Elephox\Platform\PlatformManager;
use LogicException;

class SessionMap implements Contract\SessionMap
{
	private static function session(): SessionPlatform
	{
		return PlatformManager::get(SessionPlatform::class);
	}

	public static function fromGlobals(?array $session = null, bool $recreate = false): ?Contract\SessionMap
	{
		if (self::session()->status() === PHP_SESSION_DISABLED) {
			throw new LogicException('Sessions are disabled');
		}

		if ($recreate && self::session()->status() === PHP_SESSION_ACTIVE) {
			self::session()->regenerate_id(true);
		}

		$map = self::start();

		/**
		 * @var mixed $value
		 */
		foreach ($session ?? [] as $key => $value) {
			$map->put($key, $value);
		}

		return $map;
	}

	public static function start(): Contract\SessionMap
	{
		if (self::session()->status() === PHP_SESSION_NONE) {
			self::session()->start();
		}

		return new self();
	}

	public static function destroy(): void
	{
		if (self::session()->status() === PHP_SESSION_ACTIVE) {
			self::session()->destroy();
		}
	}

	/**
	 * @use IsKeyedEnumerable<array-key, mixed>
	 */
	use IsKeyedEnumerable;

	private function __construct()
	{
	}

	public function put(mixed $key, mixed $value): bool
	{
		self::session()->globals($session);
		/** @psalm-suppress MixedAssignment */
		$session[$key] = $value;

		return true;
	}

	public function get(mixed $key): mixed
	{
		self::session()->globals($session);
		/** @psalm-suppress MixedReturnStatement */
		return $session[$key] ?? null;
	}

	public function has(mixed $key): bool
	{
		self::session()->globals($session);

		return isset($session[$key]);
	}

	public function remove(mixed $key): bool
	{
		if (!$this->has($key)) {
			return false;
		}

		self::session()->globals($session);
		if ($session !== null) {
			unset($session[$key]);
		}

		return true;
	}

	public function getIterator(): ArrayIterator
	{
		self::session()->globals($session);

		return new ArrayIterator($session ?? []);
	}
}
