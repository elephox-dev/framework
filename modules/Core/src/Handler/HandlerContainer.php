<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayList;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\UnhandledContextException;
use Exception;

class HandlerContainer implements Contract\HandlerContainer
{
	/**
	 * @var ArrayList<Contract\HandlerBinding<Closure():mixed, Context>>
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

	public function findHandler(Context $context): Contract\HandlerBinding
	{
		$bindings = $this->bindings->where(static fn(Contract\HandlerBinding $binding): bool => $binding->isApplicable($context));
		if ($bindings->isEmpty()) {
			throw new UnhandledContextException($context);
		}

		// TODO: find a better way to choose the correct binding if there are multiple applicable bindings
		/** @var Contract\HandlerBinding */
		return $bindings->first();
	}
}
