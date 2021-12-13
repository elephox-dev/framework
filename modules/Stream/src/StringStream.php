<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class StringStream implements Stream
{
	private bool $detached = false;

	/** @var positive-int|0 $pointer */
	private int $pointer = 0;

	#[Pure] public function __construct(
		private string $string,
		private bool $seekable = true,
		private bool $writable = true,
		private bool $readable = true
	)
	{}

	#[Pure] public function __toString(): string
	{
		return $this->string;
	}

	public function detach()
	{
		$this->detached = true;
	}

	public function close(): void
	{
		if ($this->detached) {
			throw new RuntimeException('Stream is detached');
		}

		$this->detach();
	}

	#[Pure] public function getSize(): ?int
	{
		return strlen($this->string);
	}

	#[Pure] public function tell(): int
	{
		return $this->pointer;
	}

	#[Pure] public function eof(): bool
	{
		return $this->pointer >= strlen($this->string);
	}

	#[Pure] public function isSeekable(): bool
	{
		return $this->seekable;
	}

	public function seek($offset, $whence = SEEK_SET): void
	{
		if (!$this->isSeekable()) {
			throw new RuntimeException('Stream is not seekable');
		}

		if ($whence === SEEK_SET) {
			$this->pointer = $offset;
		} elseif ($whence === SEEK_CUR) {
			$this->pointer += $offset;
		} elseif ($whence === SEEK_END) {
			$this->pointer = strlen($this->string) + $offset;
		} else {
			throw new InvalidArgumentException('Invalid whence: ' . $whence);
		}
	}

	public function rewind(): void
	{
		$this->seek(0);
	}

	#[Pure] public function isWritable(): bool
	{
		return $this->writable;
	}

	public function write(string $string): int
	{
		if (!$this->isWritable()) {
			throw new RuntimeException('Stream is not writable');
		}

		$this->string .= $string;

		return strlen($string);
	}

	#[Pure] public function isReadable(): bool
	{
		return $this->readable;
	}

	public function read(int $length): string
	{
		if (!$this->isReadable()) {
			throw new RuntimeException('Stream is not readable');
		}

		/** @var false|string $string */
		$string = substr($this->string, $this->pointer, $length);
		if ($string === false) {
			return "";
		}

		$this->pointer += strlen($string);

		return $string;
	}

	#[Pure] public function getContents(): string
	{
		return $this->string;
	}

	#[Pure] public function getMetadata(?string $key = null): array
	{
		return [];
	}
}
