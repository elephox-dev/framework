<?php
declare(strict_types=1);

namespace Elephox\DI;

class ResolverStack
{
	/**
	 * @var list<string>
	 */
	private array $stack = [];

	public function push(string $service): void
	{
		if (in_array($service, $this->stack, true)) {
			throw new CyclicDependencyException($service, array_reverse($this->stack));
		}

		$this->stack[] = $service;
	}

	public function pop(): ?string
	{
		return array_pop($this->stack);
	}
}
