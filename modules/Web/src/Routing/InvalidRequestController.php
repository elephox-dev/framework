<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidRequestController extends LogicException
{
	#[Pure]
	public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Class ' . $className . ' defines an invalid request controller', $code, $previous);
	}
}
