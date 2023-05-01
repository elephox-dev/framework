<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Elephox\Cache\Contract\Cache;
use Elephox\Cache\Contract\CacheConfiguration;
use Elephox\Files\Directory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

use const APP_ROOT;

/**
 * @covers \Elephox\Cache\InMemoryCache
 * @covers \Elephox\Cache\InMemoryCacheConfiguration
 * @covers \Elephox\Cache\ImmutableCacheItem
 * @covers \Elephox\Cache\AbstractCacheConfiguration
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Cache\AbstractCache
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Cache\TempDirCache
 * @covers \Elephox\Cache\TempDirCacheConfiguration
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\IsEnumerable
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\UniqueByIterator
 *
 * @internal
 */
final class CacheImplementationsTest extends TestCase
{
	public function cacheImplementationProvider(): iterable
	{
		$cacheDir = new Directory(APP_ROOT . '/tmp/cache');

		$argGroups = [
			['cache' => new InMemoryCache(new InMemoryCacheConfiguration())],
			['cache' => new TempDirCache(new TempDirCacheConfiguration(cacheId: 'test', tempDir: $cacheDir))],
		];

		foreach ($argGroups as $args) {
			// make sure cache is cleared before each test
			$args['cache']->clear();

			yield $args;
		}
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testCommit(Cache $cache): void
	{
		$item = $cache->getItem('test');
		$cache->saveDeferred($item);

		self::assertFalse($cache->hasItem('test'));
		self::assertTrue($cache->commit());
		self::assertTrue($cache->hasItem('test'));
		self::assertTrue($cache->offsetExists('test'));

		$cache->clear();
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testDeleteItem(Cache $cache): void
	{
		$item = $cache->getItem('test');
		$cache->save($item);

		self::assertTrue($cache->hasItem('test'));
		self::assertTrue($cache->deleteItem('test'));
		self::assertFalse($cache->hasItem('test'));

		$cache->save($item);
		unset($cache['test']);
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testSave(Cache $cache): void
	{
		$item = $cache['test'];

		self::assertFalse($cache->hasItem('test'));
		self::assertTrue($cache->save($item));
		self::assertTrue($cache->hasItem('test'));

		$cache->deleteItem('test');
		$cache[] = $item;
		self::assertTrue($cache->hasItem('test'));

		$cache->clear();
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testDeleteItems(Cache $cache): void
	{
		$item = $cache->getItem('test');
		$cache->save($item);
		$item2 = $cache->getItem('test2');
		$cache->save($item2);
		$item3 = $cache->getItem('test3');
		$cache->save($item3);

		self::assertTrue($cache->hasItem($item->getKey()));
		self::assertTrue($cache->hasItem($item2->getKey()));
		self::assertTrue($cache->hasItem($item3->getKey()));
		self::assertTrue($cache->deleteItems([$item->getKey(), $item2->getKey()]));
		self::assertFalse($cache->hasItem($item->getKey()));
		self::assertFalse($cache->hasItem($item2->getKey()));
		self::assertTrue($cache->hasItem($item3->getKey()));
		self::assertFalse($cache->deleteItem($item->getKey()));
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testGetItem(Cache $cache): void
	{
		self::assertFalse($cache->hasItem('test'));
		$item = $cache->getItem('test');
		self::assertFalse($cache->hasItem('test'));
		$item2 = $cache->getItem('test');
		self::assertFalse($cache->hasItem('test'));
		self::assertNotSame($item, $item2);
		self::assertTrue($cache->save($item));
		self::assertTrue($cache->hasItem('test'));
		$item3 = $cache->getItem('test');
		self::assertSame($item, $item3);

		$cache->clear();
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testClear(Cache $cache): void
	{
		$item = $cache->getItem('test');
		$cache->save($item);
		self::assertTrue($cache->hasItem('test'));
		self::assertTrue($cache->clear());
		self::assertFalse($cache->hasItem('test'));
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 *
	 * @throws PsrInvalidArgumentException
	 */
	public function testGetItems(Cache $cache): void
	{
		$item = $cache->getItem('test');
		$cache->save($item);
		$item2 = $cache->getItem('test2');
		$cache->save($item2);
		$item3 = $cache->getItem('test3');
		$cache->save($item3);

		self::assertTrue($cache->hasItem('test'));
		self::assertTrue($cache->hasItem('test2'));
		self::assertTrue($cache->hasItem('test3'));
		$items = $cache->getItems(['test', 'test2', 'test3']);
		self::assertCount(3, $items);
		foreach ($items as $key => $item) {
			self::assertInstanceOf(ImmutableCacheItem::class, $item);
			self::assertSame($item, ['test' => $item, 'test2' => $item2, 'test3' => $item3][$key]);
		}
	}

	/**
	 * @dataProvider cacheImplementationProvider
	 *
	 * @param Cache $cache
	 */
	public function testGetConfiguration(Cache $cache): void
	{
		$configuration = $cache->getConfiguration();
		self::assertInstanceOf(CacheConfiguration::class, $configuration);
	}
}
