<?php

namespace Elephox\DI;

use RuntimeException;
use Throwable;

class ServiceInstantiationException extends ServiceException
{
	public function __construct(string $serviceName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Failed to instantiate service '$serviceName'", $code, $previous);
	}
}
