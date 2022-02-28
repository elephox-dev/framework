<?php
declare(strict_types=1);

namespace Elephox\Promises;

use Closure;
use JetBrains\PhpStorm\Pure;
use Throwable;

class Promise
{
	#[Pure]
	public static function me(Closure $closure): self
	{
		return new self($closure);
	}

	public function __construct(private readonly Closure $resolver)
	{
	}

	#[Pure]
	public function then(Closure $next): self
	{
		return new self(function () use ($next): mixed {
			return $next($this->await());
		});
	}

	#[Pure]
	public function catch(Closure $catch): self
	{
		return new self(function () use ($catch): mixed {
			try {
				return $this->await();
			} catch (Throwable $e) {
				return $catch($e);
			}
		});
	}

	#[Pure]
	public function finally(Closure $finally): self
	{
		return new self(function () use ($finally): mixed {
			try {
				return $this->await();
			} finally {
				$finally();
			}
		});
	}

	/**
	 * @throws \Throwable
	 */
	public function await(): mixed
	{
		return ($this->resolver)();
	}
}
