<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface ServerRequest extends Request
{
	#[Pure]
	public function with(): ServerRequestBuilder;

	#[Pure]
	public function getParameters(): ParameterMap;

	#[Pure]
	public function getCookieMap(): CookieMap;

	#[Pure]
	public function getUploadedFiles(): UploadedFileMap;
}
