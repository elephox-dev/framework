<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\Http\Contract\HeaderName;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;

abstract class AbstractHttpMessage implements Contract\HttpMessage
{
	protected StreamInterface $body;
	protected Contract\HeaderMap $headers;

	protected static function createHeaderMap(): Contract\HeaderMap
	{
		return new HeaderMap();
	}

	public function __construct(
		?Contract\HeaderMap $headers = null,
		?StreamInterface $body = null,
		protected string $protocolVersion = "1.1"
	) {
		$this->headers = $headers ?? static::createHeaderMap();
		$this->body = $body ?? new EmptyStream();
	}

	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	public function getHeaders()
	{
		return $this->headers->asArray();
	}

	public function hasHeader($name)
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->hasHeaderName($headerName);
	}

	public function getHeader($name)
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->getHeaderName($headerName)->asArray();
	}

	public function getHeaderLine($name)
	{
		$headerName = HeaderMap::parseHeaderName($name);

		try {
			return $this->getHeaderName($headerName)->join(', ');
		} catch (OffsetNotFoundException) {
			return "";
		}
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
