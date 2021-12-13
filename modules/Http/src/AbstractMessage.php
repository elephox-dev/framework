<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Http\Contract\HeaderName;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use JetBrains\PhpStorm\Pure;

abstract class AbstractMessage implements Contract\Message
{
	protected Stream $body;
	protected Contract\HeaderMap $headers;

	protected static function createHeaderMap(): Contract\HeaderMap
	{
		return new HeaderMap();
	}

	public function __construct(
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		protected string $protocolVersion = "1.1"
	) {
		$this->headers = $headers ?? static::createHeaderMap();
		$this->body = $body ?? new EmptyStream();
	}

	#[Pure] public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	#[Pure] public function hasHeaderName(HeaderName $name): bool
	{
		return $this->headers->has($name);
	}

	#[Pure] public function getHeaderName(HeaderName $name): ReadonlyList
	{
		return $this->headers->get($name);
	}

	#[Pure] public function getBody(): Stream
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
