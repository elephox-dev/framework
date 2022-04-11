<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Memory\MemoryConfigurationSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Stringable;

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
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class ConfigurationRootTest extends TestCase
{
	public function testGetChildren(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		static::assertEquals(['this is', 'nested'], $root->getChildKeys()->toArray());
		$children = $root->getChildren();

		$firstChild = $children->first();
		static::assertEquals('this is', $firstChild->getKey());

		$nested = $children->skip(1)->first();
		static::assertEquals('nested', $nested->getKey());
		static::assertEquals(['nested:a', 'nested:c'], $nested->getChildKeys()->toArray());

		$nestedChildren = $nested->getChildren();
		$firstNestedChild = $nestedChildren->first();
		static::assertEquals('a', $firstNestedChild->getKey());
		static::assertEquals('nested:a', $firstNestedChild->getPath());
		static::assertEquals('b', $firstNestedChild->getValue());

		$secondNestedChild = $nestedChildren->skip(1)->first();
		static::assertEquals('c', $secondNestedChild->getKey());
		static::assertEquals('nested:c', $secondNestedChild->getPath());
		static::assertEquals(['nested:c:foo'], $secondNestedChild->getChildKeys()->toArray());

		static::assertEquals('bar', $secondNestedChild['foo']);
		static::assertEquals('bar', $root->offsetGet('nested:c:foo'));
	}

	public function testArrayAccess(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		static::assertEquals('bar', $root['nested:c:foo']);
		$root['nested:c:foo'] = 'baz';
		static::assertEquals('baz', $root['nested:c:foo']);

		static::assertTrue($root->offsetExists('nested:c:foo'));
		unset($root['nested:c:foo']);
		static::assertFalse($root->offsetExists('nested:c:foo'));
		static::assertNull($root['nested:c:foo']);

		$section = $root->getSection('nested');
		$section['c:foo'] = 'baz';
		static::assertTrue($section->offsetExists('c:foo'));
		static::assertEquals('baz', $section['c:foo']);
		$section['c:foo'] = 'bar';
		static::assertEquals('bar', $section['c:foo']);
		unset($section['c:foo']);
		static::assertFalse($section->offsetExists('c:foo'));
		static::assertNull($section['c:foo']);
	}

	public function testValue(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		static::assertEquals('bar', $root->getSection('nested:c:foo')->getValue());
		$root->getSection('nested:c:foo')->setValue('baz');
		static::assertEquals('baz', $root->getSection('nested:c:foo')->getValue());
		$root->getSection('nested:c:foo')->deleteValue();
		static::assertFalse($root->getSection('nested:c')->hasSection('foo'));
	}

	public function testHasSection(): void
	{
		$configBuilder = new ConfigurationBuilder();
		$configBuilder->add(new MemoryConfigurationSource(['this is' => 'a test', 'nested' => ['a' => 'b', 'c' => ['foo' => 'bar']]]));
		$root = $configBuilder->build();

		static::assertTrue($root->hasSection('nested:c'));
		static::assertTrue($root->hasSection('nested:c:foo'));
		static::assertFalse($root->hasSection('nested:c:foo:baz'));
		static::assertFalse($root->hasSection('nested:d'));
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
		$configBuilder->add(new MemoryConfigurationSource(['test' => 'this is an env value: ${TEST_VAR}', 'test2' => 'this env should not be replaced: $${TEST_VAR}', 'test3' => 'this env should remain: ${NOT_A_VAR}']));
		$root = $configBuilder->build();

		$_ENV['TEST_VAR'] = 'secret!';
		unset($_ENV['NOT_A_VAR']);

		static::assertEquals('this is an env value: secret!', $root->getSection('test')->getValue());
		static::assertEquals('this env should not be replaced: ${TEST_VAR}', $root->getSection('test2')->getValue());
		static::assertEquals('this env should remain: ${NOT_A_VAR}', $root->getSection('test3')->getValue());

		$_ENV['NOT_A_VAR'] = 'now has a value!';
		static::assertEquals('this env should remain: now has a value!', $root->getSection('test3')->getValue());

		$_ENV['TEST_VAR'] = 123.2;
		static::assertEquals('this is an env value: 123.2', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = true;
		static::assertEquals('this is an env value: true', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = false;
		static::assertEquals('this is an env value: false', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = null;
		static::assertEquals('this is an env value: null', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = '${NOT_A_VAR}';
		static::assertEquals('this is an env value: now has a value!', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = ['a' => 'b'];
		static::assertEquals('this is an env value: array', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new stdClass();
		static::assertEquals('this is an env value: stdClass', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new class {
		};
		static::assertEquals('this is an env value: class@anonymous', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = new class implements Stringable {
			public function __toString(): string
			{
				return 'this is a stringable object';
			}
		};
		static::assertEquals('this is an env value: this is a stringable object', $root->getSection('test')->getValue());

		$_ENV['TEST_VAR'] = fopen('php://memory', 'rb');
		static::assertEquals('this is an env value: resource (stream)', $root->getSection('test')->getValue());

		fclose($_ENV['TEST_VAR']);
		static::assertEquals('this is an env value: resource (closed)', $root->getSection('test')->getValue());
	}
}
