<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Stream\LazyStream
 * @covers \Elephox\Stream\StringStream
 *
 * @internal
 */
class LazyStreamTest extends MockeryTestCase
{
	public function testGetStream(): void
	{
		$streamMock = M::mock(Stream::class);

		$streamMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$stream = new LazyStream(static fn () => $streamMock);

		static::assertSame($streamMock, $stream->getStream());
		static::assertTrue($stream->isReadable());
	}

	public function methodNameProvider(): array
	{
		return [
			['__toString', [], 'test'],
			['detach', [], null],
			['close', [], null],
			['getSize', [], 0],
			['getSize', [], null],
			['tell', [], 0],
			['eof', [], false],
			['seek', [1, SEEK_SET], null],
			['seek', [1, SEEK_CUR], null],
			['seek', [1, SEEK_END], null],
			['rewind', [], null],
			['write', ['test'], 4],
			['read', [1], ''],
			['getContents', [], 'test'],
			['getMetadata', [null], null],
			['getMetadata', [null], ['test' => true]],
			['getMetadata', ['test'], true],
		];
	}

	/**
	 * @dataProvider methodNameProvider
	 */
	public function testMockMethod(string $method, array $args, mixed $result): void
	{
		$streamMock = M::mock(Stream::class);

		$streamMock
			->expects($method)
			->withArgs($args)
			->andReturn($result)
		;

		$stream = new LazyStream(static fn () => $streamMock);

		$actual = $stream->{$method}(...$args);

		static::assertEquals($result, $actual);
	}
}
