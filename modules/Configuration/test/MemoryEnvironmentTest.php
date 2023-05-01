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
final class MemoryEnvironmentTest extends TestCase
{
	public function testLoadFromEnvFileOverwritesByDefault(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$envFile = $env->getDotEnvFileName();
		$env->loadFromEnvFile($envFile);

		self::assertSame('default', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertFalse(isset($env['TEST1']));
		self::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		self::assertSame('false', $env['TEST1']);

		$test1EnvFile = $env->getDotEnvFileName(envName: 'test1');
		$env->loadFromEnvFile($test1EnvFile);

		self::assertSame('test1', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertSame('true', $env['TEST1']);
		self::assertFalse(isset($env['TEST2']));

		$test2EnvFile = $env->getDotEnvFileName(envName: 'test2');
		$env->loadFromEnvFile($test2EnvFile);

		self::assertSame('test2', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertSame('true', $env['TEST1']);
		self::assertSame('true', $env['TEST2']);
	}

	public function testLoadFromEnvFileDoesntOverwriteIfGiven(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$envFile = $env->getDotEnvFileName();
		$env->loadFromEnvFile($envFile, overwriteExisting: false);

		self::assertSame('default', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertFalse(isset($env['TEST1']));
		self::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		self::assertSame('false', $env['TEST1']);

		$test1EnvFile = $env->getDotEnvFileName(envName: 'test1');
		$env->loadFromEnvFile($test1EnvFile, overwriteExisting: false);

		self::assertSame('default', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertSame('false', $env['TEST1']);
		self::assertFalse(isset($env['TEST2']));

		unset($env['TEST1']);

		$test2EnvFile = $env->getDotEnvFileName(envName: 'test2');
		$env->loadFromEnvFile($test2EnvFile, overwriteExisting: false);

		self::assertSame('default', $env['SOURCE']);
		self::assertSame('true', $env['DEFAULT']);
		self::assertFalse(isset($env['TEST1']));
		self::assertSame('true', $env['TEST2']);

		unset($env['TEST2']);

		self::assertFalse(isset($env['TEST2']));
	}
}
