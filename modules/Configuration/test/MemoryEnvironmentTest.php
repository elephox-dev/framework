<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\MemoryEnvironment
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Configuration\DotEnvEnvironment
 *
 * @internal
 */
class MemoryEnvironmentTest extends TestCase
{
	public function testLoadFromEnvFileOverwritesByDefault(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$env->loadFromEnvFile();

		static::assertEquals('default', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertEquals('false', $env['TEST1']);

		$env->loadFromEnvFile('test1');

		static::assertEquals('test1', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertEquals('true', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		$env->loadFromEnvFile('test2');

		static::assertEquals('test2', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertEquals('true', $env['TEST1']);
		static::assertEquals('true', $env['TEST2']);
	}

	public function testLoadFromEnvFileDoesntOverwriteIfGiven(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$env->loadFromEnvFile(overwriteExisting: false);

		static::assertEquals('default', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertEquals('false', $env['TEST1']);

		$env->loadFromEnvFile('test1', overwriteExisting: false);

		static::assertEquals('default', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertEquals('false', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		unset($env['TEST1']);

		$env->loadFromEnvFile('test2', overwriteExisting: false);

		static::assertEquals('default', $env['SOURCE']);
		static::assertEquals('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertEquals('true', $env['TEST2']);

		unset($env['TEST2']);

		static::assertFalse(isset($env['TEST2']));
	}
}
