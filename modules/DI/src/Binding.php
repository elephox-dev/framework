<?php
declare(strict_types=1);

namespace Elephox\DI;

/**
 * @template T as object
 *
 * @template-implements Contract\Binding<T>
 */
class Binding implements Contract\Binding
{
	/**
	 * @var callable(Contract\Container): T
	 */
	private $builder;

	private BindingLifetime $lifetime;

	/**
	 * @var T|null
	 */
	private ?object $instance = null;

	/**
	 * @param callable(Contract\Container): T $builder
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
	 * @return callable(Contract\Container): T
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
