<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Memory\MemoryConfigurationProvider;
use Elephox\Configuration\Memory\MemoryConfigurationSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\ConfigurationBuilder
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\OOR\Filter
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Configuration\ConfigurationPath
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 * @covers \Elephox\Collection\ObjectSet
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationProvider
 * @covers \Elephox\Configuration\ConfigurationRoot
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationSource
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @internal
 */
class ConfigurationBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$source1 = new MemoryConfigurationSource([
			'foo' => 'bar',
			'baz' => [
				'qux' => 'quux',
				'corge' => [
					'grault' => 'garply',
				],
			],
		]);
		$source2 = new MemoryConfigurationSource([
			'baz' => [
				'qux' => 'corge',
				'carg' => [
					'grault' => 'waldo',
				],
			],
		]);

		$builder = new ConfigurationBuilder();
		$builder->add($source1);
		$builder->add($source2);

		$sources = $builder->getSources();
		static::assertNotEmpty($sources);
		static::assertCount(2, $sources);

		$root = $builder->build();
		static::assertNotEmpty($root);
		static::assertCount(2, $root->getProviders());

		$provider = $root->getProviders()->first();
		static::assertInstanceOf(MemoryConfigurationProvider::class, $provider);

		static::assertSame('bar', $root->offsetGet('foo'));
		static::assertSame('garply', $root->offsetGet('baz:corge:grault'));
		static::assertNull($root->offsetGet('baz:not:there'));
	}
}
