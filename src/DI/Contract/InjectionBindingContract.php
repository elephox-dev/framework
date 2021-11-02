<?php

namespace Philly\DI\Contract;

use Philly\DI\InjectionLifetime;

/**
 * @template T
 */
interface InjectionBindingContract
{
	/**
	 * @return class-string<T>
	 */
	public function getContract(): string;

	public function getLifetime(): InjectionLifetime;

	/**
	 * @return callable(ContainerContract): T
	 */
	public function getBuilder(): callable;

	/**
	 * @return T|null
	 */
	public function getInstance(): ?object;

	/**
	 * @param T $instance
	 */
	public function setInstance(object $instance): void;
}
