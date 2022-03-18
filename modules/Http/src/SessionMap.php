<?php
declare(strict_types=1);

namespace Elephox\Http;

use ArrayIterator;
use Elephox\Collection\IsKeyedEnumerable;
use Elephox\Support\DeepCloneable;
use LogicException;

class SessionMap implements Contract\SessionMap
{
	/**
	 * @param null|array $session
	 * @return null|Contract\SessionMap
	 */
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?Contract\SessionMap
	{
		if (session_status() === PHP_SESSION_DISABLED) {
			throw new LogicException('Sessions are disabled');
		}

		if ($recreate && session_status() === PHP_SESSION_ACTIVE) {
			session_regenerate_id(true);
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
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		return new self();
	}

	public static function destroy(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_destroy();
		}
	}

	/**
	 * @use IsKeyedEnumerable<array-key, mixed>
	 */
	use IsKeyedEnumerable, DeepCloneable;

	private function __construct()
	{
	}

	public function put(mixed $key, mixed $value): bool
	{
		/** @psalm-suppress MixedAssignment */
		$_SESSION[$key] = $value;

		return true;
	}

	public function get(mixed $key): mixed
	{
		/** @psalm-suppress MixedReturnStatement */
		return $_SESSION[$key] ?? null;
	}

	public function has(mixed $key): bool
	{
		return isset($_SESSION[$key]);
	}

	public function remove(mixed $key): bool
	{
		if (!$this->has($key)) {
			return false;
		}

		unset($_SESSION[$key]);

		return true;
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($_SESSION);
	}
}
