<?php
declare(strict_types=1);

namespace Elephox\DI;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

class CyclicDependencyException extends LogicException implements ContainerExceptionInterface
{
	/**
	 * @param list<string> $dependencies
	 */
	public function __construct(public readonly string $serviceName, public readonly array $dependencies, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('Cyclic dependencies found:%s depends on %s', $this->serviceName, implode(', which depends on ', $this->dependencies)), $code, $previous);
	}
}
