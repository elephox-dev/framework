<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use Elephox\Http\Contract\ParameterMap as ParameterMapContract;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Http\ParameterMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\ParameterSource
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class ParameterMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$post = [
			'foo' => 'bar',
			'baz' => 'qux',
			'ambiguous' => 'test post',
		];

		$get = [
			'faa' => 'bor',
			'biz' => 'qiz',
			'ambiguous' => 'test get',
		];

		$server = [
			'HTTPS' => 'on',
			'HTTP_HOST' => 'example.com',
			'REQUEST_URI' => '/foo/bar',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_TIME_FLOAT' => microtime(true),
			'invalid' => 'invalid',
		];

		$env = [
			'APP_DEBUG' => 'true',
			'APP_ENV' => 'dev',
		];

		$map = ParameterMap::fromGlobals($post, $get, $server, $env);

		static::assertInstanceOf(ParameterMapContract::class, $map);

		static::assertArrayHasKey('foo', $map);
		static::assertEquals('bar', $map['foo']);

		static::assertArrayHasKey('faa', $map);
		static::assertEquals('bor', $map['faa']);

		static::assertFalse($map->has('invalid'));

		$allGet = $map->allFrom(ParameterSource::Get)->toArray();
		static::assertEquals($get, $allGet);

		$ambiguous = $map->all('ambiguous')->toArray(static fn (ParameterSource $source) => $source->name);
		static::assertEquals(
			[
				ParameterSource::Post->name => 'test post',
				ParameterSource::Get->name => 'test get',
			],
			$ambiguous,
		);

		static::assertTrue($map->has('biz'));
		unset($map['biz']);
		static::assertFalse($map->has('biz'));
	}

	public function testOffsetSetException(): void
	{
		$map = new ParameterMap();

		$this->expectException(LogicException::class);
		$map->offsetSet('foo', 'bar');
	}

	public function testOffsetGetInvalidKeyType(): void
	{
		$map = new ParameterMap();

		$this->expectException(LogicException::class);
		$map->offsetGet(0);
	}

	public function testOffsetExistsInvalidKeyType(): void
	{
		$map = new ParameterMap();

		$this->expectException(LogicException::class);
		$map->offsetExists(0);
	}

	public function testOffsetUnsetInvalidKeyType(): void
	{
		$map = new ParameterMap();

		$this->expectException(LogicException::class);
		$map->offsetUnset(0);
	}

	public function testOffsetNotFoundException(): void
	{
		$map = new ParameterMap();

		static::assertFalse($map->has('foo'));

		$this->expectException(OffsetNotFoundException::class);
		$map->offsetGet('foo');
	}

	public function testAmbiguousKeyException(): void
	{
		$map = new ParameterMap();

		$map->put('foo', ParameterSource::Get, 'bar');
		$map->put('foo', ParameterSource::Post, 'baz');

		$this->expectException(RuntimeException::class);
		$map->get('foo');
	}
}
