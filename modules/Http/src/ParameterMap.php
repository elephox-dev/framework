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
}
