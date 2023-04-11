<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute;

use Elephox\Web\Routing\Attribute\Contract\RoutingAttribute;

abstract class AbstractRoutingAttribute implements RoutingAttribute
{
	public const DEFAULT_PATH = null;

	public function __construct(
		private readonly ?string $path = self::DEFAULT_PATH,
	) {
	}

	public function getPath(): ?string
	{
		return $this->path;
	}
}
