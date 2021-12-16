<?php
declare(strict_types=1);

namespace Elephox\Core\Middleware\Contract;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Contract\HandlerStackMeta;

interface Middleware extends HandlerStackMeta
{
    /**
     * @param Context $context
     * @param Closure(Context): mixed $next
     * @return mixed
     */
	public function handle(Context $context, Closure $next): mixed;
}
