<?php

namespace Philly\DI;

use JetBrains\PhpStorm\Pure;
use Throwable;

class MissingTypeHintException extends BindingBuilderException
{
	#[Pure] public function __construct(string $parameterName, ?string $class, string $method, int $code = 0, ?Throwable $previous = null)
	{
		$class ??= "global";

		parent::__construct(
			"Missing type hint for $parameterName in $class::$method",
			$code,
			$previous
		);
	}
}
