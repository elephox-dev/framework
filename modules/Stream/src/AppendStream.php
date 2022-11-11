<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use RuntimeException;

class AppendStream implements Stream
{
	use StreamReader;

	public function __construct(
		private readonly Stream $stream,
		private readonly Stream $appendedStream,
	) {
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

	public function seek($offset, #[ExpectedValues([SEEK_SET, SEEK_CUR, SEEK_END])] $whence = SEEK_SET): void
	{
		assert(is_int($offset));
		assert(is_int($whence));

		$streamSize = $this->stream->getSize();
		$appendStreamSize = $this->appendedStream->getSize();

		if ($streamSize === null || $appendStreamSize === null) {
			throw new RuntimeException('AppendStream is only seekable if the underlying streams sizes are known');
		}

		switch ($whence) {
			case SEEK_SET:
				break;
			case SEEK_CUR:
				$offset += $this->tell();

				break;
			case SEEK_END:
				$offset = ($streamSize + $appendStreamSize) - $offset;

				break;
			default:
				throw new InvalidArgumentException('Invalid whence');
		}

		if ($offset > $streamSize) {
			$offset -= $streamSize;
			$this->appendedStream->seek($offset);
		} elseif ($offset >= 0) {
			$this->stream->seek($offset);
		} else {
			throw new InvalidArgumentException('Cannot seek to negative offset');
		}
	}

	public function rewind(): void
	{
		$this->stream->rewind();
		$this->appendedStream->rewind();
	}

	public function isWritable(): bool
	{
		return $this->stream->isWritable() && $this->appendedStream->isWritable();
	}

	public function write($string): int
	{
		assert(is_string($string));

		$length = mb_strlen($string, 'UTF-8');
		$written = $this->stream->write($string);

		if ($written < $length) {
			$written += $this->appendedStream->write(mb_substr($string, $written, encoding: 'UTF-8'));
		}

		return $written;
	}

	public function isReadable(): bool
	{
		return $this->stream->isReadable() && $this->appendedStream->isReadable();
	}

	public function read($length): string
	{
		assert(is_int($length));

		$streamSize = $this->stream->getSize();

		if ($streamSize === null) {
			throw new RuntimeException('AppendStream is only readable if the underlying stream size is known');
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

	public function getMetadata($key = null): array
	{
		assert(is_string($key) || $key === null);

		return [$this->stream->getMetadata($key), $this->appendedStream->getMetadata($key)];
	}
}
