<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;

interface RequestPipelineEndpoint
{
	public function handle(Request $request): ResponseBuilder;
}
