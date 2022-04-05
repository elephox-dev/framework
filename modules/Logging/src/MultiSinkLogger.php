<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Collection\ArrayList;
use Elephox\Logging\Contract\LogLevel;
use Stringable;
use Throwable;

class MultiSinkLogger extends AbstractLogger
{
	/** @var ArrayList<Contract\Sink> */
	private readonly ArrayList $sinks;

	public function __construct()
	{
		/** @var ArrayList<Contract\Sink> sinks */
		$this->sinks = new ArrayList();
	}

	public function addSink(Contract\Sink $sink): void
	{
		$this->sinks->add($sink);
	}

	public function log(Throwable|Stringable|string $message, LogLevel $level, array $metaData = []): void
	{
		if ($message instanceof Throwable) {
			$message = $message->getMessage();
			if (!array_key_exists('exception', $metaData)) {
				$metaData['exception'] = $message;
			}
		} else if ($message instanceof Stringable) {
			$message = $message->__toString();
		}

		foreach ($this->sinks as $sink) {
			$sink->write($message, $level, $metaData);
		}
	}
}
