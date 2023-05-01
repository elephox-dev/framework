<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\RequestMethod
 *
 * @internal
 */
final class RequestMethodTest extends TestCase
{
	public function testCanHaveBody(): void
	{
		foreach (RequestMethod::cases() as $method) {
			if ($method === RequestMethod::GET || $method === RequestMethod::HEAD || $method === RequestMethod::OPTIONS) {
				self::assertFalse($method->canHaveBody());
			} else {
				self::assertTrue($method->canHaveBody());
			}
		}
	}
}
