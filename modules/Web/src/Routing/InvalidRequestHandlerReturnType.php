<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidRequestHandlerReturnType extends LogicException
{
	#[Pure]
	public function __construct(string $className, string $methodName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Method ' . $className . '::' . $methodName . ' must return a \Elephox\Http\Contract\ResponseBuilder', $code, $previous);
	}
}
