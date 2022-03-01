<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * @template T as object
 *
 * @template-implements Contract\Binding<T>
 */
class Binding implements Contract\Binding
{
	/**
	 * @var T|null
	 */
	private ?object $instance = null;

	/**
	 * @param Closure(Contract\Container): T $builder
	 * @param InstanceLifetime $lifetime
	 */
	public function __construct(private Closure $builder, private readonly InstanceLifetime $lifetime)
	{
	}

	public function getLifetime(): InstanceLifetime
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

	#[ArrayShape(['lifetime' => InstanceLifetime::class, 'builder' => "string", 'instance' => "string"])]
	public function __serialize(): array
	{
		/**
		 * @noinspection PhpUnhandledExceptionInspection
		 */
		$builderWrapper = new SerializableClosure($this->builder);
		$data = [
			'lifetime' => serialize($this->lifetime),
			'builder' => serialize($builderWrapper),
		];

		if ($this->instance !== null && method_exists($this->instance, '__serialize')) {
			$data['instance'] = serialize($this->instance);
			$data['instance_class'] = get_class($this->instance);
		}

		return $data;
	}

	public function __unserialize(array $data): void
	{
		if (!array_key_exists('lifetime', $data)) {
			throw new InvalidArgumentException('Missing lifetime in serialized data');
		}

		if (!is_string($data['lifetime'])) {
			throw new InvalidArgumentException('Invalid lifetime in serialized data');
		}

		if (!array_key_exists('builder', $data)) {
			throw new InvalidArgumentException('Missing builder in serialized data');
		}

		/** @var InstanceLifetime */
		$this->lifetime = unserialize($data['lifetime'], ['allowed_classes' => [InstanceLifetime::class]]);

		if (!is_string($data['builder'])) {
			throw new InvalidArgumentException('Invalid builder in serialized data');
		}

		/** @var SerializableClosure */
		$builderWrapper = unserialize($data['builder'], ['allowed_classes' => [SerializableClosure::class]]);
		/**
		 * @noinspection PhpUnhandledExceptionInspection
		 * @var Closure(Contract\Container): T
		 */
		$this->builder = $builderWrapper->getClosure();

		if (array_key_exists('instance', $data)) {
			if (!is_string($data['instance'])) {
				throw new InvalidArgumentException('Invalid instance in serialized data');
			}

			if (!array_key_exists('instance_class', $data)) {
				throw new InvalidArgumentException('Missing instance class in serialized data');
			}

			if (!is_string($data['instance_class'])) {
				throw new InvalidArgumentException('Invalid instance class in serialized data');
			}

			/** @var T */
			$this->instance = unserialize($data['instance'], ['allowed_classes' => [$data['instance_class']]]);
		}
	}
}
