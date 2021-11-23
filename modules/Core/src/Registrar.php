<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\DI\Contract\Container;

/**
 * @see Contract\Registrar
 *
 * @property-read list<class-string> $classes
 * @property-read array<non-empty-string, non-empty-string> $aliases
 */
trait Registrar
{
	public function registerClasses(Container $container): void
	{
		if (!property_exists($this, 'classes')) {
			return;
		}

		foreach ($this->classes as $class) {
			$container->register($class);
		}
	}

	public function registerAliases(Container $container): void
	{
		if (!property_exists($this, 'aliases')) {
			return;
		}

		foreach ($this->aliases as $alias => $contract) {
			$container->alias($alias, $contract);
		}
	}

	public function registerAll(Container $container): void
	{
		$this->registerClasses($container);
		$this->registerAliases($container);
	}
}
