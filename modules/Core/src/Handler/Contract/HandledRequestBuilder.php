<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ServerRequestBuilder;

/**
 * @psalm-consistent-constructor
 */
interface HandledRequestBuilder extends ServerRequestBuilder
{
	public static function fromRequest(Request $request): static;

	public function matchedTemplate(MatchedUrlTemplate $matchedUrlTemplate): static;

	public function get(): HandledRequest;
}
