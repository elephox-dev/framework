<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Memory\MemoryConfigurationProvider;
use Elephox\Configuration\Memory\MemoryConfigurationSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationProvider
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationSource
 * @covers \Elephox\Configuration\ConfigurationPath
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\OOR\Filter
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsEnumerable
 *
 * @internal
 */
final class MemoryConfigurationProviderTest extends TestCase
{
	public function testTryGet(): void
	{
		$provider = new MemoryConfigurationProvider(new MemoryConfigurationSource(['foo' => 'bar']));
		self::assertTrue($provider->tryGet('foo', $val));
		self::assertSame('bar', $val);

		self::assertFalse($provider->tryGet('bar', $val));
		self::assertFalse($provider->tryGet('', $val));
		self::assertFalse($provider->tryGet('::', $val));
	}

	public function testRemove(): void
	{
		$provider = new MemoryConfigurationProvider(new MemoryConfigurationSource(['foo' => 'bar', 'nested' => ['foo' => 'bar']]));
		self::assertTrue($provider->tryGet('foo', $val));

		$provider->remove('foo');

		self::assertFalse($provider->tryGet('foo', $val));

		$provider->remove('nested:test');
		$provider->remove('');
		$provider->remove('::');
	}

	public function testInvalidUnserialize(): void
	{
		$serialized = <<<EOF
O:56:"Elephox\Configuration\Memory\MemoryConfigurationProvider":0:{}
EOF;

		$this->expectException(InvalidArgumentException::class);

		unserialize($serialized);
	}

	public function testGetInvalidChildKeys(): void
	{
		$provider = new MemoryConfigurationProvider(new MemoryConfigurationSource(['foo' => 'bar', 'nested' => ['foo' => 'bar']]));

		self::assertTrue($provider->getChildKeys('baz')->isEmpty());
	}
}
