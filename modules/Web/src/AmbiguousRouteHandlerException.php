<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Http\Contract\Request;
use RuntimeException;
use Throwable;

class AmbiguousRouteHandlerException extends RuntimeException
{
	/**
	 * @param Request $request
	 */
	public function __construct(public readonly Request $request, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Multiple handlers matched route '{$this->request->getUrl()}'", $code, $previous);
	}
}
