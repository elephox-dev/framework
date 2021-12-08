<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\ReadonlyList;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

interface HttpMessage extends MessageInterface
{
	public function getHeaderMap(): ReadonlyHeaderMap;

	public function withoutBody(): static;

	public function hasHeaderName(HeaderName $name): bool;

	/**
	 * @return array<non-empty-string, list<string>>
	 */
	public function getHeaders();

	/**
	 * @param string $name
	 * @return array<string>
	 */
	public function getHeader($name);

	/**
	 * @param HeaderName $name
	 * @return ReadonlyList<string>
	 */
	public function getHeaderName(HeaderName $name): ReadonlyList;

	/**
	 * @param HeaderName $name
	 * @param string|iterable<string> $value
	 * @return static
	 */
	public function withHeaderName(HeaderName $name, string|iterable $value): static;

	/**
	 * @param HeaderName $name
	 * @param string|iterable<string> $value
	 * @return static
	 */
	public function withAddedHeaderName(HeaderName $name, string|iterable $value): static;

	public function withoutHeaderName(HeaderName $name): static;

	public function withHeaderMap(HeaderMap $map): static;
}
