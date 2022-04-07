<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Http\Contract\Request;
use ReflectionException;

interface Router
{
	public function getRouteHandler(Request $request): RouteHandler;

	/**
	 * @return iterable<mixed, RouteHandler>
	 */
	public function getRouteHandlers(): iterable;

	/**
	 * @param class-string $className
	 *
	 * @throws ReflectionException
	 */
	public function loadFromClass(string $className): static;

	public function loadFromNamespace(string $namespace): static;
}
