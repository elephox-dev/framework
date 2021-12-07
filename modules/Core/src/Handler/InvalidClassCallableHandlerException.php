<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Handler\Contract\HandlerMeta;
use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidClassCallableHandlerException extends LogicException
{
	#[Pure] public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Class ' . $className . ' is not callable despite having a ' . HandlerMeta::class . ' attribute. Please implement __invoke() or move the attribute to a class method.', $code, $previous);
	}
}
