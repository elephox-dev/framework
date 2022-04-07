<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\CookieMap;
use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\ParameterMap;
use Elephox\Http\Contract\SessionMap;
use Elephox\Http\Contract\UploadedFileMap;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class ServerRequest extends Request implements Contract\ServerRequest
{
	#[Pure]
	public static function build(): ServerRequestBuilder
	{
		return new ServerRequestBuilder();
	}

	#[Pure]
	public function __construct(
		string $protocolVersion,
		HeaderMap $headers,
		Stream $body,
		RequestMethod $method,
		Url $url,
		public readonly ParameterMap $parameters,
		public readonly CookieMap $cookies,
		public readonly ?SessionMap $session,
		public readonly UploadedFileMap $uploadedFiles,
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url);
	}

	#[Pure]
	public function with(): Contract\ServerRequestBuilder
	{
		return new ServerRequestBuilder(
			$this->protocolVersion,
			$this->headers,
			$this->body,
			$this->method,
			$this->url,
			$this->parameters,
			$this->cookies,
			$this->session,
			$this->uploadedFiles,
		);
	}

	#[Pure]
	public function getParameters(): ParameterMap
	{
		return $this->parameters;
	}

	#[Pure]
	public function getCookies(): CookieMap
	{
		return $this->cookies;
	}

	#[Pure]
	public function getUploadedFiles(): UploadedFileMap
	{
		return $this->uploadedFiles;
	}

	#[Pure]
	public function getSession(): ?SessionMap
	{
		return $this->session;
	}
}
