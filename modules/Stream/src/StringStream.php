<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Stringable;

class StringStream implements Stream
{
	use StreamReader;

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
	 * @var int<0, max> $pointer
	 */
	private int $pointer = 0;

	#[Pure]
	public function __construct(
		private string $string,
		private readonly bool $readable = true,
		private readonly bool $seekable = true,
		private readonly bool $writable = false,
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
	public function getSize(): int
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
		assert(is_int($offset));
		assert(is_int($whence));

		if (!$this->isSeekable()) {
			throw new RuntimeException('Stream is not seekable');
		}

		$size = $this->getSize();
		if ($whence === SEEK_SET) {
			if ($offset < 0) {
				throw new InvalidArgumentException('$offset must be greater than or equal to 0 when using SEEK_SET');
			}

			$this->pointer = $offset;
		} elseif ($whence === SEEK_CUR) {
			if ($this->pointer + $offset < 0) {
				throw new InvalidArgumentException('$offset would move the pointer below 0 when using SEEK_CUR');
			}

			/** @var int<0, max> */
			$this->pointer += $offset;
		} elseif ($whence === SEEK_END) {
			if ($size + $offset < 0) {
				throw new InvalidArgumentException('Offset would move pointer below 0 when using SEEK_END');
			}

			/** @var int<0, max> */
			$this->pointer = $size + $offset;
		} else {
			throw new InvalidArgumentException('Invalid whence: ' . $whence);
		}
	}

	public function rewind(): void
	{
		$this->seek(0);
	}

	#[Pure]
	public function isWritable(): bool
	{
		return $this->writable;
	}

	public function write($string): int
	{
		assert(is_string($string));

		if (!$this->isWritable()) {
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

	public function read($length): string
	{
		assert(is_int($length));

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
	public function getMetadata($key = null): array
	{
		assert(is_string($key) || $key === null);

		return [];
	}
}
