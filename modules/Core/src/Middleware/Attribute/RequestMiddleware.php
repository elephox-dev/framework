<?php
declare(strict_types=1);

namespace Elephox\Core\Middleware\Attribute;

use Elephox\Core\ActionType;
use JetBrains\PhpStorm\Pure;

abstract class RequestMiddleware extends AbstractMiddlewareAttribute
{
	#[Pure] public function __construct(int $weight = 0)
	{
		parent::__construct(ActionType::Request, $weight);
	}
}
