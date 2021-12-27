<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Closure;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;

class LazyStream implements Stream
{
	protected ?Stream $stream = null;

	/**
	 * @param Closure(): Stream $closure
	 * @param bool $readable
	 * @param bool $writeable
	 * @param bool $seekable
	 */
	public function __construct(
		protected Closure $closure,
		protected bool    $readable = true,
		protected bool    $writeable = false,
		protected bool    $seekable = true
	) {
	}

	public function getStream(): Stream
	{
		if ($this->stream === null) {
			$this->stream = ($this->closure)();
		}

		return $this->stream;
	}

	#[Pure] public function isSeekable(): bool
	{
		return $this->stream?->isSeekable() ?? $this->seekable;
	}

	#[Pure] public function isWriteable(): bool
	{
		return $this->stream?->isWriteable() ?? $this->writeable;
	}

	#[Pure] public function isReadable(): bool
	{
		return $this->stream?->isReadable() ?? $this->readable;
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

	public function seek($offset, $whence = SEEK_SET): void
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
