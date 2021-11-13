<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayList;
use Elephox\Core\Handler\Contract\Context;
use Elephox\Core\Handler\Contract\HandlerBinding;
use Exception;

class HandlerContainer implements Contract\HandlerContainer
{
	/**
	 * @var ArrayList<HandlerBinding>
	 */
	private ArrayList $bindings;

	public function __construct()
	{
		$this->bindings = new ArrayList();
	}

	public function register(HandlerBinding $binding): void
	{
		$this->bindings[] = $binding;
	}

	/**
	 * @throws Exception
	 */
	public function findHandler(Context $context): HandlerBinding
	{
		$binding = $this->bindings->first(static function (HandlerBinding $binding) use ($context): bool {
			return $binding->isApplicable($context);
		});

		if ($binding === null) {
			throw new Exception('No handler found for context');
		}

		return $binding;
	}
}
