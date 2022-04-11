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
	 *
	 * @param array $data
	 */
	public function jsonBody(array $data): static;

	public function resourceBody(mixed $resource): static;

	public function htmlBody(string $content): static;

	public function fileBody(string $path): static;

	/**
	 * @param string|list<string> $value
	 * @param string $name
	 */
	public function header(string $name, string|array $value): static;

	/**
	 * @param string|list<string> $value
	 * @param string $name
	 */
	public function addHeader(string $name, string|array $value): static;

	public function headerMap(HeaderMap $headers): static;

	public function getHeaderMap(): ?HeaderMap;

	public function get(): Message;
}
