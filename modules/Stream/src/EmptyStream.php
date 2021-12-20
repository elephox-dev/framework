<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

final class EmptyStream implements Stream
{
	#[Pure] public function __toString(): string
	{
		return '';
	}

	#[Pure] public function detach(): mixed
	{
		return null;
	}

	#[Pure] public function close(): void
	{
	}

	#[Pure] public function getSize(): ?int
	{
		return 0;
	}

	#[Pure] public function tell(): int
	{
		return 0;
	}

	#[Pure] public function eof(): bool
	{
		return true;
	}

	#[Pure] public function isSeekable(): bool
	{
		return false;
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		throw new RuntimeException('Empty stream is not seekable.');
	}

	public function rewind(): void
	{
		throw new RuntimeException('Empty stream is not seekable.');
	}

	#[Pure] public function isWritable(): bool
	{
		return false;
	}

	public function write(string $string): int
	{
		throw new RuntimeException('Empty stream is not writable.');
	}

	#[Pure] public function isReadable(): bool
	{
		return false;
	}

	public function read(int $length): string
	{
		throw new RuntimeException('Empty stream is not readable.');
	}

	#[Pure] public function getContents(): string
	{
		return '';
	}

	#[Pure] public function getMetadata(?string $key = null): mixed
	{
		return null;
	}
}
