<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Contract\ServiceProvider as ServiceProviderContract;
use Elephox\DI\Contract\ServiceScope;
use Elephox\DI\Contract\ScopedServiceProvider as ScopedServiceProviderContract;
use Elephox\DI\Contract\ServiceScopeFactory;
use LogicException;

readonly class ScopedServiceProvider extends ServiceProvider implements ScopedServiceProviderContract
{
	/**
	 * @param iterable<ServiceDescriptor> $scopedServices
	 */
	public function __construct(
		private ServiceProviderContract $root,
		iterable $scopedServices = [],
	) {
		parent::__construct();

		/** @var ServiceDescriptor $description */
		foreach ($scopedServices as $descriptor) {
			if ($descriptor->lifetime !== ServiceLifetime::Scoped) {
				throw new LogicException('Can only add scoped services to ScopedServiceProvider');
			}

			$this->descriptors->put($descriptor->serviceType, $descriptor);
		}
	}

	public function has(string $id): bool
	{
		return parent::has($id) || $this->root->has($id);
	}

	/**
	 * @psalm-suppress all
	 */
	protected function getDescriptor(string $id): ServiceDescriptor
	{
		if (parent::has($id)) {
			return parent::getDescriptor($id);
		}

		return $this->root->getDescriptor($id);
	}

	public function createScope(): ServiceScope
	{
		$factory = $this->root->get(ServiceScopeFactory::class);

		return $factory->createScope();
	}

	/**
	 * @psalm-suppress all
	 */
	protected function requireTransient(ServiceDescriptor $descriptor): object
	{
		return $this->root->requireTransient($descriptor);
	}

	/**
	 * @psalm-suppress all
	 */
	protected function requireSingleton(ServiceDescriptor $descriptor): object
	{
		return $this->root->requireSingleton($descriptor);
	}

	protected function requireScoped(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Scoped, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Scoped->name, $descriptor->lifetime->name));

		return $this->getOrCreateInstance($descriptor);
	}
}
