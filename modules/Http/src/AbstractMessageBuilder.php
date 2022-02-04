<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\MessageBuilder;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\StringStream;
use JetBrains\PhpStorm\Pure;
use JsonException;

/**
 * @psalm-consistent-constructor
 */
abstract class AbstractMessageBuilder extends AbstractBuilder implements MessageBuilder
{
	public const DefaultProtocolVersion = '1.1';

	#[Pure]
	public function __construct(
		protected ?string $protocolVersion = null,
		protected ?Contract\HeaderMap $headers = null,
		protected ?Stream $body = null
	) {
	}

	public function protocolVersion(string $version): static
	{
		$this->protocolVersion = $version;

		return $this;
	}

	public function body(Stream $body): static
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array $data): static
	{
		$this->body = new StringStream(json_encode($data, JSON_THROW_ON_ERROR));

		return $this;
	}

	public function header(string $name, array $value): static
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$this->headers->put($name, $value);

		return $this;
	}

	public function headerMap(Contract\HeaderMap $headers): static
	{
		$this->headers = $headers;

		return $this;
	}
}
