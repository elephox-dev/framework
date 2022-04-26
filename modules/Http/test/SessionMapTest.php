<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\SessionMap
 * @covers \Elephox\Platform\Session
 *
 * @internal
 */
class SessionMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$map = SessionMap::fromGlobals();

		static::assertEmpty($map);
	}
}
