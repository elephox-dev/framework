<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidRequestHandler extends LogicException
{
	#[Pure]
	public function __construct(string $className, string $methodName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Method ' . $className . ' implements ' . $methodName . ' either with no or the wrong return type. It must return a \Elephox\Http\Contract\ResponseBuilder', $code, $previous);
	}
}
