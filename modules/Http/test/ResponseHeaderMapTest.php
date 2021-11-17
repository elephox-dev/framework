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
 */
class ResponseHeaderMapTest extends TestCase
{
	public function testInvalidHeaderRow(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResponseHeaderMap::fromString("invalidheader");
	}

	public function testInvalidHeaderType(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResponseHeaderMap::fromArray([
			123 => "test"
		]);
	}
}
