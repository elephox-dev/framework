<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Http\Contract\HeaderName;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;

abstract class AbstractHttpMessage implements Contract\HttpMessage
{
	#[Pure] public function __construct(
		protected string $protocolVersion,
		protected Contract\HeaderMap $headers,
		protected StreamInterface $body
	) {}

	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	public function getHeaders(): array
	{
		return $this->headers->asArray();
	}

	public function hasHeader($name): bool
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->hasHeaderName($headerName);
	}

	public function getHeader($name): array
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->getHeaderName($headerName)->asArray();
	}

	public function getHeaderLine($name): string
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->getHeaderName($headerName)->join(',');
	}

	public function hasHeaderName(HeaderName $name): bool
	{
		return $this->headers->has($name);
	}

	public function getHeaderName(HeaderName $name): ReadonlyList
	{
		return $this->headers->get($name);
	}

	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	public function withHeaderName(Contract\HeaderName $name, iterable|string $value): static
	{
		$headers = clone $this->headers;
		$headers->put($name, $value);

		return $this->withHeaderMap($headers);
	}

	public function withAddedHeaderName(Contract\HeaderName $name, iterable|string $value): static
	{
		$headers = clone $this->headers;

		if ($headers->has($name)) {
			/** @var ArrayList<string> $values */
			$values = $headers->get($name);
			if (is_iterable($value)) {
				$values->addAll($value);
			} else {
				$values->add($value);
			}
		} else {
			$values = new ArrayList([$value]);
		}

		/** @var iterable<string> $values */
		$headers->put($name, $values);

		return $this->withHeaderMap($headers);
	}

	public function withoutHeaderName(Contract\HeaderName $name): static
	{
		$headers = clone $this->headers;

		if ($headers->has($name)) {
			$headers->remove($name);
		}

		return $this->withHeaderMap($headers);
	}
}
