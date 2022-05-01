<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Elephox\Cache\Contract\CacheConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Cache\InMemoryCache
 * @covers \Elephox\Cache\InMemoryCacheConfiguration
 * @covers \Elephox\Cache\ImmutableCacheItem
 * @covers \Elephox\Cache\AbstractCacheConfiguration
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Cache\AbstractCache
 *
 * @internal
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

		static::assertFalse($this->cache->hasItem('test'));
		static::assertTrue($this->cache->commit());
		static::assertTrue($this->cache->hasItem('test'));
		static::assertTrue($this->cache->offsetExists('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testDeleteItem(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);

		static::assertTrue($this->cache->hasItem('test'));
		static::assertTrue($this->cache->deleteItem('test'));
		static::assertFalse($this->cache->hasItem('test'));

		$this->cache->save($item);
		unset($this->cache['test']);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testSave(): void
	{
		$item = $this->cache['test'];

		static::assertFalse($this->cache->hasItem('test'));
		static::assertTrue($this->cache->save($item));
		static::assertTrue($this->cache->hasItem('test'));

		$this->cache->deleteItem('test');
		$this->cache[] = $item;
		static::assertTrue($this->cache->hasItem('test'));
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

		static::assertTrue($this->cache->hasItem('test'));
		static::assertTrue($this->cache->hasItem('test2'));
		static::assertTrue($this->cache->hasItem('test3'));
		static::assertTrue($this->cache->deleteItems(['test', 'test2']));
		static::assertFalse($this->cache->hasItem('test'));
		static::assertFalse($this->cache->hasItem('test2'));
		static::assertTrue($this->cache->hasItem('test3'));
		static::assertFalse($this->cache->deleteItem('test'));
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testGetItem(): void
	{
		static::assertFalse($this->cache->hasItem('test'));
		$item = $this->cache->getItem('test');
		static::assertFalse($this->cache->hasItem('test'));
		$item2 = $this->cache->getItem('test');
		static::assertFalse($this->cache->hasItem('test'));
		static::assertNotSame($item, $item2);
		static::assertTrue($this->cache->save($item));
		static::assertTrue($this->cache->hasItem('test'));
		$item3 = $this->cache->getItem('test');
		static::assertSame($item, $item3);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function testClear(): void
	{
		$item = $this->cache->getItem('test');
		$this->cache->save($item);
		static::assertTrue($this->cache->hasItem('test'));
		static::assertTrue($this->cache->clear());
		static::assertFalse($this->cache->hasItem('test'));
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

		static::assertTrue($this->cache->hasItem('test'));
		static::assertTrue($this->cache->hasItem('test2'));
		static::assertTrue($this->cache->hasItem('test3'));
		$items = $this->cache->getItems(['test', 'test2', 'test3']);
		static::assertCount(3, $items);
		foreach ($items as $key => $item) {
			static::assertInstanceOf(ImmutableCacheItem::class, $item);
			static::assertSame($item, ['test' => $item, 'test2' => $item2, 'test3' => $item3][$key]);
		}
	}

	public function testGetConfiguration(): void
	{
		$configuration = $this->cache->getConfiguration();
		static::assertInstanceOf(CacheConfiguration::class, $configuration);
	}
}
