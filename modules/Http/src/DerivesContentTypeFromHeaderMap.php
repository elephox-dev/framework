<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\DefaultEqualityComparer;
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
		 * @psalm-suppress UnnecessaryVarAnnotation
		 *
		 * @var null|Contract\HeaderMap $headerMap
		 */
		if ($headerMap === null) {
			return null;
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @psalm-suppress UnusedClosureParam
		 */
		$headerName = $headerMap->firstKeyOrDefault(null, static fn (string|array $value, string $key) => DefaultEqualityComparer::equalsIgnoreCase($key, HeaderName::ContentType->name));
		if ($headerName === null) {
			return null;
		}

		/** @psalm-suppress ImpureMethodCall */
		$header = $headerMap->get($headerName);
		if (is_array($header)) {
			return MimeType::tryFrom($header[0]);
		}

		return MimeType::tryFrom($header);
	}
}
