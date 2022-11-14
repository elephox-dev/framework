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

	public function removedParameter(string $key, ?ParameterSource $source = null): static;

	public function parameters(ParameterMap $parameters): static;

	public function getParameters(): ?ParameterMap;

	public function cookie(Cookie $cookie): static;

	public function cookies(CookieMap $cookies): static;

	public function getCookies(): ?CookieMap;

	public function uploadedFile(string $name, UploadedFile $uploadedFile): static;

	public function uploadedFiles(UploadedFileMap $uploadedFiles): static;

	public function getUploadedFiles(): ?UploadedFileMap;

	public function sessionParam(string $name, mixed $value): static;

	public function session(SessionMap $session): static;

	public function getSession(): ?SessionMap;

	public function get(): ServerRequest;
}
