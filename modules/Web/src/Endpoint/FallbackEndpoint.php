<?php
declare(strict_types=1);

namespace Elephox\Web\Endpoint;

use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Web\Contract\RequestPipelineEndpoint;

class FallbackEndpoint implements RequestPipelineEndpoint
{
	public function handle(RequestContract $request): ResponseBuilderContract
	{
		return Response::build()->responseCode(ResponseCode::NotFound);
	}
}
