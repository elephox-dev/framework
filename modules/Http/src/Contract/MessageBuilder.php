<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Files\Contract\File;
use Elephox\Http\HeaderName;
use JetBrains\PhpStorm\Language;
use JsonException;
use Psr\Http\Message\StreamInterface;

/**
 * @psalm-consistent-constructor
 */
interface MessageBuilder
{
	public function protocolVersion(string $version): static;

	public function getProtocolVersion(): ?string;

	public function body(StreamInterface $body): static;

	public function getBody(): ?StreamInterface;

	public function textBody(string $content): static;

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array|object $data): static;

	/**
	 * @param resource $resource
	 */
	public function resourceBody(mixed $resource): static;

	public function htmlBody(#[Language('HTML')] string $content): static;

	public function fileBody(string|File $path): static;

	/**
	 * @param string|array<mixed, string> $value
	 */
	public function header(string|HeaderName $name, string|array $value): static;

	/**
	 * @param string|array<mixed, string> $value
	 */
	public function addedHeader(string|HeaderName $name, string|array $value): static;

	public function removedHeader(string|HeaderName $name): static;

	public function headerMap(HeaderMap $headers): static;

	public function getHeaderMap(): ?HeaderMap;

	public function get(): Message;
}
