<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ServerRequestInterface;

#[Immutable]
interface ServerRequest extends Request, ServerRequestInterface
{
	#[Pure]
	public static function build(): ServerRequestBuilder;

	#[Pure]
	public function with(): ServerRequestBuilder;

	#[Pure]
	public function getParameterMap(): ParameterMap;

	#[Pure]
	public function getCookieMap(): CookieMap;

	#[Pure]
	public function getUploadedFileMap(): UploadedFileMap;

	#[Pure]
	public function getSessionMap(): ?SessionMap;
}
