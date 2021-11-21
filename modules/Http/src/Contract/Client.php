<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface Client
{
	public function execute(Request $request): Response;
}
