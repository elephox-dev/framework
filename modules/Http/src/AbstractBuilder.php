<?php
declare(strict_types=1);

namespace Elephox\Http;

use LogicException;

abstract class AbstractBuilder
{
	protected static function missingParameterException(string $name): LogicException
	{
		return new LogicException("Missing required parameter: $name");
	}
}
