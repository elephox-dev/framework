<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Elephox\Cache\Contract\Cache;
use Elephox\Cache\Contract\CacheConfiguration;
use Elephox\Files\Directory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

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
 *
 * @internal
 */
class CacheImplementationsTest extends TestCase
{
	private static function getCacheDir(): Directory
	{
		return new Directory(APP_ROOT . '/tmp/cache');
	}

	public function cacheImplementationProvider(): iterable
	{
		$argGroups = [
			['cache' => new InMemoryCache(new InMemoryCacheConfiguration())],
			['cache' => new TempDirCache(new TempDirCacheConfiguration(cacheId: 'test', tempDir: self::getCacheDir()))],
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

		static::assertFalse($cache->hasItem('test'));
		static::assertTrue($cache->commit());
		static::assertTrue($cache->hasItem('test'));
		static::assertTrue($cache->offsetExists('test'));

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

		static::assertTrue($cache->hasItem('test'));
		static::assertTrue($cache->deleteItem('test'));
		static::assertFalse($cache->hasItem('test'));

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

		static::assertFalse($cache->hasItem('test'));
		static::assertTrue($cache->save($item));
		static::assertTrue($cache->hasItem('test'));

		$cache->deleteItem('test');
		$cache[] = $item;
		static::assertTrue($cache->hasItem('test'));

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

		static::assertTrue($cache->hasItem($item->getKey()));
		static::assertTrue($cache->hasItem($item2->getKey()));
		static::assertTrue($cache->hasItem($item3->getKey()));
		static::assertTrue($cache->deleteItems([$item->getKey(), $item2->getKey()]));
		static::assertFalse($cache->hasItem($item->getKey()));
		static::assertFalse($cache->hasItem($item2->getKey()));
		static::assertTrue($cache->hasItem($item3->getKey()));
		static::assertFalse($cache->deleteItem($item->getKey()));
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
		static::assertFalse($cache->hasItem('test'));
		$item = $cache->getItem('test');
		static::assertFalse($cache->hasItem('test'));
		$item2 = $cache->getItem('test');
		static::assertFalse($cache->hasItem('test'));
		static::assertNotSame($item, $item2);
		static::assertTrue($cache->save($item));
		static::assertTrue($cache->hasItem('test'));
		$item3 = $cache->getItem('test');
		static::assertSame($item, $item3);

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
		static::assertTrue($cache->hasItem('test'));
		static::assertTrue($cache->clear());
		static::assertFalse($cache->hasItem('test'));
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

		static::assertTrue($cache->hasItem('test'));
		static::assertTrue($cache->hasItem('test2'));
		static::assertTrue($cache->hasItem('test3'));
		$items = $cache->getItems(['test', 'test2', 'test3']);
		static::assertCount(3, $items);
		foreach ($items as $key => $item) {
			static::assertInstanceOf(ImmutableCacheItem::class, $item);
			static::assertSame($item, ['test' => $item, 'test2' => $item2, 'test3' => $item3][$key]);
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
		static::assertInstanceOf(CacheConfiguration::class, $configuration);
	}
}
