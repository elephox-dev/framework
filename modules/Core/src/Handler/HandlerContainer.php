<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayList;
use Elephox\Core\Context\Contract\Context;
use Exception;

class HandlerContainer implements Contract\HandlerContainer
{
	/**
	 * @var ArrayList<HandlerBinding<object, Context>>
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
	public function findHandler(Context $context): HandlerBinding
	{
		$bindings = $this->bindings->where(static function (HandlerBinding $binding) use ($context): bool {
			return $binding->isApplicable($context);
		});

		if ($bindings->isEmpty()) {
			throw new Exception('No handler found for context');
		}

		// TODO: find a better way to choose the correct binding

		return $bindings->first();
	}
}
