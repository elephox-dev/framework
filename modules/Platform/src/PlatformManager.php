<?php
declare(strict_types=1);

namespace Elephox\Platform;

use Elephox\Platform\Contract\PlatformInterface;
use Elephox\Platform\Contract\SessionPlatform;
use Elephox\Platform\Native\NativeSessionPlatform;

class PlatformManager
{
	/**
	 * @var array<class-string<PlatformInterface>, PlatformInterface>
	 */
	private static array $platforms = [];

	/**
	 * @var array<class-string<PlatformInterface>, class-string<PlatformInterface>>
	 */
	public static array $services = [
		SessionPlatform::class => NativeSessionPlatform::class,
	];

	/**
	 * @template T of PlatformInterface
	 *
	 * @param class-string<T> $service
	 *
	 * @return T
	 */
	public static function get(string $service): PlatformInterface
	{
		if (isset(self::$platforms[$service])) {
			/** @var T */
			return self::$platforms[$service];
		}

		/** @var T $platform */
		$platform = new (self::$services[$service])();
		self::$platforms[$service] = $platform;

		return $platform;
	}
}
