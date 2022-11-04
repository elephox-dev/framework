<?php
declare(strict_types=1);

namespace Elephox\Http;

use AppendIterator;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\KeyedEnumerable;
use Elephox\Collection\ObjectMap;
use Elephox\Collection\OffsetNotFoundException;
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

	public function get(string|int $key, ?ParameterSource $source = null): mixed
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		$candidateFound = false;
		$candidate = null;
		$candidateSource = null;

		foreach ($trySources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$parameterList = $this->parameters->get($parameterSource);
				if ($parameterList->has($key)) {
					if ($candidateFound) {
						/** @var ParameterSource $candidateSource */
						throw new RuntimeException("Ambiguous parameter key: '$key'. Found in both '$parameterSource->name' and '$candidateSource->name'.");
					}

					/** @var mixed $candidate */
					$candidate = $parameterList->get($key);
					$candidateSource = $parameterSource;
					$candidateFound = true;
				}
			}
		}

		if ($candidateFound) {
			return $candidate;
		}

		throw new OffsetNotFoundException("Key '$key' not found in parameter map.");
	}

	public function has(string|int $key, ?ParameterSource $source = null): bool
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		foreach ($trySources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$parameterList = $this->parameters->get($parameterSource);
				if ($parameterList->has($key)) {
					return true;
				}
			}
		}

		return false;
	}

	public function put(string|int $key, ParameterSource $source, mixed $value): void
	{
		if ($this->parameters->has($source)) {
			$this->parameters->get($source)->put($key, $value);
		} else {
			$this->parameters->put($source, new ArrayMap([$key => $value]));
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

	public static function fromGlobals(?array $post = null, ?array $get = null, ?array $server = null, ?array $env = null): Contract\ParameterMap
	{
		$post ??= $_POST;
		$get ??= $_GET;
		$server ??= $_SERVER;
		$env ??= $_ENV;

		$map = new self();

		foreach (
			[
				'PHP_SELF',
				'argv',
				'argc',
				'GATEWAY_INTERFACE',
				'SERVER_ADDR',
				'SERVER_NAME',
				'SERVER_SOFTWARE',
				'SERVER_PROTOCOL',
				'REQUEST_METHOD',
				'REQUEST_TIME',
				'REQUEST_TIME_FLOAT',
				'QUERY_STRING',
				'DOCUMENT_ROOT',
				'HTTPS',
				'REMOTE_ADDR',
				'REMOTE_HOST',
				'REMOTE_PORT',
				'REDIRECT_REMOTE_USER',
				'SCRIPT_FILENAME',
				'SERVER_ADMIN',
				'SERVER_PORT',
				'SERVER_SIGNATURE',
				'PATH_TRANSLATED',
				'SCRIPT_NAME',
				'REQUEST_URI',
				'PHP_AUTH_DIGEST',
				'PHP_AUTH_USER',
				'PHP_AUTH_PW',
				'AUTH_TYPE',
				'PATH_INFO',
				'ORIG_PATH_INFO',
				'CONTENT_LENGTH',
			] as $serverKey
		) {
			if (!array_key_exists($serverKey, $server)) {
				continue;
			}

			$map->put($serverKey, ParameterSource::Server, $server[$serverKey]);
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

		/**
		 * @psalm-suppress MixedReturnStatement
		 */
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
