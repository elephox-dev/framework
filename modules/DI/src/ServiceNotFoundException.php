<?php
declare(strict_types=1);

namespace Elephox\DI;

use Throwable;

class ServiceNotFoundException extends ServiceException
{
	public function __construct(string $serviceName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Service not found: $serviceName", $code, $previous);
	}
}
