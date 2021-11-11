<?php

namespace Elephox\Http\Contract;

interface Client
{
	public function execute(Request $request): Response;
}
