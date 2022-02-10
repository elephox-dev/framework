<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Elephox\Cache\Contract\CacheConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Cache\InMemoryCache
 * @covers \Elephox\Cache\InMemoryCacheConfiguration
 * @covers \Elephox\Cache\ImmutableCacheItem
 * @covers \Elephox\Cache\DefaultCacheConfiguration
 * @covers \Elephox\Collection\ArrayMap
 * @uses \Elephox\Cache\Contract\CacheItem
 * @uses \Elephox\Cache\Contract\InMemoryCacheConfiguration
 */
class InMemoryCacheTest extends TestCase
{
	private InMemoryCache $cache;

	public function setUp(): void
	{
		parent::setUp();

		$this->cache = new InMemoryCache(new InMemoryCacheConfiguration());
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testCommit(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->saveDeferred($item);

		self::assertFalse($this->cache->hasItem('test'));
		self::assertTrue($this->cache->commit());
		self::assertTrue($this->cache->hasItem('test'));
		self::assertTrue($this->cache->offsetExists('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testDeleteItem(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);

		self::assertTrue($this->cache->hasItem('test'));
		self::assertTrue($this->cache->deleteItem('test'));
		self::assertFalse($this->cache->hasItem('test'));

		$this->cache->save($item);
		unset($this->cache['test']);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testSave(): void
	{
		$item = $this->cache['test'];

		self::assertFalse($this->cache->hasItem('test'));
		self::assertTrue($this->cache->save($item));
		self::assertTrue($this->cache->hasItem('test'));

		$this->cache->deleteItem('test');
		$this->cache[] = $item;
		self::assertTrue($this->cache->hasItem('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testDeleteItems(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);
		$item2 = $this->cache->getItem('test2');
		$this->cache->save($item2);
		$item3 = $this->cache->getItem('test3');
		$this->cache->save($item3);

		self::assertTrue($this->cache->hasItem('test'));
		self::assertTrue($this->cache->hasItem('test2'));
		self::assertTrue($this->cache->hasItem('test3'));
		self::assertTrue($this->cache->deleteItems(['test', 'test2']));
		self::assertFalse($this->cache->hasItem('test'));
		self::assertFalse($this->cache->hasItem('test2'));
		self::assertTrue($this->cache->hasItem('test3'));
		self::assertFalse($this->cache->deleteItem('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testGetItem(): void
	{
		self::assertFalse($this->cache->hasItem('test'));
		$item = $this->cache->getItem('test');
		self::assertFalse($this->cache->hasItem('test'));
		$item2 = $this->cache->getItem('test');
		self::assertFalse($this->cache->hasItem('test'));
		self::assertNotSame($item, $item2);
		self::assertTrue($this->cache->save($item));
		self::assertTrue($this->cache->hasItem('test'));
		$item3 = $this->cache->getItem('test');
		self::assertSame($item, $item3);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testClear(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);
		self::assertTrue($this->cache->hasItem('test'));
		self::assertTrue($this->cache->clear());
		self::assertFalse($this->cache->hasItem('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testGetItems(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);
		$item2 = $this->cache->getItem('test2');
		$this->cache->save($item2);
		$item3 = $this->cache->getItem('test3');
		$this->cache->save($item3);

		self::assertTrue($this->cache->hasItem('test'));
		self::assertTrue($this->cache->hasItem('test2'));
		self::assertTrue($this->cache->hasItem('test3'));
		$items = $this->cache->getItems(['test', 'test2', 'test3']);
		self::assertCount(3, $items);
		foreach ($items as $key => $item)
		{
			self::assertInstanceOf(ImmutableCacheItem::class, $item);
			self::assertSame($item, ['test' => $item, 'test2' => $item2, 'test3' => $item3][$key]);
		}
	}

	public function testGetConfiguration(): void
	{
		$configuration = $this->cache->getConfiguration();
		self::assertInstanceOf(CacheConfiguration::class, $configuration);
	}
}
