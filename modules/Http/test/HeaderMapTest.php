<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Http\HeaderMap
 * @covers \Philly\Http\HeaderName
 * @covers \Philly\Collection\ArrayList
 * @covers \Philly\Collection\GenericWeakMap
 * @covers \Philly\Http\InvalidHeaderNameException
 * @covers \Philly\Http\InvalidHeaderNameTypeException
 * @covers \Philly\Http\InvalidHeaderTypeException
 */
class HeaderMapTest extends TestCase
{
	public function testFromArray(): void
	{
		$headers = HeaderMap::fromArray([
			'Accept' => 'text/html',
			'Accept-Language' => 'en-US,en;q=0.8',
			'Accept-Encoding' => 'gzip, deflate, br',
			'Connection' => 'keep-alive',
			'Host' => 'example.com',
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
			'Cookie' => [
				'Name=test;Value=test;Secure;HttpOnly',
				'Name=test2;Value=test2;Secure;HttpOnly'
			]
		]);

		$this->assertEquals(
			[
				'Accept' => ['text/html'],
				'Accept-Language' => ['en-US,en;q=0.8'],
				'Accept-Encoding' => ['gzip, deflate, br'],
				'Connection' => ['keep-alive'],
				'Host' => ['example.com'],
				'User-Agent' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36'],
				'Cookie' => [
					'Name=test;Value=test;Secure;HttpOnly',
					'Name=test2;Value=test2;Secure;HttpOnly'
				],
			],
			$headers->asArray()
		);
	}

	public function testFromArrayInvalidNameTypeInt(): void
	{
		$this->expectException(InvalidHeaderNameTypeException::class);

		HeaderMap::fromArray([234 => 'test']);
	}

	public function testFromArrayInvalidValueType(): void
	{
		$this->expectException(InvalidHeaderTypeException::class);

		HeaderMap::fromArray(['Host' => 234.2]);
	}

	public function testFromArrayInvalidHeaderName(): void
	{
		$this->expectException(InvalidHeaderNameException::class);

		HeaderMap::fromArray(['test' => 'test']);
	}

	public function testFromArrayInvalidValueArrayType(): void
	{
		$this->expectException(InvalidHeaderTypeException::class);

        HeaderMap::fromArray(['Cookie' => [234.2, 2343]]);
	}

	public function testFromArrayProducesNormalizedArrayKeys(): void
	{
		$map = HeaderMap::fromArray(['Cookie' => [423 => 'test=value', 3433 => 'asdf=sdfg']]);

		$cookies = $map->get(HeaderName::Cookie);

		self::assertEquals("test=value", $cookies[0]);
		self::assertEquals("asdf=sdfg", $cookies[1]);
	}
}
