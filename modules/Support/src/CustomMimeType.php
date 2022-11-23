<?php
declare(strict_types=1);

namespace Elephox\Support;

use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use RuntimeException;
use Throwable;

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
		$mime = null;
		if ((is_string($file) || is_resource($file)) && function_exists('mime_content_type')) {
			try {
				$mime = mime_content_type($file);
			} catch (Throwable) {
				// ignore
			}
		}

		if (empty($mime)) {
			if (is_string($file) && !empty($file)) {
				return self::fromFilename($file);
			}

			if (is_resource($file)) {
				$metadata = stream_get_meta_data($file);
				if (array_key_exists('uri', $metadata)) {
					$filename = pathinfo($metadata['uri'], PATHINFO_BASENAME);
					if (!empty($filename)) {
						return self::fromFilename($filename);
					}
				}
			}

			throw new RuntimeException('Unable to determine mime type of file');
		}

		return MimeType::tryFrom($mime) ?? new self($mime);
	}

	public static function fromFilename(string $filename): MimeTypeInterface
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
	protected function __construct(
		private readonly string $value,
		private readonly string $extension = '',
	) {
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
