<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\Collection\Contract\GenericList;

/**
 * @extends GenericList<\Elephox\DI\ServiceDescriptor>
 */
interface ServiceCollection extends GenericList
{
}
