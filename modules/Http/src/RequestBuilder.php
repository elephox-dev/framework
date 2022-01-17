<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use JetBrains\PhpStorm\Pure;

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

	public function requestMethod(RequestMethod $requestMethod): Contract\RequestBuilder
	{
		$this->method = $requestMethod;

		return $this;
	}

	public function requestUri(Url $url): Contract\RequestBuilder
	{
		$this->url = $url;

		return $this;
	}

	public function get(): Contract\Request
	{
		return new Request(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->method ?? RequestMethod::GET,
			$this->url ?? throw self::missingParameterException("url")
		);
	}
}
