<?php
declare(strict_types=1);

namespace Elephox\Support;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Elephox\Support\Contract\HasHash;
use stdClass;

/**
 * @covers \Elephox\Support\SplObjectIdHashGenerator
 *
 * @internal
 */
class SplObjectIdHashGeneratorTest extends MockeryTestCase
{
	public function testGenerateHash(): void
	{
		$hasHashMock = Mockery::mock(HasHash::class);

		$generator = new SplObjectIdHashGenerator();

		$a = new stdClass();
		$b = new stdClass();

		$hashA = $generator->generateHash($a);
		$hashB = $generator->generateHash($b);

		static::assertNotSame($hashA, $hashB);

		$hasHashMock
			->expects('getHash')
			->withNoArgs()
			->once()
			->andReturn('testhash')
		;

		$testHash = $generator->generateHash($hasHashMock);

		static::assertSame('testhash', $testHash);
	}
}
