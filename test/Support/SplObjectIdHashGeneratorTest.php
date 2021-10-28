<?php

namespace Philly\Base\Support;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Philly\Base\Support\Contract\HasHash;
use stdClass;

/**
 * @covers \Philly\Base\Support\SplObjectIdHashGenerator
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

        self::assertNotEquals($hashA, $hashB);

        $hasHashMock
            ->expects('getHash')
            ->withNoArgs()
            ->once()
            ->andReturn("testhash")
        ;

        $testHash = $generator->generateHash($hasHashMock);

        self::assertEquals("testhash", $testHash);
    }
}
