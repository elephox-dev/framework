<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceClass2;
use Elephox\DI\Data\TestServiceClass3;
use Elephox\DI\Data\TestServiceInterface;
use Elephox\DI\Data\TestServiceInterface2;
use Elephox\DI\Data\TestServiceInterface3;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\DI\ScopedServiceProvider
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\ServiceProvider
 * @covers \Elephox\DI\ServiceScope
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ScopedServiceProviderTest extends TestCase
{
	public function testScopedServiceProviderHandleScopedServicesAsSingletons(): void
	{
		$scopedDescriptor = new ServiceDescriptor(
			TestServiceInterface::class,
			TestServiceClass::class,
			ServiceLifetime::Scoped,
			static fn () => new TestServiceClass(),
			null,
		);

		$sp = new ServiceProvider([$scopedDescriptor]);
		$scope = $sp->createScope();
		$ssp = $scope->services();

		$instance1 = $ssp->get(TestServiceInterface::class);
		$instance2 = $ssp->get(TestServiceInterface::class);

		self::assertSame($instance1, $instance2);
	}

	public function testScopedServiceProvidersCreateDifferentInstances(): void
	{
		$scopedDescriptor = new ServiceDescriptor(
			TestServiceInterface::class,
			TestServiceClass::class,
			ServiceLifetime::Scoped,
			static fn () => new TestServiceClass(),
			null,
		);

		$sp = new ServiceProvider([$scopedDescriptor]);

		$scope1 = $sp->createScope();
		$scope2 = $sp->createScope();

		self::assertNotSame($scope1, $scope2);

		$ssp1 = $scope1->services();
		$ssp2 = $scope2->services();

		self::assertNotSame($ssp1, $ssp2);

		$instance1 = $ssp1->get(TestServiceInterface::class);
		$instance2 = $ssp2->get(TestServiceInterface::class);

		self::assertNotSame($instance1, $instance2);
	}

	public function testScopedServiceProviderUsesRootProvider(): void
	{
		$sp = new ServiceProvider([
			new ServiceDescriptor(
				TestServiceInterface::class,
				TestServiceClass::class,
				ServiceLifetime::Singleton,
				null,
				new TestServiceClass(),
			),
			new ServiceDescriptor(
				TestServiceInterface3::class,
				TestServiceClass3::class,
				ServiceLifetime::Transient,
				static fn () => new TestServiceClass3(),
				null,
			),
			new ServiceDescriptor(
				TestServiceInterface2::class,
				TestServiceClass2::class,
				ServiceLifetime::Scoped,
				static fn () => new TestServiceClass2(),
				null,
			),
		]);

		$scope = $sp->createScope();
		$scopedSp = $scope->services();

		$nestedScope = $scopedSp->createScope();
		$nestedScopedSp = $nestedScope->services();

		$singleton1 = $sp->get(TestServiceInterface::class);
		$singleton2 = $scopedSp->get(TestServiceInterface::class);
		$singleton3 = $nestedScopedSp->get(TestServiceInterface::class);

		self::assertSame($singleton1, $singleton2);
		self::assertSame($singleton2, $singleton3);

		$transient1 = $sp->get(TestServiceInterface3::class);
		$transient2 = $scopedSp->get(TestServiceInterface3::class);
		$transient3 = $nestedScopedSp->get(TestServiceInterface3::class);

		self::assertNotSame($transient1, $transient2);
		self::assertNotSame($transient2, $transient3);

		$scoped1 = $scopedSp->get(TestServiceInterface2::class);
		$scoped2 = $nestedScopedSp->get(TestServiceInterface2::class);

		self::assertNotSame($scoped1, $scoped2);

		$this->expectException(ServiceException::class);
		$this->expectExceptionMessage("Cannot resolve service '" . TestServiceInterface2::class . "' from " . ServiceProvider::class . ', as it requires a scope.');

		$sp->get(TestServiceInterface2::class);
	}

	public function testScopedServiceProviderCleansUpInstancesOnEnd(): void {
		$sp = new ServiceProvider([
			new ServiceDescriptor(
				TestServiceInterface::class,
				TestServiceClass::class,
				ServiceLifetime::Scoped,
				static fn () => new TestServiceClass(),
				null,
			),
		]);
		$scope = $sp->createScope();
		$ssp = $scope->services();

		$instance1 = $ssp->get(TestServiceInterface::class);
		$instance2 = $ssp->get(TestServiceInterface::class);

		self::assertSame($instance1, $instance2);

		$scope->endScope();

		$instance3 = $ssp->get(TestServiceInterface::class);

		self::assertNotSame($instance1, $instance3);
	}
}
