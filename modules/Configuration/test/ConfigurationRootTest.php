<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Memory\MemoryConfigurationSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Elephox\Configuration\ConfigurationBuilder
 * @covers \Elephox\Configuration\ConfigurationRoot
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationSource
 * @covers \Elephox\Configuration\Memory\MemoryConfigurationProvider
 * @covers \Elephox\Collection\ObjectSet
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 * @covers \Elephox\Collection\Iterator\UniqueByIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Configuration\ConfigurationSection
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\OOR\Filter
 * @covers \Elephox\Configuration\ConfigurationPath
 * @uses \Elephox\Collection\IsKeyedEnumerable
 */
class ConfigurationRootTest extends TestCase
{
	public function testGetChildren(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertEquals(['this is', 'nested'], $root->getChildKeys()->toArray());
		$children = $root->getChildren();

		$firstChild = $children->first();
		self::assertEquals('this is', $firstChild->getKey());

		$nested = $children->skip(1)->first();
		self::assertEquals('nested', $nested->getKey());
		self::assertEquals(['nested:a', 'nested:c'], $nested->getChildKeys()->toArray());

		$nestedChildren = $nested->getChildren();
		$firstNestedChild = $nestedChildren->first();
		self::assertEquals('a', $firstNestedChild->getKey());
		self::assertEquals('nested:a', $firstNestedChild->getPath());
		self::assertEquals('b', $firstNestedChild->getValue());

		$secondNestedChild = $nestedChildren->skip(1)->first();
		self::assertEquals('c', $secondNestedChild->getKey());
		self::assertEquals('nested:c', $secondNestedChild->getPath());
		self::assertEquals(['nested:c:foo'], $secondNestedChild->getChildKeys()->toArray());

		self::assertEquals('bar', $secondNestedChild['foo']);
		self::assertEquals('bar', $root->offsetGet('nested:c:foo'));
	}

	public function testArrayAccess(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertEquals('bar', $root['nested:c:foo']);
		$root['nested:c:foo'] = 'baz';
		self::assertEquals('baz', $root['nested:c:foo']);

		self::assertTrue($root->offsetExists('nested:c:foo'));
		unset($root['nested:c:foo']);
		self::assertFalse($root->offsetExists('nested:c:foo'));
		self::assertNull($root['nested:c:foo']);

		$section = $root->getSection('nested');
		$section['c:foo'] = 'baz';
		self::assertTrue($section->offsetExists('c:foo'));
		self::assertEquals('baz', $section['c:foo']);
		$section['c:foo'] = 'bar';
		self::assertEquals('bar', $section['c:foo']);
		unset($section['c:foo']);
		self::assertFalse($section->offsetExists('c:foo'));
		self::assertNull($section['c:foo']);
	}

	public function testValue(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertEquals('bar', $root->getSection('nested:c:foo')->getValue());
		$root->getSection('nested:c:foo')->setValue('baz');
		self::assertEquals('baz', $root->getSection('nested:c:foo')->getValue());
		$root->getSection('nested:c:foo')->deleteValue();
		self::assertFalse($root->getSection('nested:c')->hasSection('foo'));
	}

	public function testHasSection(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertTrue($root->hasSection('nested:c'));
		self::assertTrue($root->hasSection('nested:c:foo'));
		self::assertFalse($root->hasSection('nested:c:foo:baz'));
		self::assertFalse($root->hasSection('nested:d'));
	}

	public function testInvalidOffsetUnset(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		$this->expectException(InvalidArgumentException::class);
		$root->offsetUnset(123);
	}

	public function testInvalidOffsetSet(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		$this->expectException(InvalidArgumentException::class);
		$root->offsetSet(123, 'test');
	}

	public function testInvalidOffsetSetValue(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		$this->expectException(InvalidArgumentException::class);
		$root->offsetSet('test', new stdClass());
	}

	public function testOffsetSetNoProviders(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$root = $configBuilder->build();

		$this->expectException(RuntimeException::class);
		$root->offsetSet('test', 'alpha');
	}

	public function testInvalidOffsetExists(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		$this->expectException(InvalidArgumentException::class);
		$root->offsetExists(123);
	}

	public function testInvalidOffsetGet(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		$this->expectException(InvalidArgumentException::class);
		$root->offsetGet(123);
	}
}
