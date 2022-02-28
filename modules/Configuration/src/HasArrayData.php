<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\OOR\Str;
use Generator;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;

trait HasArrayData
{
	/** @var array<string, mixed> */
	protected array $data;

	public function getChildKeys(string|Str|null $path = null): GenericEnumerable
	{
		return new Enumerable(function () use ($path): Generator {
			if (empty($path)) {
				/** @psalm-suppress MixedAssignment */
				foreach (array_keys($this->data) as $key) {
					yield $key;
				}
			} else {
				$keyParts = ConfigurationPath::getSectionKeys($path);
				$data = $this->data;

				while (!$keyParts->isEmpty()) {
					$keyPart = $keyParts->shift();
					if (!isset($data[$keyPart])) {
						return;
					}

					$data = $data[$keyPart];
					if ($keyParts->isEmpty()) {
						if (!is_array($data)) {
							return;
						}

						/** @psalm-suppress MixedAssignment */
						foreach (array_keys($data) as $key) {
							yield $key;
						}
					}
				}
			}
		});
	}

	public function set(string|Str $key, string|float|bool|int|null $value): void
	{
		$keys = ConfigurationPath::getSectionKeys($key);
		$data = &$this->data;
		while (!$keys->isEmpty()) {
			/** @var string $keyPart */
			$keyPart = $keys->shift();

			/**
			 * @psalm-suppress MixedArrayAccess
			 * @psalm-suppress MixedAssignment
			 */
			$data = &$data[$keyPart];
		}

		$data = $value;
	}

	public function tryGet(string|Str $key, string|float|bool|int|null &$value = null): bool
	{
		$keys = ConfigurationPath::getSectionKeys($key);
		$data = $this->data;
		while (!$keys->isEmpty()) {
			/** @var string $keyPart */
			$keyPart = $keys->shift();
			if (is_array($data) && array_key_exists($keyPart, $data)) {
				/** @psalm-suppress MixedAssignment */
				$data = $data[$keyPart];

				if ($keys->isEmpty()) {
					/** @psalm-suppress MixedAssignment */
					$value = $data;
					return true;
				}
			} else {
				return false;
			}
		}

		return false;
	}

	public function remove(string|Str $key): void
	{
		$keys = ConfigurationPath::getSectionKeys($key);
		$data = &$this->data;
		while (!$keys->isEmpty()) {
			/** @var string $keyPart */
			$keyPart = $keys->shift();
			if (is_array($data) && array_key_exists($keyPart, $data)) {
				if ($keys->isEmpty()) {
					unset($data[$keyPart]);

					return;
				}

				/** @psalm-suppress MixedAssignment */
				$data = &$data[$keyPart];
			} else {
				return;
			}
		}
	}

	#[ArrayShape(['data' => "array"])]
	public function __serialize(): array
	{
		return [
			'data' => $this->data,
		];
	}

	public function __unserialize(array $data): void
	{
		if (!array_key_exists('data', $data)) {
			throw new InvalidArgumentException('Missing "data" key in serialized data');
		}

		/** @var array<string, mixed> */
		$this->data = $data['data'];
	}
}
