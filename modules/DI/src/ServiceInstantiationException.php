<?php
declare(strict_types=1);

namespace Elephox\DI;

use Throwable;

class ServiceInstantiationException extends ServiceException
{
	public function __construct(public readonly string $serviceName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Failed to instantiate service: $serviceName", $code, $previous);
	}
}
