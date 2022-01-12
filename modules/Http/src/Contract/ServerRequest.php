<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericList;

interface ServerRequest extends Request
{
	public function getParameterMap(): ParameterMap;

	public function getCookies(): CookieMap;

	/**
	 * @return GenericList<UploadedFile>
	 */
	public function getUploadedFiles(): GenericList;

	public function with(): ServerRequestBuilder;
}
