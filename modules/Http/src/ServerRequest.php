<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\CookieMap;
use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\ParameterMap;
use Elephox\Http\Contract\UploadedFileList;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class ServerRequest extends Request implements Contract\ServerRequest
{
	#[Pure]
	public function __construct(
		string $protocolVersion,
		HeaderMap $headers,
		Stream $body,
		RequestMethod $method,
		Url $url,
		public readonly ParameterMap $parameters,
		public readonly CookieMap $cookies,
		public readonly UploadedFileList $uploadedFiles
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url);
	}

	#[Pure]
	public function with(): Contract\ServerRequestBuilder
	{
		return new ServerRequestBuilder();
	}
}
