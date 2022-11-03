<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\MemoryEnvironment
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Configuration\DotEnvEnvironment
 * @covers \Elephox\Files\File
 *
 * @internal
 */
class MemoryEnvironmentTest extends TestCase
{
	public function testLoadFromEnvFileOverwritesByDefault(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$envFile = $env->getDotEnvFileName();
		$env->loadFromEnvFile($envFile);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertSame('false', $env['TEST1']);

		$test1EnvFile = $env->getDotEnvFileName(envName: 'test1');
		$env->loadFromEnvFile($test1EnvFile);

		static::assertSame('test1', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('true', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		$test2EnvFile = $env->getDotEnvFileName(envName: 'test2');
		$env->loadFromEnvFile($test2EnvFile);

		static::assertSame('test2', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('true', $env['TEST1']);
		static::assertSame('true', $env['TEST2']);
	}

	public function testLoadFromEnvFileDoesntOverwriteIfGiven(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$envFile = $env->getDotEnvFileName();
		$env->loadFromEnvFile($envFile, overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertSame('false', $env['TEST1']);

		$test1EnvFile = $env->getDotEnvFileName(envName: 'test1');
		$env->loadFromEnvFile($test1EnvFile, overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('false', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		unset($env['TEST1']);

		$test2EnvFile = $env->getDotEnvFileName(envName: 'test2');
		$env->loadFromEnvFile($test2EnvFile, overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertSame('true', $env['TEST2']);

		unset($env['TEST2']);

		static::assertFalse(isset($env['TEST2']));
	}
}
