<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;
use Elephox\Http\ParameterSource;

/**
 * @extends GenericMap<string, mixed>
 */
interface ParameterMap extends GenericMap
{
	public function getSource(string $key): ParameterSource;
}
