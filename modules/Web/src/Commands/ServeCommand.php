<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\MemoryEnvironment;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Logging\Contract\Logger;
use InvalidArgumentException;
use ricardoboss\Console;
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
			->optional('host', 'localhost', 'Host to bind to')
			->optional('port', '8000', 'Port to bind to (>=1024, <=65535)')
			->optional('root', $publicDir->getPath(), 'Root directory to serve from')
			->optional('env', 'development', 'The environment to use (e.g. development, staging or production)')
			->optional('router', dirname(__DIR__, 2) . '/data/router.php', 'The router script to use')
			->optional('workers', 'auto', 'How many threads to use for the PHP server (PHP_CLI_SERVER_WORKERS)')
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$host = $command->getArgument('host')->value;
		$port = $command->getArgument('port')->value;
		$root = $command->getArgument('root')->value;
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

		if (!is_string($router)) {
			throw new InvalidArgumentException('Router must be a string');
		}

		if ($router === 'null') {
			$router = null;
		} else {
			$router = realpath($router);
			if ($router === false) {
				throw new InvalidArgumentException('Given router file does not exist');
			}
		}

		$environment = $this->getEnvironment($command);

		$serverCommand = [PHP_BINARY, '-S', "$host:$port", '-t', $documentRoot];
		if ($router) {
			$serverCommand[] = $router;
		}

		$this->logger->info('Starting PHP built-in webserver on ' . Console::link('http://' . $host . ':' . $port), ['command' => implode(' ', $serverCommand)]);

		$process = proc_open(
			$serverCommand,
			[STDIN],
			$pipes,
			$documentRoot,
			$environment->asEnumerable()->toArray(),
		);

		if ($process === false) {
			throw new RuntimeException('Failed to start server');
		}

		// give the server time to start (0.5s)
		usleep(500000);

		$status = proc_get_status($process);
		if ($status['running'] === false) {
			$this->logger->error('Server exited unexpectedly');

			return -1;
		}

		register_shutdown_function(static fn (): bool => proc_terminate($process));

		$this->logger->info('Server process started', ['pid' => $status['pid']]);

		while ($status = proc_get_status($process)) {
			if (!$status['running']) {
				break;
			}

			sleep(1);
		}

		$this->logger->warning(sprintf('Server process exited with %d', $status['exitcode']));

		return 0;
	}

	private function getEnvironment(CommandInvocation $command): MemoryEnvironment
	{
		$envName = $command->getArgument('env')->value;
		$workers = $command->getArgument('workers')->value;

		$environment = new MemoryEnvironment($this->environment->getRoot()->getPath());
		$environment->loadFromEnvFile();

		if (!is_string($envName)) {
			throw new InvalidArgumentException('Environment must be a string');
		}

		if ($envName !== 'null') {
			$environment['APP_ENV'] = $envName;

			$environment->loadFromEnvFile($envName);
		}

		if (is_string($workers)) {
			if (ctype_digit($workers)) {
				$environment['PHP_CLI_SERVER_WORKERS'] = (int) $workers;
			} elseif ($workers === 'auto') {
				if (PHP_OS_FAMILY === 'Windows') {
					$procCountCommand = 'echo %NUMBER_OF_PROCESSORS%';

					$this->logger->warning('PHP_CLI_SERVER_WORKERS is not supported by PHP on Windows but will be set anyway.');
				} else {
					$procCountCommand = 'nproc';
				}

				exec($procCountCommand, $cores, $code);

				if ($code === 0 && isset($cores[0]) && ctype_digit((string) $cores[0])) {
					$environment['PHP_CLI_SERVER_WORKERS'] = (int) $cores[0];
				} else {
					throw new RuntimeException("Unable to determine number of cores available (used: $procCountCommand)");
				}
			} elseif ($workers !== 'null') {
				throw new InvalidArgumentException('Workers must be a number, "auto" or "null"');
			}
		}

		return $environment;
	}
}
