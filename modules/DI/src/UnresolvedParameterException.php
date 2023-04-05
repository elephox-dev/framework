<?php
declare(strict_types=1);

namespace Elephox\DI;

use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class UnresolvedParameterException extends LogicException
{
	#[Pure]
	public function __construct(public readonly string $className, public readonly string $methodName, public readonly string $type, public readonly ?string $paramName = null, public readonly ?string $paramFilename = null, public readonly ?int $paramStartLine = null, public readonly ?int $paramEndLine = null, int $code = 0, ?Throwable $previous = null)
	{
		$msg = 'Could not resolve';
		if ($paramName !== null) {
			$msg .= " parameter $$paramName with";
		}

		$msg .= " type $type in $className::$methodName()";

		if ($paramFilename !== null) {
			$msg .= " at $paramFilename";
			if ($paramStartLine !== null && $paramEndLine !== null) {
				if ($paramStartLine === $paramEndLine) {
					$msg .= " in line $paramStartLine";
				} else {
					$msg .= " in lines $paramStartLine-$paramEndLine";
				}
			}
		}

		parent::__construct($msg, $code, $previous);
	}
}
