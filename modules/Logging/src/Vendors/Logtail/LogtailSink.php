<?php
declare(strict_types=1);

namespace Elephox\Logging\Vendors\Logtail;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use JsonException;

class LogtailSink implements Sink
{
	public function __construct(
		private readonly LogtailClient $client,
	) {
	}

	/**
	 * @throws JsonException
	 *
	 * @param LogLevel $level
	 * @param string $message
	 * @param array $context
	 */
	public function write(LogLevel $level, string $message, array $context): void
	{
		$data = [
			'dt' => date(DATE_ATOM),
			'level' => $level->getName(),
			'message' => $message,
		];

		if (!empty($context)) {
			$data['context'] = $context;
		}

		$this->client->send(json_encode($data, JSON_THROW_ON_ERROR));
	}
}
