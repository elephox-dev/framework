<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\ReadonlyList;
use JetBrains\PhpStorm\Pure;

interface Message
{
	#[Pure] public function getProtocolVersion(): string;

	public function withProtocolVersion(string $version): static;

	#[Pure] public function getBody(): Stream;

	public function withBody(Stream $body): static;

	#[Pure] public function hasHeaderName(HeaderName $name): bool;

	/**
	 * @param HeaderName $name
	 * @return ReadonlyList<string>
	 */
	#[Pure] public function getHeaderName(HeaderName $name): ReadonlyList;

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

	public function getHeaderMap(): ReadonlyHeaderMap;

	public function withHeaderMap(HeaderMap $map): static;
}
