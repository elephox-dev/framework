<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\RequestMethod;

interface ActionAttribute extends RoutingAttribute
{
	/**
	 * @return GenericList<RequestMethod>
	 */
	public function getRequestMethods(): GenericList;
}
