<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface ServerRequest extends Request
{
	#[Pure]
	public static function build(): ServerRequestBuilder;

	#[Pure]
	public function with(): ServerRequestBuilder;

	#[Pure]
	public function getParameters(): ParameterMap;

	#[Pure]
	public function getCookies(): CookieMap;

	#[Pure]
	public function getUploadedFiles(): UploadedFileMap;

	#[Pure]
	public function getSession(): ?SessionMap;
}
