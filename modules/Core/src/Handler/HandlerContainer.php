<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayList;
use Elephox\Core\Context\Contract\Context;
use Exception;

class HandlerContainer implements Contract\HandlerContainer
{
	/**
	 * @var ArrayList<Contract\HandlerBinding<object, Context>>
	 */
	private ArrayList $bindings;

	public function __construct()
	{
		$this->bindings = new ArrayList();
	}

	public function register(Contract\HandlerBinding $binding): void
	{
		$this->bindings[] = $binding;
	}

	/**
	 * @throws Exception
	 */
	public function findHandler(Context $context): Contract\HandlerBinding
	{
		$bindings = $this->bindings->where(static function (Contract\HandlerBinding $binding) use ($context): bool {
			return $binding->isApplicable($context);
		});

		// TODO: find a better way to choose the correct binding

		$binding = $bindings->first();
		if ($binding === null) {
			throw new Exception('No handler found for context');
		}

		return $binding;
	}
}
