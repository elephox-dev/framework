<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use JetBrains\PhpStorm\Pure;

/**
 * @psalm-consistent-constructor
 */
class RequestBuilder extends AbstractMessageBuilder implements Contract\RequestBuilder
{
	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		protected ?RequestMethod $method = null,
		protected ?Url $url = null,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	public function requestMethod(RequestMethod $requestMethod): static
	{
		$this->method = $requestMethod;

		return $this;
	}

	public function getRequestMethod(): ?RequestMethod
	{
		return $this->method;
	}

	public function requestUrl(Url $url, bool $preserveHostHeader = false): static
	{
		$this->url = $url;

		if (!$preserveHostHeader) {
			$host = $url->getHost();

			$this->header(HeaderName::Host->value, $host);
		}

		return $this;
	}

	public function getRequestUrl(): ?Url
	{
		return $this->url;
	}

	public function get(): Contract\Request
	{
		return new Request(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->method ?? RequestMethod::GET,
			$this->url ?? throw self::missingParameterException('url'),
		);
	}
}
