<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\GlobalEnvironment
 *
 * @internal
 */
class GlobalEnvironmentTest extends TestCase
{
	public function envDataProvider(): iterable
	{
		yield [null, null, false];
		yield ['false', null, false];
		yield ['true', null, true];
		yield ['off', null, false];
		yield ['on', null, true];
		yield ['0', null, false];
		yield ['1', null, true];

		yield [null, 'development', true];
		yield [null, 'dev', true];
		yield [null, 'local', true];
		yield [null, 'debug', true];
		yield [null, 'prod', false];
		yield [null, 'staging', false];
		yield [null, 'testing', false];

		yield ['false', 'development', false];
		yield ['true', 'development', true];
		yield ['true', 'production', true];
		yield ['false', 'staging', false];
	}

	/**
	 * @dataProvider envDataProvider
	 *
	 * @param ?string $appDebug
	 * @param ?string $appEnv
	 * @param bool $shouldBeDevelopment
	 */
	public function testIsDevelopment(?string $appDebug, ?string $appEnv, bool $shouldBeDevelopment): void
	{
		unset($_ENV['APP_DEBUG'], $_ENV['APP_ENV']);

		if ($appDebug !== null) {
			$_ENV['APP_DEBUG'] = $appDebug;
		}

		if ($appEnv !== null) {
			$_ENV['APP_ENV'] = $appEnv;
		}

		$env = new GlobalEnvironment();
		static::assertEquals($shouldBeDevelopment, $env->isDevelopment());
	}
}
