<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\TypedStreamInterface;
use RuntimeException;

final class EmptyStream implements TypedStreamInterface
{
	public function __toString(): string
	{
		return '';
	}

	public function detach(): mixed
	{
		return null;
	}

	public function close(): void
	{
	}

	public function getSize(): ?int
	{
		return 0;
	}

	public function tell(): int
	{
		return 0;
	}

	public function eof(): bool
	{
		return true;
	}

	public function isSeekable(): bool
	{
		return false;
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		throw new RuntimeException('Empty stream is not seekable.');
	}

	public function rewind(): void
	{
	}

	public function isWritable(): bool
	{
		return false;
	}

	public function write($string): int
	{
		throw new RuntimeException('Empty stream is not writable.');
	}

	public function isReadable(): bool
	{
		return false;
	}

	public function read($length): string
	{
		throw new RuntimeException('Empty stream is not readable.');
	}

	public function getContents(): string
	{
		return '';
	}

	public function getMetadata($key = null): mixed
	{
		return null;
	}
}
