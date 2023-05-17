<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use PHPUnit\Framework\TestCase;
use SQLite3;

/**
 * @covers \Elephox\DB\Abstraction\SqliteAdapter
 * @covers \Elephox\DB\Abstraction\SqliteConnection
 * @covers \Elephox\DB\Abstraction\SqliteAdapterConfiguration
 * @covers \Elephox\DB\Abstraction\AbstractAdapter
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\ArrayList
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 * @uses \Elephox\Collection\IsArrayEnumerable
 *
 * @internal
 */
final class SqliteAdapterTest extends TestCase
{
	private static string $path = __DIR__ . '/../data/test.sqlitedb';

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		if (!extension_loaded('sqlite3')) {
			self::markTestSkipped('sqlite3 not installed');
		}

		$db = new SQLite3(self::$path);
		$db->exec('CREATE TABLE IF NOT EXISTS users (name TEXT, password TEXT)');
	}

	private function getConnection(): SqliteConnection
	{
		$config = new SqliteAdapterConfiguration(self::$path);
		$adapter = new SqliteAdapter($config);
		$connection = $adapter->connect();
		self::assertInstanceOf(SqliteConnection::class, $connection);

		return $connection;
	}
}
