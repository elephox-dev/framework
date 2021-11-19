<?php
declare(strict_types=1);

namespace Elephox\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\InvalidHeaderNameTypeException
 * @covers \Elephox\Collection\KeyValuePair
 * @covers \Elephox\Http\InvalidHeaderNameException
 * @covers \Elephox\Http\CustomHeaderName
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Http\InvalidHeaderTypeException
 */
class ResponseHeaderMapTest extends TestCase
{
	public function testVariousHeaderNames(): void
	{
		$map = ResponseHeaderMap::fromArray([
			'Host' => 'localhost',
			'x-custom' => 'custom',
			'Set-Cookie' => [
				'name' => 'value',
				'name2' => 'value2',
			],
		]);

		self::assertEquals('localhost', $map->get(HeaderName::Host));
		self::assertEquals('custom', $map->get('x-custom')[0]);
		self::assertEquals([
			'value',
			'value2',
		], $map->get(HeaderName::SetCookie));
	}

	public function testInvalidHeaderRow(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResponseHeaderMap::fromString("invalidheader");
	}

	public function testInvalidHeaderName(): void
	{
		$this->expectException(InvalidHeaderNameException::class);

		ResponseHeaderMap::fromString(": no name");
	}

	public function testInvalidHeaderType(): void
	{
		$this->expectException(InvalidHeaderNameTypeException::class);

		ResponseHeaderMap::fromArray([
			123 => "test"
		]);
	}

	public function testInvalidHeaderValueType(): void
	{
		$this->expectException(InvalidHeaderTypeException::class);

		ResponseHeaderMap::fromArray([
			'Host' => 123
		]);
	}
}