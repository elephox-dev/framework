<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\DI\Contract\Container;

/**
 * @see Contract\Registrar
 */
trait Registrar
{
	/** @var list<class-string> $classes */
	public array $classes;

	/** @var array<non-empty-string, object> $instances */
	public array $instances;

	/** @var array<non-empty-string, non-empty-string> $aliases */
	public array $aliases;

	public function registerClasses(Container $container): void
	{
		foreach ($this->classes as $class) {
			$container->register($class);
		}
	}

	public function registerInstances(Container $container): void
	{
		foreach ($this->instances as $contract => $instance) {
			$container->register($contract, $instance);
		}
	}

	public function registerAliases(Container $container): void
	{
		foreach ($this->aliases as $alias => $contract) {
			$container->alias($alias, $contract);
		}
	}

	public function registerAll(Container $container): void
	{
		$this->registerClasses($container);
		$this->registerInstances($container);
		$this->registerAliases($container);
	}
}
