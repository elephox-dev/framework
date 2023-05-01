<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Memory\MemoryConfigurationSource;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Stringable;
use Traversable;

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
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ConfigurationRootTest extends TestCase
{
	public function testGetChildren(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertSame(['this is', 'nested'], $root->getChildKeys()->toArray());
		$children = $root->getChildren();

		$firstChild = $children->first();
		self::assertSame('this is', $firstChild->getKey());

		$nested = $children->skip(1)->first();
		self::assertSame('nested', $nested->getKey());
		self::assertSame(['nested:a', 'nested:c'], $nested->getChildKeys()->toArray());

		$nestedChildren = $nested->getChildren();
		$firstNestedChild = $nestedChildren->first();
		self::assertSame('a', $firstNestedChild->getKey());
		self::assertSame('nested:a', $firstNestedChild->getPath());
		self::assertSame('b', $firstNestedChild->getValue());

		$secondNestedChild = $nestedChildren->skip(1)->first();
		self::assertSame('c', $secondNestedChild->getKey());
		self::assertSame('nested:c', $secondNestedChild->getPath());
		self::assertSame(['nested:c:foo'], $secondNestedChild->getChildKeys()->toArray());

		self::assertSame('bar', $secondNestedChild['foo']);
		self::assertSame('bar', $root->offsetGet('nested:c:foo'));
	}

	public function testArrayAccess(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertSame('bar', $root['nested:c:foo']);
		$root['nested:c:foo'] = 'baz';
		self::assertSame('baz', $root['nested:c:foo']);

		self::assertTrue($root->offsetExists('nested:c:foo'));
		unset($root['nested:c:foo']);
		self::assertFalse($root->offsetExists('nested:c:foo'));
		self::assertNull($root['nested:c:foo']);

		$section = $root->getSection('nested');
		$section['c:foo'] = 'baz';
		self::assertTrue($section->offsetExists('c:foo'));
		self::assertSame('baz', $section['c:foo']);
		$section['c:foo'] = 'bar';
		self::assertSame('bar', $section['c:foo']);
		unset($section['c:foo']);
		self::assertFalse($section->offsetExists('c:foo'));
		self::assertNull($section['c:foo']);
	}

	public function testValue(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		self::assertSame('bar', $root->getSection('nested:c:foo')->getValue());
		$root->getSection('nested:c:foo')->setValue('baz');
		self::assertSame('baz', $root->getSection('nested:c:foo')->getValue());
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

	public function testSubstitutesEnvironmentVariables(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource([
			'test' => 'this is an env value: ${TEST_VAR}',
			'test2' => 'this env should not be replaced: $${TEST_VAR}',
			'test3' => 'this env should remain: ${NOT_A_VAR}',
			'test4' => [
				'test' => 'this is a nested env value: ${TEST_VAR}',
				'not-a-string-and-not-iterable' => 2,
			],
		]));
		$root = $configBuilder->build();

		$_ENV['TEST_VAR'] = 'secret!';
		unset($_ENV['NOT_A_VAR']);

		self::assertSame('this is an env value: secret!', $root->getSection('test')->getValue());
		self::assertSame('this env should not be replaced: ${TEST_VAR}', $root->getSection('test2')->getValue());
		self::assertSame('this env should remain: ${NOT_A_VAR}', $root->getSection('test3')->getValue());
		self::assertSame(['test' => 'this is a nested env value: secret!', 'not-a-string-and-not-iterable' => 2], $root->getSection('test4')->getValue());

		$_ENV['NOT_A_VAR'] = 'now has a value!';
		self::assertSame('this env should remain: now has a value!', $root->getSection('test3')->getValue());

		$_ENV['TEST_VAR'] = 123.2;
		self::assertSame('this is an env value: 123.2', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = true;
		self::assertSame('this is an env value: true', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = false;
		self::assertSame('this is an env value: false', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = null;
		self::assertSame('this is an env value: null', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = '${NOT_A_VAR}';
		self::assertSame('this is an env value: now has a value!', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = ['a' => 'b', 'c' => [1, 2, 3], 'nested' => '${NOT_A_VAR}'];
		self::assertSame('this is an env value: {"a":"b","c":[1,2,3],"nested":"now has a value!"}', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new stdClass();
		self::assertSame('this is an env value: stdClass', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new class {
		};
		self::assertSame('this is an env value: class@anonymous', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new class implements Stringable {
			public function __toString(): string
			{
				return 'this is a stringable object';
			}
		};
		self::assertSame('this is an env value: this is a stringable object', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new class implements IteratorAggregate {
			public function getIterator(): Traversable
			{
				yield 'this';
				yield 'is';
				yield 'an';
				yield 'iterable';
			}
		};
		self::assertSame('this is an env value: ["this","is","an","iterable"]', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = fopen('php://memory', 'rb');
		self::assertSame('this is an env value: resource (stream)', $root->getSection('test')->getValue());

		fclose($_ENV['TEST_VAR']);
		self::assertSame('this is an env value: resource (closed)', $root->getSection('test')->getValue());
	}
}
