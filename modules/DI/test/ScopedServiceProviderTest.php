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
class ScopedServiceProviderTest extends TestCase
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

		$instance1 = $ssp->require(TestServiceInterface::class);
		$instance2 = $ssp->require(TestServiceInterface::class);

		static::assertSame($instance1, $instance2);
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

		static::assertNotSame($scope1, $scope2);

		$ssp1 = $scope1->services();
		$ssp2 = $scope2->services();

		static::assertNotSame($ssp1, $ssp2);

		$instance1 = $ssp1->require(TestServiceInterface::class);
		$instance2 = $ssp2->require(TestServiceInterface::class);

		static::assertNotSame($instance1, $instance2);
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

		$singleton1 = $sp->require(TestServiceInterface::class);
		$singleton2 = $scopedSp->require(TestServiceInterface::class);
		$singleton3 = $nestedScopedSp->require(TestServiceInterface::class);

		static::assertSame($singleton1, $singleton2);
		static::assertSame($singleton2, $singleton3);

		$transient1 = $sp->require(TestServiceInterface3::class);
		$transient2 = $scopedSp->require(TestServiceInterface3::class);
		$transient3 = $nestedScopedSp->require(TestServiceInterface3::class);

		static::assertNotSame($transient1, $transient2);
		static::assertNotSame($transient2, $transient3);

		$scoped1 = $scopedSp->require(TestServiceInterface2::class);
		$scoped2 = $nestedScopedSp->require(TestServiceInterface2::class);

		static::assertNotSame($scoped1, $scoped2);

		$this->expectException(ServiceException::class);
		$this->expectExceptionMessage("Cannot resolve service '" . TestServiceInterface2::class . "' from " . ServiceProvider::class . ', as it requires a scope.');

		$sp->require(TestServiceInterface2::class);
	}
}
