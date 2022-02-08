<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Http\Contract\CookieMap;
use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\ParameterMap;
use Elephox\Http\Contract\SessionMap;
use Elephox\Http\Contract\UploadedFileMap;
use Elephox\Http\ServerRequest;
use Elephox\Http\RequestMethod;
use Elephox\Http\Url;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class HandledRequest extends ServerRequest implements Contract\HandledRequest
{
	#[Pure]
	public function __construct(
		string $protocolVersion,
		HeaderMap $headers,
		Stream $body,
		RequestMethod $method,
		Url $url,
		ParameterMap $parameters,
		CookieMap $cookies,
		?SessionMap $session,
		UploadedFileMap $uploadedFiles,
		public readonly Contract\MatchedUrlTemplate $template
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url, $parameters, $cookies, $session, $uploadedFiles);
	}

	#[Pure]
	public static function build(): HandledRequestBuilder
	{
		return new HandledRequestBuilder();
	}

	#[Pure]
	public function with(): Contract\HandledRequestBuilder
	{
		return new HandledRequestBuilder(
			$this->protocolVersion,
			$this->headers,
			$this->body,
			$this->method,
			$this->url,
			$this->parameters,
			$this->cookies,
			$this->session,
			$this->uploadedFiles,
			$this->template,
		);
	}

	#[Pure]
	public function getMatchedTemplate(): Contract\MatchedUrlTemplate
	{
		return $this->template;
	}
}
