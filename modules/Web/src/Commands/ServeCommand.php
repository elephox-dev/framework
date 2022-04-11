<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Configuration\Contract\Environment;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Logging\Contract\Logger;
use InvalidArgumentException;
use RuntimeException;

class ServeCommand implements CommandHandler
{
	public function __construct(
		private readonly Logger $logger,
		private readonly Environment $environment,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$publicDir = $this->environment->getRoot()->getDirectory('public');

		$builder
			->name('serve')
			->description('Starts the PHP built-in webserver for your application')
			->argument('host', 'Host to bind to', 'localhost', false)
			->argument('port', 'Port to bind to (>=1024, <=65535)', '8000', false)
			->argument('root', 'Root directory to serve from', $publicDir->getPath(), false)
			->argument('env', 'The environment to use (e.g. development, staging or production)', 'development', false)
			->argument('router', 'The router script to use', dirname(__DIR__, 2) . '/data/router.php', false)
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$host = $command->getArgument('host')->value;
		$port = $command->getArgument('port')->value;
		$root = $command->getArgument('root')->value;
		$env = $command->getArgument('env')->value;
		$router = $command->getArgument('router')->value;

		if (!is_string($port) || !ctype_digit($port)) {
			throw new InvalidArgumentException('Port must be a number');
		}

		$port = (int) $port;
		if ($port < 1 || $port > 65535) {
			throw new InvalidArgumentException('Port must be between 1 and 65535');
		}

		if (!is_string($root) || !is_dir($root)) {
			throw new InvalidArgumentException('Root directory (' . ((string) $root) . ') does not exist');
		}

		$documentRoot = realpath($root);
		if (!is_string($documentRoot)) {
			throw new RuntimeException('Unable to resolve document root');
		}

		if (!is_string($host)) {
			throw new InvalidArgumentException('Host must be a string');
		}

		if (!is_string($env)) {
			throw new InvalidArgumentException('Environment must be a string');
		}

		if (!is_string($router)) {
			throw new InvalidArgumentException('Router must be a string');
		}

		$this->logger->info('Starting PHP built-in webserver on ' . $host . ':' . $port);

		$process = proc_open(
			sprintf('%s -S %s:%d %s', escapeshellarg(PHP_BINARY), $host, $port, $router),
			[STDIN],
			$pipes,
			$documentRoot,
			array_merge(getenv(), $_ENV, ['APP_ENV' => $env]),
		);

		if ($process === false) {
			throw new RuntimeException('Failed to start server');
		}

		$status = proc_get_status($process);
		if ($status['running'] === false) {
			$this->logger->error('Server exited unexpectedly');

			return -1;
		}

		register_shutdown_function(static fn (): bool => proc_terminate($process));

		$this->logger->info('Server process started', ['pid' => $status['pid'], 'env' => $env, 'command' => $status['command']]);

		while ($status = proc_get_status($process)) {
			if (!$status['running']) {
				break;
			}

			sleep(1);
		}

		$this->logger->warning(sprintf('Server process exited with %d', $status['exitcode']));

		return 0;
	}
}
