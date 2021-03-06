<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Stringable;

class StringStream extends AbstractStream
{
	#[Pure]
	public static function from(
		string|Stringable $string,
		bool $seekable = true,
		bool $writeable = false,
		bool $readable = true,
	): Stream {
		return new self((string) $string, $readable, $seekable, $writeable);
	}

	private bool $detached = false;

	/**
	 * @var positive-int|0 $pointer
	 */
	private int $pointer = 0;

	#[Pure]
	public function __construct(
		private string $string,
		private readonly bool $readable = true,
		private readonly bool $seekable = true,
		private readonly bool $writeable = false,
	) {
	}

	#[Pure]
	public function __toString(): string
	{
		return $this->string;
	}

	public function detach(): void
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

	#[Pure]
	public function getSize(): ?int
	{
		return strlen($this->string);
	}

	#[Pure]
	public function tell(): int
	{
		return $this->pointer;
	}

	#[Pure]
	public function eof(): bool
	{
		return $this->pointer >= strlen($this->string);
	}

	#[Pure]
	public function isSeekable(): bool
	{
		return $this->seekable;
	}

	public function seek($offset, #[ExpectedValues([SEEK_SET, SEEK_CUR, SEEK_END])] $whence = SEEK_SET): void
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

	#[Pure]
	public function isWriteable(): bool
	{
		return $this->writeable;
	}

	public function write(string $string): int
	{
		if (!$this->isWriteable()) {
			throw new RuntimeException('Stream is not writable');
		}

		$this->string .= $string;

		return strlen($string);
	}

	#[Pure]
	public function isReadable(): bool
	{
		return $this->readable;
	}

	public function read(int $length): string
	{
		if (!$this->isReadable()) {
			throw new RuntimeException('Stream is not readable');
		}

		$string = substr($this->string, $this->pointer, $length);

		$this->pointer += strlen($string);

		return $string;
	}

	#[Pure]
	public function getContents(): string
	{
		return $this->string;
	}

	#[Pure]
	public function getMetadata(?string $key = null): array
	{
		return [];
	}
}
