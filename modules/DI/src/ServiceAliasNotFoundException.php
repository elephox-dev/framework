<?php
declare(strict_types=1);

namespace Elephox\DI;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ServiceAliasNotFoundException extends ServiceException implements NotFoundExceptionInterface
{
	public function __construct(public readonly string $alias, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Service alias not found: $alias", $code, $previous);
	}
}
