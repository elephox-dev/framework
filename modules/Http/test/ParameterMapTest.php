<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use Elephox\Http\Contract\ParameterMap as ParameterMapContract;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ParameterMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\ParameterSource
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\KeyedEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
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

		self::assertInstanceOf(ParameterMapContract::class, $map);

		self::assertArrayHasKey('foo', $map);
		self::assertEquals('bar', $map['foo']);

		self::assertArrayHasKey('faa', $map);
		self::assertEquals('bor', $map['faa']);

		self::assertFalse($map->has('invalid'));

		$allGet = $map->allFrom(ParameterSource::Get)->toArray();
		self::assertEquals($get, $allGet);

		$ambiguous = $map->all('ambiguous')->toArray(fn (ParameterSource $source) => $source->name);
		self::assertEquals(
			[
				ParameterSource::Post->name => 'test post',
				ParameterSource::Get->name => 'test get'
			],
			$ambiguous
		);

		self::assertTrue($map->has('biz'));
		unset($map['biz']);
		self::assertFalse($map->has('biz'));
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

		self::assertFalse($map->has('foo'));

		$this->expectException(OffsetNotFoundException::class);
		$map->offsetGet('foo');
	}

	public function testAmbiguousKeyException(): void
	{
		$map = new ParameterMap();

		$map->put('foo', ParameterSource::Get, 'bar');
		$map->put('foo', ParameterSource::Post, 'baz');

		$this->expectException(LogicException::class);
		$map->get('foo');
	}
}
