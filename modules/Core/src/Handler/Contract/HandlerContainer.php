<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\UnhandledContextException;

interface HandlerContainer
{
	public function register(HandlerBinding $binding): void;

	/**
	 * @param class-string $className
	 * @return static
	 */
	public function loadFromClass(string $className): static;

	/**
	 * @param non-empty-string $namespace
	 * @return static
	 */
	public function loadFromNamespace(string $namespace): static;

	/**
	 * @throws UnhandledContextException
	 */
	public function findHandler(Context $context): HandlerBinding;
}
