<?php
declare(strict_types=1);

namespace Elephox\DI;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class ServiceException extends LogicException implements ContainerExceptionInterface
{
}
