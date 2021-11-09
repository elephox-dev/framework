<?php
declare(strict_types=1);

namespace Philly\DI;

use JetBrains\PhpStorm\Pure;
use ReflectionParameter;
use Throwable;

class MissingTypeHintException extends BindingBuilderException
{
	#[Pure] public function __construct(ReflectionParameter $parameter, int $code = 0, ?Throwable $previous = null)
	{
		/** @psalm-suppress ImpureMethodCall */
		$class = $parameter->getDeclaringClass()?->getName() ?? "global";
		/** @psalm-suppress ImpureMethodCall */
		$method = $parameter->getDeclaringFunction()->getName();
		$parameterName = $parameter->getName();

		parent::__construct(
			"Missing type hint for $parameterName in $class::$method",
			$code,
			$previous
		);
	}
}
