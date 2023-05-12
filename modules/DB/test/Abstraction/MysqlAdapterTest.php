<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Querying\Contract\ResultSetQueryResult;
use Elephox\DB\Querying\QueryParameters;
use Elephox\DB\Querying\QueryStarter;
use mysqli;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @covers \Elephox\DB\Abstraction\MysqlAdapter
 * @covers \Elephox\DB\Abstraction\MysqlConnection
 * @covers \Elephox\DB\Abstraction\MysqlAdapterConfiguration
 * @covers \Elephox\DB\Abstraction\AbstractAdapter
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsArrayEnumerable
 *
 * @internal
 */
final class MysqlAdapterTest extends TestCase
{
	private static string $host = 'localhost';
	private static int $port = 3306;
	private static string $user = 'root';
	private static string $password = 'root';
	private static string $database = 'test';

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		if (!extension_loaded('mysqli')) {
			self::markTestSkipped('mysqli not installed');
		}

		try {
			$db = new mysqli();
			$db->connect(self::$host, self::$user, self::$password, self::$database, self::$port);
			$db->execute_query('CREATE TABLE IF NOT EXISTS users (name TEXT, password TEXT)');
			$db->close();
		} catch (Throwable $t) {
			$tClass = $t::class;
			self::markTestSkipped("mysqli not available ($tClass: {$t->getMessage()})");
		}
	}

	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		$db = new mysqli();
		$db->connect(self::$host, self::$user, self::$password, self::$database, self::$port);
		$db->execute_query('DROP TABLE IF EXISTS users');
		$db->close();
	}

	private function getConnection(): MysqlConnection
	{
		$config = new MysqlAdapterConfiguration(self::$host, self::$port, self::$database, self::$user, self::$password);
		$adapter = new MysqlAdapter($config);
		$connection = $adapter->connect();
		self::assertInstanceOf(MysqlConnection::class, $connection);

		return $connection;
	}

	public function testQueryBuilder(): void
	{
		$connection = $this->getConnection();
		$adapter = $connection->getAdapter();

		$result = (new QueryStarter())
			->select('*')
			->from('users')
			->where('name')
			->equals('username')
			->build()
			->bind(QueryParameters::from(['username' => 'rboss']))
			->run($adapter)
		;

		self::assertInstanceOf(ResultSetQueryResult::class, $result);
		self::assertNotEmpty($result->getResults());
	}
}
