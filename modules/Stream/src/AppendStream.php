<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use function Safe\swoole_async_write;

class AppendStream implements Stream
{
	public function __construct(
		private Stream $stream,
		private Stream $appendedStream
	) {
	}

	protected function getCurrentStream(): Stream
	{
		return $this->stream->eof() ? $this->appendedStream : $this->stream;
	}

	public function __toString(): string
	{
		return $this->stream . $this->appendedStream;
	}

	public function detach()
	{
		$this->appendedStream->detach();

		return $this->stream->detach();
	}

	public function close(): void
	{
		$this->appendedStream->close();

		$this->stream->close();
	}

	public function getSize(): ?int
	{
		$streamSize = $this->stream->getSize();
		$appendStreamSize = $this->appendedStream->getSize();

		if ($streamSize === null || $appendStreamSize === null) {
			return null;
		}

		return $streamSize + $appendStreamSize;
	}

	public function tell(): int
	{
		if ($this->stream->eof()) {
			return ($this->stream->getSize() ?? 0) + $this->appendedStream->tell();
		}

		return $this->stream->tell();
	}

	public function eof(): bool
	{
		if ($this->stream->eof()) {
			return $this->appendedStream->eof();
		}

		return false;
	}

	public function isSeekable(): bool
	{
		return $this->stream->isSeekable() && $this->appendedStream->isSeekable() && $this->getSize() !== null;
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		$streamSize = $this->stream->getSize();
		$appendStreamSize = $this->appendedStream->getSize();

		if ($streamSize === null || $appendStreamSize === null) {
			throw new RuntimeException('AppendStream is only seekable if the underlying streams sizes are known');
		}

		$totalSize = $streamSize + $appendStreamSize;
		$tell = $this->tell();

		switch ($whence) {
			case SEEK_SET:
				if ($offset > $streamSize) {
					$offset -= $streamSize;
					/** @var positive-int|0 $offset */

					$this->appendedStream->seek($offset, SEEK_SET);
				} else if ($offset >= 0) {
					$this->stream->seek($offset, SEEK_SET);
				} else {
					throw new InvalidArgumentException('Cannot seek to negative offset');
				}
				break;
			case SEEK_CUR:
				$newOffset = $offset + $tell;
				if ($newOffset > $streamSize) {
					$newOffset -= $streamSize;
					/** @var positive-int|0 $newOffset */

					$this->appendedStream->seek($newOffset, SEEK_SET);
				} else if ($newOffset >= 0) {
					$this->stream->seek($newOffset, SEEK_SET);
				} else {
					throw new InvalidArgumentException('Cannot seek to negative offset');
				}
				break;
			case SEEK_END:
				$newOffset = $totalSize - $offset;
				if ($newOffset > $streamSize) {
					$newOffset -= $streamSize;
					/** @var positive-int|0 $newOffset */

					$this->appendedStream->seek($newOffset, SEEK_SET);
				} else if ($newOffset >= 0) {
					$this->stream->seek($newOffset, SEEK_SET);
				} else {
					throw new InvalidArgumentException('Cannot seek to negative offset');
				}
				break;
			default:
				throw new InvalidArgumentException('Invalid whence');
		}
	}

	public function rewind(): void
	{
		$this->stream->rewind();
		$this->appendedStream->rewind();
	}

	public function isWriteable(): bool
	{
		return $this->stream->isWriteable() && $this->appendedStream->isWriteable();
	}

	public function write(string $string): int
	{
		$length = mb_strlen($string);
		$written = $this->stream->write($string);

		if ($written < $length) {
			$written += $this->appendedStream->write(mb_substr($string, $written));
		}

		return $written;
	}

	public function isReadable(): bool
	{
		return $this->stream->isReadable() && $this->appendedStream->isReadable();
	}

	public function read(int $length): string
	{
		$streamSize = $this->stream->getSize();
		$appendedStreamSize = $this->appendedStream->getSize();

		if ($streamSize === null || $appendedStreamSize === null) {
			throw new RuntimeException('AppendStream is only readable if the underlying streams sizes are known');
		}

		$tell = $this->tell();
		if ($tell >= $streamSize) {
			return $this->appendedStream->read($length);
		}

		if ($tell + $length > $streamSize) {
			$streamLength = $streamSize - $tell;
			$appendedStreamLength = $length - $streamLength;

			return $this->stream->read($streamLength) . $this->appendedStream->read($appendedStreamLength);
		}

		return $this->stream->read($length);
	}

	public function getContents(): string
	{
		return $this->stream->getContents() . $this->appendedStream->getContents();
	}

	public function getMetadata(?string $key = null): array
	{
		return [$this->stream->getMetadata($key), $this->appendedStream->getMetadata($key)];
	}
}
