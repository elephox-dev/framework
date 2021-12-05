<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Stringable;

interface TypedStreamInterface extends StreamInterface, Stringable
{
	/**
	 * @psalm-suppress ImplementedReturnTypeMismatch
	 * @return closed-resource|resource|null
	 */
	public function detach();

	public function close(): void;

	/**
	 * @return positive-int|null|0
	 */
	public function getSize(): ?int;

	/**
	 * @return positive-int|0
	 */
	public function tell(): int;

	public function eof(): bool;

	#[Pure] public function isSeekable(): bool;

	/**
	 * @param positive-int|0 $offset
	 * @param positive-int|0 $whence
	 * @return void
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function seek($offset, $whence = SEEK_SET): void;

	public function rewind(): void;

	#[Pure] public function isWritable(): bool;

	/**
	 * @param string $string
	 *
	 * @return positive-int|0
	 */
	public function write($string): int;

	#[Pure] public function isReadable(): bool;

	public function read($length): string;

	public function getContents(): string;

	public function getMetadata($key = null): mixed;
}
