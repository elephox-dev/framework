<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\UploadedFileMap as UploadedFileMapContract;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UploadedFileMap
 * @covers \Elephox\Http\UploadedFile
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Files\File
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Http\UploadError
 * @covers \Elephox\Support\CustomMimeType
 * @covers \Elephox\Files\AbstractFilesystemNode
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class UploadedFileMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$tmp = tempnam(sys_get_temp_dir(), 'tmp');
		fclose(fopen($tmp, 'wb+'));
		$fname = pathinfo($tmp, PATHINFO_BASENAME);

		$map = UploadedFileMap::fromGlobals([
			'test' => [
				'name' => 'test.txt',
				'type' => 'text/plain',
				'size' => 12,
				'tmp_name' => $fname,
				'full_path' => $tmp,
				'error' => UPLOAD_ERR_OK,
			],
			'custom-mime' => [
				'name' => 'strange.abc',
				'type' => 'application/x-abc',
				'size' => 1,
				'tmp_name' => $fname,
				'full_path' => $tmp,
				'error' => UPLOAD_ERR_OK,
			],
			'unknown-filesize' => [
				'name' => 'empty.txt',
				'type' => 'text/plain',
				'size' => -1,
				'tmp_name' => $fname,
				'full_path' => $tmp,
				'error' => UPLOAD_ERR_OK,
			],
		]);

		static::assertInstanceOf(UploadedFileMapContract::class, $map);
		static::assertCount(3, $map);
		static::assertInstanceOf(UploadedFile::class, $map->get('test'));
		static::assertInstanceOf(UploadedFile::class, $map->get('custom-mime'));
		static::assertInstanceOf(UploadedFile::class, $map->get('unknown-filesize'));

		unlink($tmp);
	}
}
