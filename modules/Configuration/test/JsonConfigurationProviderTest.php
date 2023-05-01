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
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Stream\StringStream
 *
 * @internal
 */
final class JsonConfigurationProviderTest extends TestCase
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

		self::assertTrue($provider->tryGet('baz:guz', $value));
		self::assertSame('qux', $value);
	}

	/**
	 * @throws JsonException
	 */
	public function testLoadOptionalFile(): void
	{
		$source = new JsonFileConfigurationSource(new File(''), true);

		self::assertSame([], $source->getData());
	}
}
