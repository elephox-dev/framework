<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\DI\BindingLifetime;

/**
 * @template T
 */
interface Binding
{
	public function getLifetime(): BindingLifetime;

	/**
	 * @return callable(Container): T
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