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
 *
 * @uses \Elephox\Collection\IsEnumerable
 *
 * @internal
 */
class MemoryConfigurationProviderTest extends TestCase
{
	public function testTryGet(): void
	{
		$provider = new MemoryConfigurationProvider(new MemoryConfigurationSource(['foo' => 'bar']));
		static::assertTrue($provider->tryGet('foo', $val));
		static::assertEquals('bar', $val);

		static::assertFalse($provider->tryGet('bar', $val));
		static::assertFalse($provider->tryGet('', $val));
		static::assertFalse($provider->tryGet('::', $val));
	}

	public function testRemove(): void
	{
		$provider = new MemoryConfigurationProvider(new MemoryConfigurationSource(['foo' => 'bar', 'nested' => ['foo' => 'bar']]));
		static::assertTrue($provider->tryGet('foo', $val));

		$provider->remove('foo');

		static::assertFalse($provider->tryGet('foo', $val));

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

		static::assertTrue($provider->getChildKeys('baz')->isEmpty());
	}
}
