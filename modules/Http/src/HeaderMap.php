<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\DefaultEqualityComparer;
use Elephox\Collection\OffsetNotAllowedException;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\OOR\Casing;

/**
 * @extends ArrayMap<string, string|list<string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	/**
	 * @param array<string, string|list<string>>|null $server
	 */
	public static function fromGlobals(?array $server = null): Contract\HeaderMap
	{
		$server ??= $_SERVER;

		$map = new self();

		/**
		 * @var string|list<string> $value
		 */
		foreach ($server as $name => $value) {
			if (!str_starts_with($name, 'HTTP_')) {
				continue;
			}

			$name = Casing::toHttpHeader(substr($name, 5));

			$map->put($name, is_array($value) ? $value : [$value]);
		}

		return $map;
	}

	public static function compareHeaderNames(string $a, string $b): bool
	{
		return strcasecmp($a, $b) === 0;
	}

	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		$validKey = $this->validateKey($key);

		return parent::containsKey($validKey, $comparer ?? self::compareHeaderNames(...));
	}

	protected function validateKey(mixed $key): string
	{
		if ($key instanceof HeaderName) {
			return $key->value;
		}

		if (is_string($key)) {
			return $key;
		}

		throw new OffsetNotAllowedException($key);
	}

	public function get(mixed $key): array
	{
		$validKey = $this->validateKey($key);

		foreach ($this->items as $k => $v) {
			if (self::compareHeaderNames($k, $validKey)) {
				return $v;
			}
		}

		throw new OffsetNotFoundException($key);
	}

	public function put(mixed $key, mixed $value): bool
	{
		$validKey = $this->validateKey($key);

		$existed = $this->has($validKey);
		if ($existed) {
			foreach (array_keys($this->items) as $k) {
				if (self::compareHeaderNames($k, $validKey)) {
					$this->items[$k] = $value;
				}
			}
		} else {
			$this->items[$validKey] = $value;
		}

		return $existed;
	}

	public function has(mixed $key): bool
	{
		$validKey = $this->validateKey($key);

		if (parent::has($validKey)) {
			return true;
		}

		return $this->containsKey($validKey);
	}

	public function remove(mixed $key): bool
	{
		$validKey = $this->validateKey($key);

		if (!$this->has($validKey)) {
			return false;
		}

		$anyUnset = false;
		foreach (array_keys($this->items) as $k) {
			if (self::compareHeaderNames($k, $validKey)) {
				unset($this->items[$k]);

				$anyUnset = true;
			}
		}

		return $anyUnset;
	}
}
