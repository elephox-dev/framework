<?php
declare(strict_types=1);

namespace Elephox\DI;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ServiceNotFoundException extends ServiceException implements NotFoundExceptionInterface
{
	public function __construct(public readonly string $serviceName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Service not found: $serviceName", $code, $previous);
	}
}
