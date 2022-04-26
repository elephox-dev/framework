<?php
declare(strict_types=1);

namespace Elephox\Http;

use ArrayIterator;
use Elephox\Collection\IsKeyedEnumerable;
use Elephox\Platform\Session;
use LogicException;

class SessionMap implements Contract\SessionMap
{
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?Contract\SessionMap
	{
		if (Session::status() === PHP_SESSION_DISABLED) {
			throw new LogicException('Sessions are disabled');
		}

		if ($recreate && Session::status() === PHP_SESSION_ACTIVE) {
			Session::regenerate_id(true);
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
		if (Session::status() === PHP_SESSION_NONE) {
			Session::start();
		}

		return new self();
	}

	public static function destroy(): void
	{
		if (Session::status() === PHP_SESSION_ACTIVE) {
			Session::destroy();
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
		Session::globals($session);
		/** @psalm-suppress MixedAssignment */
		$session[$key] = $value;

		return true;
	}

	public function get(mixed $key): mixed
	{
		Session::globals($session);
		/** @psalm-suppress MixedReturnStatement */
		return $session[$key] ?? null;
	}

	public function has(mixed $key): bool
	{
		Session::globals($session);

		return isset($session[$key]);
	}

	public function remove(mixed $key): bool
	{
		if (!$this->has($key)) {
			return false;
		}

		Session::globals($session);
		if ($session !== null) {
			unset($session[$key]);
		}

		return true;
	}

	public function getIterator(): ArrayIterator
	{
		Session::globals($session);

		return new ArrayIterator($session ?? []);
	}
}
