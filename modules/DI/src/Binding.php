<?php

namespace Philly\DI;

use Philly\DI\Contract\ContainerContract;
use Philly\DI\Contract\InjectionBindingContract;

/**
 * @template T as object
 *
 * @template-implements InjectionBindingContract<T>
 */
class Binding implements InjectionBindingContract
{
	/**
	 * @var callable(ContainerContract): T
	 */
	private $builder;

	private BindingLifetime $lifetime;

	/**
	 * @var T|null
	 */
	private ?object $instance = null;

	/**
	 * @param callable(ContainerContract): T $builder
	 * @param BindingLifetime $lifetime
	 */
	public function __construct(callable $builder, BindingLifetime $lifetime)
	{
		$this->builder = $builder;
		$this->lifetime = $lifetime;
	}

	public function getLifetime(): BindingLifetime
	{
		return $this->lifetime;
	}

	/**
	 * @return callable(ContainerContract): T
	 */
	public function getBuilder(): callable
	{
		return $this->builder;
	}

	/**
	 * @return T|null
	 */
	public function getInstance(): ?object
	{
		return $this->instance;
	}

	/**
	 * @param T $instance
	 */
	public function setInstance(object $instance): void
	{
		$this->instance = $instance;
	}
}
