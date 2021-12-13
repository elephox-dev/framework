<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Http\Contract\Cookie;
use Elephox\Stream\Contract\Stream;

class ServerRequest extends Request implements Contract\ServerRequest
{

	public function __construct(
		Contract\RequestMethod     $method = RequestMethod::GET,
		?Contract\Url              $url = null,
		?Contract\RequestHeaderMap $headers = null,
		?Stream                    $body = null,
		string                     $protocolVersion = "1.1",
		bool                       $inferHostHeader = true
	)
	{
		parent::__construct($method, $url, $headers, $body, $protocolVersion, $inferHostHeader);
	}

	public function getServerParamsMap(): GenericMap
	{
		// TODO: Implement getServerParamsMap() method.
	}

	public function getCookies(): GenericList
	{
		// TODO: Implement getCookies() method.
	}

	public function withCookies(iterable $cookies): static
	{
		// TODO: Implement withCookies() method.
	}

	public function withCookie(Cookie $cookie): static
	{
		// TODO: Implement withCookie() method.
	}

	public function getUploadedFiles(): GenericList
	{
		// TODO: Implement getUploadedFiles() method.
	}

	public function withUploadedFiles(iterable $uploadedFiles): static
	{
		// TODO: Implement withUploadedFiles() method.
	}

	public function getParsedBody(): null|array|object
	{
		// TODO: Implement getParsedBody() method.
	}
}
