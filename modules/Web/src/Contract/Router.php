<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Http\Contract\Request;
use ReflectionException;

interface Router
{
	public function getHandler(Request $request): RouteHandler;

	/**
	 * @param class-string $className
	 * @throws ReflectionException
	 */
	public function loadFromClass(string $className): static;

	public function loadFromNamespace(string $namespace): static;
}
