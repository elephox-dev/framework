<?php
declare(strict_types=1);

namespace Elephox\DI;

use RuntimeException;
use Throwable;

class ServiceAliasNotFoundException extends ServiceException
{
	public function __construct(string $alias, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Service alias not found: $alias", $code, $previous);
	}
}
