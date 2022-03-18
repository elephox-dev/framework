<?php
declare(strict_types=1);

namespace Elephox\DI;

use JetBrains\PhpStorm\Pure;
use Throwable;

class UnresolvedParameterException extends BindingException
{
	#[Pure]
	public function __construct(string $type, ?string $paramName = null, int $code = 0, ?Throwable $previous = null)
	{
		$msg = "Could not resolve";
		if ($paramName !== null)
		{
			$msg .= " parameter $$paramName with";
		}
		$msg .= " type $type";

		parent::__construct($msg, $code, $previous);
	}
}
