<?php
declare(strict_types=1);

namespace Elephox\Support;

use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use RuntimeException;

#[Immutable]
class CustomMimeType implements MimeTypeInterface
{
	/**
	 * @param non-empty-string $mimeType
	 * @param string $extension
	 *
	 * @return self
	 */
	#[Pure]
	public static function from(string $mimeType, string $extension = ''): self
	{
		return new self($mimeType, $extension);
	}

	/**
	 * @param string|resource $file
	 */
	public static function fromFile(mixed $file): MimeTypeInterface
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($file) && !is_resource($file)) {
			throw new InvalidArgumentException("MimeType::fromFile only accepts strings or resource streams!");
		}

		$mime = null;
		if (is_string($file) && function_exists('mime_content_type')) {
			$mime = mime_content_type($file);
		}

		if (empty($mime)) {
			if (is_string($file)) {
				return self::fromFileExtension($file);
			}

			// MAYBE: try to get meta information of resource to get filename

			throw new RuntimeException("Unable to determine mime type of file resource");
		}

		return MimeType::tryFrom($mime) ?? new self($mime);
	}

	public static function fromFileExtension(string $filename): MimeTypeInterface
	{
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		try {
			return MimeType::fromExtension($extension);
		} catch (InvalidArgumentException) {
			return new self(MimeType::ApplicationOctetStream->value, $extension);
		}
	}

	/**
	 * @param non-empty-string $value
	 * @param string $extension
	 */
	#[Pure]
	public function __construct(
		private string $value,
		private string $extension = ''
	)
	{
	}

	#[Pure]
	public function getValue(): string
	{
		return $this->value;
	}

	#[Pure]
	public function getExtension(): string
	{
		return $this->extension;
	}
}
