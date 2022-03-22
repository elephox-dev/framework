<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Host\Contract\RequestPipelineEndpoint;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;

class FallbackEndpoint implements RequestPipelineEndpoint
{
	public function handle(RequestContract $request): ResponseBuilderContract
	{
		return Response::build()->responseCode(ResponseCode::NotFound);
	}
}
