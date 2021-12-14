<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;

interface ServerRequest extends Request
{
	/**
	 * @return ArrayMap<string, string>
	 */
	public function getServerParamsMap(): ArrayMap;

	/**
	 * @return ArrayList<Cookie>
	 */
	public function getCookies(): ArrayList;

	/**
	 * @param iterable<Cookie> $cookies
	 */
	public function withCookies(iterable $cookies): static;

	public function withCookie(Cookie $cookie): static;

	/**
	 * @return ArrayList<UploadedFile>
	 */
	public function getUploadedFiles(): ArrayList;

	/**
	 * @param iterable<UploadedFile> $uploadedFiles
	 */
	public function withUploadedFiles(iterable $uploadedFiles): static;

	/**
	 * @return array|object|null
	 */
	public function getParsedBody(): null|array|object;
}
