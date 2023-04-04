<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use Psr\Http\Message\StreamInterface;

class Psr7Stream implements Stream
{
	use StreamReader;

	public function __construct(
		private readonly StreamInterface $stream,
	) {
	}

	public function __toString(): string
	{
		return $this->stream->__toString();
	}

	public function detach()
	{
		return $this->stream->detach();
	}

	public function close(): void
	{
		$this->stream->close();
	}

	public function getSize(): ?int
	{
		/** @var null|int<0, max> */
		return $this->stream->getSize();
	}

	public function tell(): int
	{
		/** @var int<0, max> */
		return $this->stream->tell();
	}

	public function seek(int $offset, int $whence = SEEK_SET): void
	{
		$this->stream->seek($offset, $whence);
	}

	public function rewind(): void
	{
		$this->stream->rewind();
	}

	public function write(string $string): int
	{
		/** @var int<0, max> */
		return $this->stream->write($string);
	}

	public function getContents(): string
	{
		return $this->stream->getContents();
	}

	public function getMetadata(?string $key = null): mixed
	{
		return $this->stream->getMetadata($key);
	}

	public function isSeekable(): bool
	{
		return $this->stream->isSeekable();
	}

	public function isWritable(): bool
	{
		return $this->stream->isWritable();
	}

	public function isReadable(): bool
	{
		return $this->stream->isReadable();
	}

	public function eof(): bool
	{
		return $this->stream->eof();
	}

	public function read(int $length): string
	{
		return $this->stream->read($length);
	}
}
