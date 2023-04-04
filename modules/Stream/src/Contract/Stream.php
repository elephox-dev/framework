<?php
declare(strict_types=1);

namespace Elephox\Stream\Contract;

use Psr\Http\Message\StreamInterface;
use Stringable;

interface Stream extends Stringable, StreamInterface
{
	/**
	 * @return resource|null
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

	public function seek(int $offset, int $whence = SEEK_SET): void;

	public function rewind(): void;

	/**
	 * @return int<0, max>
	 */
	public function write(string $string): int;

	public function getContents(): string;

	public function getMetadata(?string $key = null): mixed;

	/**
	 * @param int $length
	 */
	public function read(int $length): string;

	/**
	 * @return string A possibly multibyte character
	 */
	public function readChar(string $encoding = 'UTF-8'): string;

	public function readLine(string $eol = "\r\n", string $encoding = 'UTF-8'): string;

	/**
	 * @return iterable<int, string>
	 */
	public function readAllLines(string $eol = "\r\n", string $encoding = 'UTF-8'): iterable;

	/**
	 * @return int<0, 255>
	 */
	public function readByte(): int;

	/**
	 * @return iterable<int, int<0, 255>>
	 */
	public function readBytes(int $length, int $chunkSize = 1024): iterable;
}
