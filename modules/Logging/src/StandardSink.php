<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Stream\ResourceStream;

use const STDERR;
use const STDOUT;

class StandardSink extends StreamSink
{
	/**
	 * @var resource $stdout
	 */
	private mixed $stdout;

	/**
	 * @var resource $stderr
	 */
	private mixed $stderr;

	public function __construct()
	{
		$stdout = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'wb');
		$stderr = defined('STDERR') ? STDERR : fopen('php://stderr', 'wb');

		assert($stdout !== false, 'Unable to open STDOUT stream');
		assert($stderr !== false, 'Unable to open STDERR stream');

		$this->stdout = $stdout;
		$this->stderr = $stderr;

		$stdoutStream = new ResourceStream($this->stdout, readable: false, writable: true, seekable: false);
		$stderrStream = new ResourceStream($this->stderr, readable: false, writable: true, seekable: false);

		parent::__construct($stdoutStream, $stderrStream);
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return match ($capability) {
			SinkCapability::AnsiFormatting => stream_isatty($this->stdout) && stream_isatty($this->stderr),
			default => parent::hasCapability($capability),
		};
	}
}
