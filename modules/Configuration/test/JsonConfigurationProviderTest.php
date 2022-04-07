<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Json\JsonConfigurationProvider;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use PHPUnit\Framework\TestCase;
use JsonException;

/**
 * @covers \Elephox\Configuration\Json\JsonFileConfigurationSource
 * @covers \Elephox\Configuration\Json\JsonConfigurationProvider
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\OOR\Filter
 * @covers \Elephox\Configuration\ConfigurationPath
 *
 * @internal
 */
class JsonConfigurationProviderTest extends TestCase
{
	private ?string $tmpFile = null;

	public function setUp(): void
	{
		$this->tmpFile = tempnam(sys_get_temp_dir(), 'ele') . '.json';
		file_put_contents($this->tmpFile, <<<JSON
{
	"foo": "bar",
	"baz": {
		"guz": "qux",
		"nested": {
			"abc": "def",
			"ghi": "jkl"
		}
	}
}
JSON);
	}

	public function tearDown(): void
	{
		unlink($this->tmpFile);
	}

	/**
	 * @throws JsonException
	 */
	public function testGetDataFromFile(): void
	{
		$source = new JsonFileConfigurationSource($this->tmpFile);
		$provider = new JsonConfigurationProvider($source);

		static::assertTrue($provider->tryGet('baz:guz', $value));
		static::assertEquals('qux', $value);
	}

	/**
	 * @throws JsonException
	 */
	public function testLoadOptionalFile(): void
	{
		$source = new JsonFileConfigurationSource('/does/not/exist', true);

		static::assertEquals([], $source->getData());
	}
}
