<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;

class AppendStream implements Stream
{
	public function __construct(
		private Stream $stream,
		private Stream $appendedStream
	) {
	}

	protected function getStream(): Stream
	{
		return $this->stream->eof() ? $this->appendedStream : $this->stream;
	}

	public function __toString(): string
	{
		return (string)$this->getStream();
	}

	public function detach()
	{
		return $this->getStream()->detach();
	}

	public function close(): void
	{
		$this->getStream()->close();
	}

	public function getSize(): ?int
	{
		return $this->getStream()->getSize();
	}

	public function tell(): int
	{
		return $this->getStream()->tell();
	}

	public function eof(): bool
	{
		return $this->getStream()->eof();
	}

	#[Pure] public function isSeekable(): bool
	{
		return $this->getStream()->isSeekable();
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		$this->getStream()->seek($offset, $whence);
	}

	public function rewind(): void
	{
		$this->getStream()->rewind();
	}

	#[Pure] public function isWritable(): bool
	{
		return $this->getStream()->isWritable();
	}

	public function write(string $string): int
	{
		return $this->getStream()->write($string);
	}

	#[Pure] public function isReadable(): bool
	{
		return $this->getStream()->isReadable();
	}

	public function read(int $length): string
	{
		return $this->getStream()->read($length);
	}

	public function getContents(): string
	{
		return $this->getStream()->getContents();
	}

	public function getMetadata(?string $key = null): mixed
	{
		return $this->getStream()->getMetadata($key);
	}
}
