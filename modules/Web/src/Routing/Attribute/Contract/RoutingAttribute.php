<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Contract;

interface RoutingAttribute
{
	public function getPath(): ?string;
}
