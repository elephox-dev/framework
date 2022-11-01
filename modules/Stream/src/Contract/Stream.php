<?php
declare(strict_types=1);

namespace Elephox\Stream\Contract;

use Stringable;

interface Stream extends Stringable
{
	/**
	 * @return closed-resource|resource|null
	 */
	public function detach();

	public function close(): void;

	/**
	 * @return int<0, max>|null
	 */
	public function getSize(): ?int;

	/**
	 * @return int<0, max>
	 */
	public function tell(): int;

	public function eof(): bool;

	public function isSeekable(): bool;

	/**
	 * @param int<0, max> $offset
	 * @param int<0, max> $whence
	 */
	public function seek(int $offset, int $whence = SEEK_SET): void;

	public function rewind(): void;

	public function isWriteable(): bool;

	/**
	 * @param string $string
	 *
	 * @return int<0, max>
	 */
	public function write(string $string): int;

	public function isReadable(): bool;

	public function read(int $length): string;

	public function getContents(): string;

	public function getMetadata(?string $key = null): mixed;

	public function readLine(string $eol = "\r\n"): string;

	public function readAllLines(string $eol = "\r\n"): iterable;

	public function readByte(): int;
}
