<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Handler\Attribute\Contract\HandlerAttribute;
use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidClassCallableHandlerException extends LogicException
{
	#[Pure] public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Class ' . $className . ' is not callable despite having a ' . HandlerAttribute::class . ' attribute. Please implement __invoke() or move the attribute to a class method.', $code, $previous);
	}
}
