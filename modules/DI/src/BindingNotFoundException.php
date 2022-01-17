<?php
declare(strict_types=1);

namespace Elephox\DI;

use JetBrains\PhpStorm\Pure;
use Throwable;

class BindingNotFoundException extends BindingException
{
	#[Pure]
	public function __construct(string $contract, ?string $paramName = null, int $code = 0, ?Throwable $previous = null)
	{
		$msg = "Binding not found for contract '$contract'";
		if ($paramName !== null)
		{
			$msg .= " (Parameter: $$paramName)";
		}

		parent::__construct($msg, $code, $previous);
	}
}
