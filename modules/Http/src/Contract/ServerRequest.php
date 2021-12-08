<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;
use Psr\Http\Message\ServerRequestInterface;

interface ServerRequest extends ServerRequestInterface, Request
{
	/**
	 * @return array<string, string>
	 */
	public function getServerParams(): array;

	/**
	 * @return GenericMap<string, string>
	 */
	public function getServerParamsMap(): GenericMap;

	/**
	 * @return array<string, string>
	 */
	public function getCookieParams(): array;

	/**
	 * @return GenericList<Cookie>
	 */
	public function getCookies(): GenericList;

	/**
	 * @param array $cookies
	 */
	public function withCookieParams(array $cookies): static;

	/**
	 * @param iterable<Cookie> $cookies
	 */
	public function withCookies(iterable $cookies): static;

	public function withCookie(Cookie $cookie): static;

	public function getQueryParams(): array;

	public function withQueryParams(array $query): static;

	/**
	 * @return list<UploadedFile>
	 */
	public function getUploadedFiles(): array;

	public function withUploadedFiles(array $uploadedFiles): static;

	/**
	 * @template T of object|array
	 *
	 * @return array|T|null
	 */
	public function getParsedBody(): null|array|object;

	public function withParsedBody($data): static;

	public function getAttributes(): array;

	public function getAttribute($name, $default = null): mixed;

	public function withAttribute($name, $value): static;

	public function withoutAttribute($name): static;
}
