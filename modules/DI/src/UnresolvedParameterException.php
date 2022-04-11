<?php
declare(strict_types=1);

namespace Elephox\DI;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class UnresolvedParameterException extends LogicException
{
	#[Pure]
	public function __construct(string $className, string $methodName, string $type, ?string $paramName = null, int $code = 0, ?Throwable $previous = null)
	{
		$msg = 'Could not resolve';
		if ($paramName !== null) {
			$msg .= " parameter $$paramName with";
		}
		$msg .= " type $type in $className::$methodName()";

		parent::__construct($msg, $code, $previous);
	}
}
