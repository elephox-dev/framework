<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Http\RequestMethod;

interface RouteAttribute
{
	public function getPath(): string;

	public function getWeight(): int;

	/**
	 * @return GenericList<RequestMethod>
	 */
	public function getMethods(): GenericList;
}
