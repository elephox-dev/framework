<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ServerRequestBuilder;

interface HandledRequestBuilder extends ServerRequestBuilder
{
	public static function fromRequest(Request $request): HandledRequestBuilder;

	public function matchedTemplate(MatchedUrlTemplate $matchedUrlTemplate): HandledRequestBuilder;

	public function get(): HandledRequest;
}
