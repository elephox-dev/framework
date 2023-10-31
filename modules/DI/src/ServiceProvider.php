<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\DI\Contract\Disposable;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceProvider as ServiceProviderContract;
use Elephox\DI\Contract\ServiceScope as ServiceScopeContract;

/**
 * @psalm-type argument-list = array<non-empty-string, mixed>
 */
readonly class ServiceProvider implements ServiceProviderContract, Disposable
{
	/**
	 * @var ArrayMap<string, ServiceDescriptor<object, object>>
	 */
	protected ArrayMap $descriptors;

	/** @var ArrayMap<class-string, object>
	 */
	protected ArrayMap $instances;

	private array $selfIds;

	private Resolver $resolver;

	/**
	 * @param iterable<ServiceDescriptor> $descriptors
	 */
	public function __construct(iterable $descriptors = [], ?Resolver $resolver = null, private ?ServiceProviderContract $root = null)
	{
		$this->descriptors = new ArrayMap();
		$this->instances = new ArrayMap();

		/** @var ServiceDescriptor $description */
		foreach ($descriptors as $descriptor) {
			$this->descriptors->put($descriptor->serviceType, $descriptor);
		}

		$interfaces = class_implements($this);

		assert($interfaces !== false);

		$this->selfIds = [
			self::class,
			...$interfaces,
		];

		$this->resolver = $resolver ?? new DynamicResolver($this);
		$this->instances->put(Resolver::class, $this->resolver);
		$this->descriptors->put(Resolver::class, new ServiceDescriptor(Resolver::class, $this->resolver::class, ServiceLifetime::Singleton, null, $this->resolver));
	}

	protected function isSelf(string $id): bool
	{
		return in_array($id, $this->selfIds, true);
	}

	public function has(string $id): bool
	{
		return $this->isSelf($id) || $this->descriptors->has($id) || ($this->root !== null && $this->root->has($id));
	}

	protected function getDescriptor(string $id): ServiceDescriptor
	{
		try {
			return $this->descriptors->get($id);
		} catch (OffsetNotFoundException $e) {
			if ($this->root !== null) {
				return $this->root->getDescriptor($id);
			}

			throw new ServiceNotFoundException($id, previous: $e);
		}
	}

	/**
	 * @template TService of object
	 *
	 * @param string|class-string<TService> $id
	 *
	 * @return TService
	 */
	public function get(string $id): object
	{
		if ($this->isSelf($id)) {
			/** @var TService */
			return $this;
		}

		if ($id === Resolver::class) {
			return $this->resolver;
		}

		$descriptor = $this->getDescriptor($id);

		/** @var TService */
		return match ($descriptor->lifetime) {
			ServiceLifetime::Transient => $this->requireTransient($descriptor),
			ServiceLifetime::Singleton => $this->requireSingleton($descriptor),
			ServiceLifetime::Scoped => $this->requireScoped($descriptor),
		};
	}

	protected function requireTransient(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Transient, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Transient->name, $descriptor->lifetime->name));

		return $this->createInstance($descriptor);
	}

	protected function requireSingleton(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Singleton, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Singleton->name, $descriptor->lifetime->name));

		return $this->getOrCreateInstance($descriptor);
	}

	protected function requireScoped(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Scoped, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Scoped->name, $descriptor->lifetime->name));

		if ($this->root === null) {
			throw new ServiceException(sprintf(
				"Cannot resolve service '%s' from %s, as it requires a scope.",
				$descriptor->serviceType,
				get_debug_type($this),
			));
		}

		return $this->getOrCreateInstance($descriptor);
	}

	protected function getOrCreateInstance(ServiceDescriptor $descriptor): object
	{
		if ($this->instances->has($descriptor->serviceType)) {
			$service = $this->instances->get($descriptor->serviceType);
		} else {
			$service = $this->createInstance($descriptor);

			$this->instances->put($descriptor->serviceType, $service);
		}

		return $service;
	}

	protected function createInstance(ServiceDescriptor $descriptor): object
	{
		try {
			return $descriptor->createInstance($this->resolver);
		} catch (BadFunctionCallException $e) {
			throw new ServiceInstantiationException($descriptor->serviceType, previous: $e);
		}
	}

	public function createScope(): ServiceScopeContract
	{
		$scopedProvider = new self(
			$this->descriptors->where(static fn (ServiceDescriptor $d) => $d->lifetime === ServiceLifetime::Scoped),
			root: $this,
		);

		return new ServiceScope($scopedProvider);
	}

	public function dispose(): void
	{
		foreach ($this->instances as $instance) {
			if ($instance instanceof Disposable && !$instance instanceof self) {
				$instance->dispose();
			}
		}

		$this->instances->clear();
	}
}
