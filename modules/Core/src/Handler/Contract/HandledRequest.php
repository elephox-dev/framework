<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Http\Contract\ServerRequest;
use JetBrains\PhpStorm\Pure;

interface HandledRequest extends ServerRequest
{
	#[Pure]
	public static function build(): HandledRequestBuilder;

	#[Pure]
	public function with(): HandledRequestBuilder;

	#[Pure]
	public function getMatchedTemplate(): MatchedUrlTemplate;
}
