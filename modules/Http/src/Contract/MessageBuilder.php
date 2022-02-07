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

	public function body(Stream $body): static;

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array $data): static;

	/**
	 * @param string $name
	 * @param list<string> $value
	 *
	 * @return static
	 */
	public function header(string $name, array $value): static;

	public function headerMap(HeaderMap $headers): static;

	public function get(): Message;
}
