<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ParameterSource;

interface ServerRequestBuilder extends RequestBuilder
{
	public function parameter(string $key, int|string|array $value, ParameterSource $source): ServerRequestBuilder;

	public function parameterMap(ParameterMap $parameterMap): ServerRequestBuilder;

	public function cookie(Cookie $cookie): ServerRequestBuilder;

	public function cookieMap(CookieMap $cookieMap): ServerRequestBuilder;

	public function uploadedFile(UploadedFile $uploadedFile): ServerRequestBuilder;

	public function uploadedFiles(UploadedFileList $uploadedFiles): ServerRequestBuilder;

	public function build(): ServerRequest;
}
