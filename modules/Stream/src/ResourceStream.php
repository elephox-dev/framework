<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\OOR\Str;
use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class ResourceStream implements Stream
{
	use StreamReader;

	public static function open(
		string $url,
		bool $readable = true,
		bool $writable = false,
		bool $create = false,
		bool $append = false,
		bool $truncate = false,
	): self {
		$flags = match (true) {
			$readable && $writable && $create && $append && !$truncate => 'ab+',
			!$readable && $writable && $create && $append && !$truncate => 'ab',
			$readable && $writable && $create && !$append && $truncate => 'wb+',
			!$readable && $writable && $create && !$append && $truncate => 'wb',
			$readable && $writable && $create && !$append && !$truncate => 'cb+',
			!$readable && $writable && $create && !$append && !$truncate => 'cb',
			$readable && $writable && !$create && !$append && !$truncate => 'rb+',
			$readable && !$writable && !$create && !$append && !$truncate => 'rb',
			default => throw new InvalidArgumentException('Invalid combination of flags: readable=' . ($readable ?: '0') . ', writeable=' . ($writable ?: '0') . ', create=' . ($create ?: '0') . ', append=' . ($append ?: '0') . ', truncate=' . ($truncate ?: '0')),
		};

		$exception = null;

		// handle any warnings emitted by fopen()
		set_error_handler(static function (int $errorCode, string $errorMessage, string $filename = '<unknown>', int $line = 0) use (&$exception): bool {
			$exception = new RuntimeException(sprintf('[%d] %s in %s:%d', $errorCode, $errorMessage, $filename, $line));

			return true;
		});

		$resource = fopen($url, $flags);

		restore_error_handler();

		if ($exception !== null) {
			if (is_resource($resource)) {
				fclose($resource);
			}

			throw $exception;
		}

		if ($resource === false) {
			$exception = new RuntimeException('Unable to open resource stream: ' . $url);
		}

		if ($exception !== null) {
			if (is_resource($resource)) {
				fclose($resource);
			}

			throw $exception;
		}

		/** @var resource $resource */
		return new self($resource, $readable, $writable, $readable);
	}

	/**
	 * @param int<0, max>|null $size
	 */
	public static function wrap(mixed $resource, ?bool $readable = null, ?bool $writable = null, ?bool $seekable = null, ?int $size = null): self
	{
		if (!is_resource($resource)) {
			throw new InvalidArgumentException('Resource expected, got ' . get_debug_type($resource));
		}

		$data = stream_get_meta_data($resource);
		$mode = Str::wrap($data['mode']);

		$readable ??= $mode->contains_any('r', '+');
		$writable ??= $mode->contains_any('w', 'a', 'x', 'c', '+');
		$seekable ??= $data['seekable'];

		return new self($resource, $readable, $writable, $seekable, $size);
	}

	/**
	 * @param closed-resource|resource|null $resource
	 * @param bool $readable
	 * @param bool $writable
	 * @param bool $seekable
	 * @param null|int<0, max> $size
	 */
	protected function __construct(
		private mixed $resource,
		private readonly bool $readable = true,
		private readonly bool $writable = false,
		private readonly bool $seekable = true,
		private ?int $size = null,
	) {
		if (!is_resource($this->resource)) {
			throw new InvalidArgumentException('ResourceStream expects a resource, got ' . get_debug_type($resource));
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

		/** @var resource $resource */
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

	public function seek(int $offset, #[ExpectedValues([SEEK_SET, SEEK_CUR, SEEK_END])] int $whence = SEEK_SET): void
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
	public function isWritable(): bool
	{
		return $this->writable;
	}

	public function write(string $string): int
	{
		if (!is_resource($this->resource)) {
			throw new RuntimeException('Resource is not available');
		}

		if (!$this->isWritable()) {
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
		assert(is_string($key) || $key === null);

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
