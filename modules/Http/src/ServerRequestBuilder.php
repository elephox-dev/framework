<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Cookie;
use Elephox\Http\Contract\UploadedFile;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use JetBrains\PhpStorm\Pure;

class ServerRequestBuilder extends RequestBuilder implements Contract\ServerRequestBuilder
{
	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		?RequestMethod $method = null,
		?Url $url = null,
		protected ?Contract\ParameterMap $parameterMap = null,
		protected ?Contract\CookieMap $cookies = null,
		protected ?Contract\UploadedFileList $uploadedFiles = null
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url);
	}

	public function parameter(string $key, array|int|string $value, ParameterSource $source): Contract\ServerRequestBuilder
	{
		if ($this->parameterMap === null) {
			$this->parameterMap = new ParameterMap();
		}

		$this->parameterMap->put($key, $source, $value);

		return $this;
	}

	public function parameterMap(Contract\ParameterMap $parameterMap): Contract\ServerRequestBuilder
	{
		$this->parameterMap = $parameterMap;

		return $this;
	}

	public function cookie(Cookie $cookie): Contract\ServerRequestBuilder
	{
		if ($this->cookies === null) {
			$this->cookies = new CookieMap();
		}

		$this->cookies->put($cookie->getName(), $cookie);

		return $this;
	}

	public function cookieMap(Contract\CookieMap $cookieMap): Contract\ServerRequestBuilder
	{
		$this->cookies = $cookieMap;

		return $this;
	}

	public function uploadedFile(UploadedFile $uploadedFile): Contract\ServerRequestBuilder
	{
		if ($this->uploadedFiles === null) {
			$this->uploadedFiles = new UploadedFileList();
		}

		$this->uploadedFiles->add($uploadedFile);

		return $this;
	}

	public function uploadedFiles(Contract\UploadedFileList $uploadedFiles): Contract\ServerRequestBuilder
	{
		$this->uploadedFiles = $uploadedFiles;

		return $this;
	}

	public function build(): Contract\ServerRequest
	{
		return new ServerRequest(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->method ?? RequestMethod::GET,
			$this->url ?? throw self::missingParameterException("url"),
			$this->parameterMap ?? new ParameterMap(),
			$this->cookies ?? new CookieMap(),
			$this->uploadedFiles ?? new UploadedFileList()
		);
	}
}
