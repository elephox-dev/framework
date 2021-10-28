<?php

namespace Philly\Base\Collection;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Philly\Base\Collection\Map
 * @covers \Philly\Base\Support\SplObjectIdHashGenerator
 */
class MapTest extends TestCase
{
    public function testPutAndGet(): void
    {
        /**
         * @var Map<string, mixed> $map
         * @noinspection PhpRedundantVariableDocTypeInspection
         */
        $map = new Map();

        $map->put('testKey', 'testValue');
        $map->put('anotherKey', 'anotherValue');

        self::assertEquals('testValue', $map->get('testKey'));
        self::assertEquals('anotherValue', $map->get('anotherKey'));
    }

    public function testInitialize(): void
    {
        $map = new Map(['test' => 'val', 123 => '134']);

        self::assertEquals('val', $map->get('test'));
    }

    public function testObjectKey(): void
    {
        /**
         * @var Map<stdClass, mixed> $map
         * @noinspection PhpRedundantVariableDocTypeInspection
         */
        $map = new Map();

        $key = new stdClass();
        $map->put($key, "test");

        self::assertEquals("test", $map->get($key));
    }

    public function testInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /**
         * @var Map<float, mixed> $map
         * @noinspection PhpRedundantVariableDocTypeInspection
         * @psalm-suppress InvalidTemplateParam
         */
        $map = new Map();

        $map->put(123.542, "test");
    }
}
