<?php
declare(strict_types=1);

namespace Elephox\Http;

use AppendIterator;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\Enumerable;
use Elephox\Collection\ObjectMap;
use Elephox\Collection\OffsetNotFoundException;
use InvalidArgumentException;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\TentativeType;
use LogicException;

class ParameterMap implements Contract\ParameterMap
{
	/**
	 * @var ObjectMap<ParameterSource, GenericMap<string, mixed>>
	 */
	private ObjectMap $parameters;

	public function __construct()
	{
		$this->parameters = new ObjectMap();
	}

	public function get(string $key, ?ParameterSource $source = null): mixed
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		foreach ($trySources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$parameterList = $this->parameters->get($parameterSource);
				if ($parameterList->has($key)) {
					return $parameterList->get($key);
				}
			}
		}

		throw new OffsetNotFoundException("Key '$key' not found in parameter map.");
	}

	public function has(string $key, ?ParameterSource $source = null): bool
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

	public function put(string $key, ParameterSource $source, mixed $value): void
	{
		if ($this->parameters->has($source)) {
			$this->parameters->get($source)->put($key, $value);
		} else {
			$this->parameters->put($source, new ArrayMap([$key => $value]));
		}
	}

	public function remove(string $key, ?ParameterSource $source = null): void
	{
		$sources = $source ? [$source] : ParameterSource::cases();

		foreach ($sources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$parameterList = $this->parameters->get($parameterSource);
				$parameterList->remove($key);
			}
		}
	}

	public function all(?ParameterSource $source = null): GenericEnumerable
	{
		$trySources = $source ? [$source] : ParameterSource::cases();

		$iterator = new AppendIterator();

		foreach ($trySources as $parameterSource) {
			if ($this->parameters->has($parameterSource)) {
				$iterator->append($this->parameters->get($parameterSource)->getIterator());
			}
		}

		return new Enumerable($iterator);
	}

	public static function fromGlobals(?array $post = null, ?array $get = null, ?array $session = null, ?array $server = null, ?array $env = null): Contract\ParameterMap
	{
		$post ??= $_POST;
		$get ??= $_GET;
		$session ??= $_SESSION;
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
			] as $serverKey
		) {
			if (!array_key_exists($serverKey, $server)) {
				continue;
			}

			$map->put($serverKey, ParameterSource::Server, $server[$serverKey]);
		}

		foreach ($get as $name => $value) {
			$map->put($name, ParameterSource::Get, $value);
		}

		foreach ($post as $name => $value) {
			$map->put($name, ParameterSource::Post, $value);
		}

		foreach ($session as $name => $value) {
			$map->put($name, ParameterSource::Session, $value);
		}

		foreach ($env as $name => $value) {
			$map->put($name, ParameterSource::Env, $value);
		}

		return $map;
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Parameter map keys must be strings.');
		}

		return $this->has($offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Parameter map keys must be strings.');
		}

		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new LogicException('Cannot set parameter map values via array access. Use put() instead.');
	}

	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Parameter map keys must be strings.');
		}

		$this->remove($offset);
	}
}
