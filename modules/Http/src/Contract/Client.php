<?php

namespace Philly\Http\Contract;

interface Client
{
	public function execute(Request $request): Response;
}
