<?php
declare(strict_types=1);

namespace Elephox\Http;

use Closure;
use Elephox\Http\Contract\Stream;
use JetBrains\PhpStorm\Pure;

class LazyResourceStream implements Stream
{
	protected ?ResourceStream $resourceStream = null;

	/**
	 * @param Closure(): resource $closure
	 * @param bool $readable
	 * @param bool $writable
	 * @param bool $seekable
	 */
	public function __construct(
		protected Closure $closure,
		protected bool    $readable = true,
		protected bool    $writable = true,
		protected bool    $seekable = true
	)
	{
	}

	protected function getResourceStream(): ResourceStream
	{
		if ($this->resourceStream === null) {
			$resource = ($this->closure)();
			$this->resourceStream = new ResourceStream(
				$resource,
				$this->readable,
				$this->writable,
				$this->seekable,
			);
		}

		return $this->resourceStream;
	}

	#[Pure] public function isSeekable(): bool
	{
		return $this->resourceStream?->isSeekable() ?? $this->seekable;
	}

	#[Pure] public function isWritable(): bool
	{
		return $this->resourceStream?->isWritable() ?? $this->writable;
	}

	#[Pure] public function isReadable(): bool
	{
		return $this->resourceStream?->isReadable() ?? $this->readable;
	}

	public function __toString(): string
	{
		return $this->getResourceStream()->__toString();
	}

	public function detach()
	{
		return $this->getResourceStream()->detach();
	}

	public function close(): void
	{
		$this->getResourceStream()->close();
	}

	public function getSize(): ?int
	{
		return $this->getResourceStream()->getSize();
	}

	public function tell(): int
	{
		return $this->getResourceStream()->tell();
	}

	public function eof(): bool
	{
		return $this->getResourceStream()->eof();
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		$this->getResourceStream()->seek($offset, $whence);
	}

	public function rewind(): void
	{
		$this->getResourceStream()->rewind();
	}

	public function write($string): int
	{
		return $this->getResourceStream()->write($string);
	}

	public function read($length): string
	{
		return $this->getResourceStream()->read($length);
	}

	public function getContents(): string
	{
		return $this->getResourceStream()->getContents();
	}

	public function getMetadata($key = null): mixed
	{
		return $this->getResourceStream()->getMetadata($key);
	}
}
