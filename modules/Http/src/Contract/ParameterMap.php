<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Http\ParameterSource;

interface ParameterMap extends ArrayAccess
{
	public static function fromGlobals(?array $post = null, ?array $get = null, ?array $session = null, ?array $server = null, ?array $env = null): ParameterMap;

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
