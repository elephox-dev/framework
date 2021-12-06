<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;

abstract class AbstractHttpMessage implements Contract\HttpMessage
{
	#[Pure] public function __construct(
		protected string $protocolVersion,
		protected Contract\ReadonlyHeaderMap $headers,
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
		return $this->headers->has($name);
	}

	public function getHeader($name): array
	{
		return $this->headers->get($name)->asArray();
	}

	public function getHeaderLine($name): string
	{
		return $this->headers->get($name)->join(',');
	}

	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	public function withHeaderName(Contract\HeaderName $name, array|string $value): static
	{
		$headers = clone $this->headers;
		$headers->put($name, $value);

		return self::withHeadMap($headers);
	}

	public function withAddedHeaderName(Contract\HeaderName $name, array|string $value): static
	{
		$headers = clone $this->headers;

		if ($headers->has($name)) {
			/** @var ArrayList<string> $values */
			$values = $headers->get($name);
			if (is_array($value)) {
				$values->addAll($value);
			} else {
				$values->add($value);
			}
		} else {
			$values = new ArrayList([$value]);
		}

		/** @var iterable<string> $values */
		$headers->put($name, $values);

		return self::withHeadMap($headers);	}

	public function withoutHeaderName(Contract\HeaderName $name): static
	{
		$headers = clone $this->headers;

		if ($headers->has($name)) {
			$headers->remove($name);
		}

		return self::withHeadMap($headers);
	}
}
