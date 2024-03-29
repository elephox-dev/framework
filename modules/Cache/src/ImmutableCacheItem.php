<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;

/**
 * @psalm-consistent-constructor
 */
#[Immutable]
readonly class ImmutableCacheItem implements CacheItemInterface
{
	#[Pure]
	public function __construct(
		private string $key,
		private mixed $value,
		private bool $isHit,
		private ?DateTimeInterface $expiresAt,
	) {
	}

	#[Pure]
	public function getKey(): string
	{
		return $this->key;
	}

	#[Pure]
	public function get(): mixed
	{
		return $this->value;
	}

	#[Pure]
	public function isHit(): bool
	{
		return $this->isHit;
	}

	#[Pure]
	public function set(mixed $value): static
	{
		return new static(
			$this->key,
			$value,
			$this->isHit,
			$this->expiresAt,
		);
	}

	#[Pure]
	public function expiresAt(?DateTimeInterface $expiration): static
	{
		return new static(
			$this->key,
			$this->value,
			$this->isHit,
			$expiration,
		);
	}

	#[Pure]
	public function expiresAfter(DateInterval|int|null $time): static
	{
		if ($time === null) {
			return $this->expiresAt(null);
		}

		if (is_int($time)) {
			$time = new DateInterval("PT{$time}S");
		}

		/** @psalm-suppress ImpureMethodCall */
		$expiresAt = (new DateTime())->add($time);

		return $this->expiresAt($expiresAt);
	}
}
