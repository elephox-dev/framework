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

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertSame('false', $env['TEST1']);

		$env->loadFromEnvFile('test1');

		static::assertSame('test1', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('true', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		$env->loadFromEnvFile('test2');

		static::assertSame('test2', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('true', $env['TEST1']);
		static::assertSame('true', $env['TEST2']);
	}

	public function testLoadFromEnvFileDoesntOverwriteIfGiven(): void
	{
		$env = new MemoryEnvironment(__DIR__ . '/data');
		$env->loadFromEnvFile(overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertFalse(isset($env['TEST2']));

		$env['TEST1'] = 'false';
		static::assertSame('false', $env['TEST1']);

		$env->loadFromEnvFile('test1', overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertSame('false', $env['TEST1']);
		static::assertFalse(isset($env['TEST2']));

		unset($env['TEST1']);

		$env->loadFromEnvFile('test2', overwriteExisting: false);

		static::assertSame('default', $env['SOURCE']);
		static::assertSame('true', $env['DEFAULT']);
		static::assertFalse(isset($env['TEST1']));
		static::assertSame('true', $env['TEST2']);

		unset($env['TEST2']);

		static::assertFalse(isset($env['TEST2']));
	}
}
