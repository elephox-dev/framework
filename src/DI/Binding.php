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
	 * @var class-string<T>
	 */
	private string $contract;

	/**
	 * @var callable(ContainerContract): T
	 */
	private $builder;

	private InjectionLifetime $lifetime;

	/**
	 * @var T|null
	 */
	private ?object $instance = null;

	/**
	 * @param class-string<T> $contract
	 * @param callable(ContainerContract): T $builder
	 * @param InjectionLifetime $lifetime
	 */
	public function __construct(string $contract, callable $builder, InjectionLifetime $lifetime)
	{
		$this->contract = $contract;
		$this->builder = $builder;
		$this->lifetime = $lifetime;
	}

	public function getContract(): string
	{
		return $this->contract;
	}

	public function getLifetime(): InjectionLifetime
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
