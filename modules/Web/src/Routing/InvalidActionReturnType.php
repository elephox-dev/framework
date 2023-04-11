<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidActionReturnType extends LogicException
{
	#[Pure]
	public function __construct(string $className, string $methodName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Class ' . $className . '::' . $methodName . ' is registered as a request handler and has the wrong return type. It must return a \Elephox\Http\Contract\ResponseBuilder', $code, $previous);
	}
}
