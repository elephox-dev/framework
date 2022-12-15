<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Json\JsonConfigurationProvider;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Files\File;
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
	private ?File $tmpFile = null;

	public function setUp(): void
	{
		$this->tmpFile = File::temp();
		$this->tmpFile->writeContents(<<<JSON
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
		$this->tmpFile->delete();
	}

	/**
	 * @throws JsonException
	 */
	public function testGetDataFromFile(): void
	{
		$source = new JsonFileConfigurationSource($this->tmpFile);
		$provider = new JsonConfigurationProvider($source);

		static::assertTrue($provider->tryGet('baz:guz', $value));
		static::assertSame('qux', $value);
	}

	/**
	 * @throws JsonException
	 */
	public function testLoadOptionalFile(): void
	{
		$source = new JsonFileConfigurationSource(new File(''), true);

		static::assertSame([], $source->getData());
	}
}
