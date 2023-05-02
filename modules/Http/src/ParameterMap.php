<?php
declare(strict_types=1);

namespace Elephox\Http;

use AppendIterator;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\KeyedEnumerable;
use Elephox\Collection\ObjectMap;
use Generator;
use Iterator;
use IteratorIterator;
use LogicException;
use RuntimeException;

class ParameterMap implements Contract\ParameterMap
{
	/**
	 * @var ObjectMap<ParameterSource, GenericMap<array-key, mixed>>
	 */
	private ObjectMap $parameters;

	public function __construct()
	{
		$this->parameters = new ObjectMap();
	}

	public function get(string|int $key, ?ParameterSource $source = null, mixed $default = null): mixed
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		$candidateFound = false;
		$candidate = null;
		$candidateSource = null;

		foreach ($trySources as $parameterSource) {
			if (!$this->parameters->has($parameterSource)) {
				continue;
			}

			$parameterList = $this->parameters->get($parameterSource);
			if (!$parameterList->has($key)) {
				continue;
			}

			if ($candidateFound) {
				/** @var ParameterSource $candidateSource */
				throw new RuntimeException("Ambiguous parameter key: '$key'. Found in both '$parameterSource->name' and '$candidateSource->name'.");
			}

			/** @var mixed $candidate */
			$candidate = $parameterList->get($key);
			$candidateSource = $parameterSource;
			$candidateFound = true;
		}

		if ($candidateFound) {
			return $candidate;
		}

		return $default;
	}

	public function has(string|int $key, ?ParameterSource $source = null): bool
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		foreach ($trySources as $parameterSource) {
			if (!$this->parameters->has($parameterSource)) {
				continue;
			}

			$parameterList = $this->parameters->get($parameterSource);
			if ($parameterList->has($key)) {
				return true;
			}
		}

		return false;
	}

	public function put(string|int $key, ParameterSource $source, mixed $value): void
	{
		if ($this->parameters->has($source)) {
			$this->parameters->get($source)->put($key, $value);
		} else {
			/** @var GenericMap<string|int, mixed> $map */
			$map = new ArrayMap([$key => $value]);

			$this->parameters->put($source, $map);
		}
	}

	public function remove(string|int $key, ?ParameterSource $source = null): void
	{
		$sources = $source ? [$source] : ParameterSource::cases();

		foreach ($sources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$parameterList = $this->parameters->get($parameterSource);
				$parameterList->remove($key);
			}
		}
	}

	public function all(string $key): GenericKeyedEnumerable
	{
		/** @var KeyedEnumerable<ParameterSource, mixed> */
		return new KeyedEnumerable(function () use ($key): Generator {
			foreach (ParameterSource::cases() as $source) {
				if ($this->parameters->has($source)) {
					$parameterList = $this->parameters->get($source);
					if ($parameterList->has($key)) {
						yield $source => $parameterList->get($key);
					}
				}
			}
		});
	}

	public function allFrom(?ParameterSource $source = null): GenericKeyedEnumerable
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		$iterator = new AppendIterator();

		foreach ($trySources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$sourceIterator = $this->parameters->get($parameterSource)->getIterator();
				if (!($sourceIterator instanceof Iterator)) {
					$sourceIterator = new IteratorIterator($sourceIterator);
				}
				$iterator->append($sourceIterator);
			}
		}

		/** @var KeyedEnumerable<array-key, mixed> */
		return new KeyedEnumerable($iterator);
	}

	public static function fromGlobals(?array $post = null, ?array $get = null, ?array $server = null, ?array $env = null, ?array $attributes = null): Contract\ParameterMap
	{
		$post ??= $_POST;
		$get ??= $_GET;
		$server ??= $_SERVER;
		$env ??= $_ENV;
		$attributes ??= [];

		$map = new self();

		/**
		 * @var mixed $value
		 */
		foreach ($server as $name => $value) {
			$map->put($name, ParameterSource::Server, $value);
		}

		/**
		 * @var mixed $value
		 */
		foreach ($get as $name => $value) {
			$map->put($name, ParameterSource::Get, $value);
		}

		/**
		 * @var mixed $value
		 */
		foreach ($post as $name => $value) {
			$map->put($name, ParameterSource::Post, $value);
		}

		/**
		 * @var mixed $value
		 */
		foreach ($env as $name => $value) {
			$map->put($name, ParameterSource::Env, $value);
		}

		/**
		 * @var mixed $value
		 */
		foreach ($attributes as $name => $value) {
			$map->put($name, ParameterSource::Attribute, $value);
		}

		return $map;
	}

	public function offsetExists(mixed $offset): bool
	{
		assert(is_string($offset), 'Parameter map keys must be strings.');

		return $this->has($offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		assert(is_string($offset), 'Parameter map keys must be strings.');

		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new LogicException('Cannot set parameter map values via array access. Use put() instead.');
	}

	public function offsetUnset(mixed $offset): void
	{
		assert(is_string($offset), 'Parameter map keys must be strings.');

		$this->remove($offset);
	}
}
