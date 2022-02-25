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
 */
class ConfigurationBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$source1 = new MemoryConfigurationSource([
			'foo' => "bar",
			'baz' => [
				'qux' => "quux",
				'corge' => [
					'grault' => "garply",
				],
			],
		]);
		$source2 = new MemoryConfigurationSource([
			'baz' => [
				'qux' => "corge",
				'carg' => [
					'grault' => "waldo",
				],
			],
		]);

		$builder = new ConfigurationBuilder();
		$builder->add($source1);
		$builder->add($source2);

		$sources = $builder->getSources();
		self::assertNotEmpty($sources);
		self::assertCount(2, $sources);

		$root = $builder->build();
		self::assertNotEmpty($root);
		self::assertCount(2, $root->getProviders());

		$provider = $root->getProviders()->first();
		self::assertInstanceOf(MemoryConfigurationProvider::class, $provider);

		self::assertEquals('bar', $root->offsetGet('foo'));
		self::assertEquals('garply', $root->offsetGet('baz:corge:grault'));
		self::assertNull($root->offsetGet('baz:not:there'));
	}
}
