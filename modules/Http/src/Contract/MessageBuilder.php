<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Stream\Contract\Stream;
use JsonException;

/**
 * @psalm-consistent-constructor
 */
interface MessageBuilder
{
	public function protocolVersion(string $version): static;

	public function getProtocolVersion(): ?string;

	public function body(Stream $body): static;

	public function getBody(): ?Stream;

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array $data): static;

	public function resourceBody(mixed $resource): static;

	public function htmlBody(string $content): static;

	/**
	 * @param string $name
	 * @param list<string> $value
	 *
	 * @return static
	 */
	public function header(string $name, array $value): static;

	public function headerMap(HeaderMap $headers): static;

	public function getHeaderMap(): ?HeaderMap;

	public function get(): Message;
}
