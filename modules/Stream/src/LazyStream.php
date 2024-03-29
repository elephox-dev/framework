<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Closure;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\ExpectedValues;

class LazyStream implements Stream
{
	use StreamReader;

	protected ?Stream $stream = null;

	/**
	 * @param Closure(): Stream $closure
	 */
	public function __construct(
		private readonly Closure $closure,
	) {
	}

	public function getStream(): Stream
	{
		if ($this->stream === null) {
			$this->stream = ($this->closure)();
		}

		return $this->stream;
	}

	public function isSeekable(): bool
	{
		return $this->getStream()->isSeekable();
	}

	public function isWritable(): bool
	{
		return $this->getStream()->isWritable();
	}

	public function isReadable(): bool
	{
		return $this->getStream()->isReadable();
	}

	public function __toString(): string
	{
		return $this->getStream()->__toString();
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

	public function seek(int $offset, #[ExpectedValues([SEEK_SET, SEEK_CUR, SEEK_END])] int $whence = SEEK_SET): void
	{
		$this->getStream()->seek($offset, $whence);
	}

	public function rewind(): void
	{
		$this->getStream()->rewind();
	}

	public function write(string $string): int
	{
		return $this->getStream()->write($string);
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
