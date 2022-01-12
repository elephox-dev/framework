<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Pure;

interface Response extends Message
{
	#[Pure]
	public function with(): ResponseBuilder;
}
