<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class ResourceStream extends AbstractStream
{
	/**
	 * @param closed-resource|resource|null $resource
	 * @param bool $readable
	 * @param bool $writeable
	 * @param bool $seekable
	 * @param null|int<0, max> $size
	 */
	public function __construct(
		private mixed $resource,
		private readonly bool $readable = true,
		private readonly bool $writeable = false,
		private readonly bool $seekable = true,
		private ?int $size = null,
	) {
		if (!is_resource($this->resource)) {
			throw new InvalidArgumentException('ResourceStream expects a resource');
		}
	}

	public function __toString(): string
	{
		if (!is_resource($this->resource)) {
			return '';
		}

		if ($this->isSeekable()) {
			$this->rewind();
		}

		return $this->getContents();
	}

	/**
	 * @return resource|closed-resource|null
	 */
	public function getResource()
	{
		return $this->resource;
	}

	public function detach()
	{
		if (!isset($this->resource)) {
			return null;
		}

		$resource = $this->resource;

		$this->resource = null;

		return $resource;
	}

	public function close(): void
	{
		if (!is_resource($this->resource)) {
			return;
		}

		fclose($this->resource);

		$this->detach();
	}

	public function getSize(): ?int
	{
		if ($this->size !== null) {
			return $this->size;
		}

		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		$stats = fstat($this->resource);
		if (is_array($stats)) {
			/** @var int<0, max> */
			$this->size = $stats['size'];
		}

		return $this->size;
	}

	public function tell(): int
	{
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		/** @var int<0, max>|false $position */
		$position = ftell($this->resource);
		if ($position === false) {
			throw new RuntimeException('Unable to determine the current position');
		}

		return $position;
	}

	public function eof(): bool
	{
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		return feof($this->resource);
	}

	#[Pure]
	public function isSeekable(): bool
	{
		return $this->seekable;
	}

	public function seek(int $offset, int $whence = SEEK_SET): void
	{
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		if (!$this->seekable) {
			throw new RuntimeException('Resource is not seekable');
		}

		if (fseek($this->resource, $offset, $whence) === -1) {
			throw new RuntimeException('Unable to seek to resource position ' . $offset . ' with whence ' . $whence);
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
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		if (!$this->writeable) {
			throw new RuntimeException('Cannot write to a non-writable resource');
		}

		$this->size = null;

		/** @var false|int<0, max> $written */
		$written = fwrite($this->resource, $string);
		if ($written === false) {
			throw new RuntimeException('Unable to write to resource');
		}

		return $written;
	}

	#[Pure]
	public function isReadable(): bool
	{
		return $this->readable;
	}

	public function read(int $length): string
	{
		if ($length < 0) {
			throw new InvalidArgumentException('Length parameter cannot be negative');
		}

		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		if (!$this->readable) {
			throw new RuntimeException('Cannot read from a non-readable resource');
		}

		if ($length === 0) {
			return '';
		}

		$buffer = fread($this->resource, $length);
		if ($buffer === false) {
			throw new RuntimeException('Unable to read from resource');
		}

		return $buffer;
	}

	public function getContents(): string
	{
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		$contents = stream_get_contents($this->resource);
		if ($contents === false) {
			throw new RuntimeException('Unable to read resource contents');
		}

		return $contents;
	}

	public function getMetadata(?string $key = null): mixed
	{
		if (!is_resource($this->resource)) {
			return $key ? null : [];
		}

		$meta = stream_get_meta_data($this->resource);

		if ($key === null) {
			return $meta;
		}

		return $meta[$key] ?? null;
	}
}
