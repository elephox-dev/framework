<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Http\ParameterSource;

interface ParameterMap
{
	public function get(string $key, ?ParameterSource $source = null): mixed;

	public function has(string $key, ?ParameterSource $source = null): bool;

	public function put(string $key, ParameterSource $source, mixed $value): void;

	public function remove(string $key, ?ParameterSource $source = null): void;

	/**
	 * @param ParameterSource|null $source
	 * @return GenericEnumerable<mixed>
	 */
	public function all(?ParameterSource $source = null): GenericEnumerable;
}
