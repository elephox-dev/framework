<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Core\Handler\Contract\HandlerMeta;
use JetBrains\PhpStorm\Pure;
use LogicException;
use Throwable;

class InvalidRequestController extends LogicException
{
	#[Pure]
	public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		// TODO: change message to be more general and/or create more specific exceptions
		parent::__construct('Class ' . $className . ' implements __invoke() either with no or the wrong return type. It must return a \Elephox\Http\Contract\ResponseBuilder', $code, $previous);
	}
}
