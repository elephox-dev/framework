<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ParameterSource;

/**
 * @psalm-consistent-constructor
 */
interface ServerRequestBuilder extends RequestBuilder
{
	public static function fromRequest(Request $request): static;

	public static function fromGlobals(): ServerRequest;

	public function parameter(string $key, int|string|array $value, ParameterSource $source): static;

	public function parameterMap(ParameterMap $parameterMap): static;

	public function cookie(Cookie $cookie): static;

	public function cookieMap(CookieMap $cookieMap): static;

	public function uploadedFile(string $name, UploadedFile $uploadedFile): static;

	public function uploadedFiles(UploadedFileMap $uploadedFiles): static;

	public function get(): ServerRequest;
}
