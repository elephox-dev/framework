<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidRouteTemplateException extends InvalidArgumentException
{
	#[Pure]
	public function __construct(public readonly string $template, string $message = '', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Invalid route template: '$template' ($message)", $code, $previous);
	}
}
