<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
trait DerivesContentTypeFromHeaderMap
{
	#[Pure]
	abstract public function getHeaderMap(): ?Contract\HeaderMap;

	#[Pure]
	public function getContentType(): ?MimeTypeInterface
	{
		$headerMap = $this->getHeaderMap();

		/**
		 * @var null|Contract\HeaderMap $headerMap
		 */
		if ($headerMap === null) {
			return null;
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 */
		$headerName = $headerMap->firstKeyOrDefault(null, static fn (string|array $value, string $key) => strcasecmp($key, HeaderName::ContentType->value) === 0);
		if ($headerName === null) {
			return null;
		}

		/** @psalm-suppress ImpureMethodCall */
		$header = $headerMap->get($headerName);

		return MimeType::tryFrom($header[0]);
	}
}
