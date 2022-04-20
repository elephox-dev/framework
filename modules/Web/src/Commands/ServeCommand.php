<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\MemoryEnvironment;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Files\Contract\File;
use Elephox\Logging\Contract\Logger;
use InvalidArgumentException;
use ricardoboss\Console;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

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
			->optional('host', $this->environment['SERVER_HOST'] ?? 'localhost', 'Host to bind to')
			->optional('port', $this->environment['SERVER_PORT'] ?? '8000', 'Port to bind to (>=1024, <=65535)')
			->optional('root', $publicDir->getPath(), 'Root directory to serve from')
			->optional('env', 'development', 'The environment to use (e.g. development, staging or production)')
			->optional('router', dirname(__DIR__, 2) . '/data/router.php', 'The router script to use')
			->optional('workers', 'auto', 'How many threads to use for the PHP server (PHP_CLI_SERVER_WORKERS)')
			->optional('no-reload', false, 'Whether to restart the server upon env file changes')
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$host = $command->getArgument('host')->value;
		$port = $command->getArgument('port')->value;
		$root = $command->getArgument('root')->value;
		$router = $command->getArgument('router')->value;
		$noReload = $command->getArgument('no-reload')->value;

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

		$environment = $this->getEnvironment(dirname($documentRoot), $command);

		$serverCommand = [(new PhpExecutableFinder())->find(false), '-S', "$host:$port", '-t', $documentRoot];
		if ($router) {
			$serverCommand[] = $router;
		}

		$this->logger->info('Starting PHP built-in webserver on ' . Console::link('http://' . $host . ':' . $port), ['command' => implode(' ', $serverCommand)]);

		// initialize timestamps
		$environment->pollDotEnvFileChanged();
		$environment->pollDotEnvFileChanged((string) $environment['APP_ENV']);

		$process = $this->startServerProcess($serverCommand, $documentRoot, $environment);

		$environment->addDotEnvChangeListener(function (?string $envName, bool $local, File $envFile) use (&$process, $serverCommand, $documentRoot, $environment): void {
			$this->logger->warning($envFile->getName() . ' file changed. Restarting server...', ['name' => $envName, 'local' => $local]);

			$environment->loadFromEnvFile($envName);
			$environment->loadFromEnvFile($envName, true);

			/** @var Process $process */
			$process->stop();

			usleep(1000 * 1000);

			$process = $this->startServerProcess($serverCommand, $documentRoot, $environment);
		});

		/** @var Process $process */
		while ($process->isRunning()) {
			if (!$noReload) {
				$environment->pollDotEnvFileChanged();
				$environment->pollDotEnvFileChanged((string) $environment['APP_ENV']);
			}

			usleep(500 * 1000);
		}

		$this->logger->warning(sprintf('Server process exited with code %s', $process->getExitCode() ?? '<unknown>'));

		return 0;
	}

	private function startServerProcess(array $serverCommand, string $documentRoot, Environment $environment): Process
	{
		$process = new Process(
			$serverCommand,
			$documentRoot,
			$environment->asEnumerable()->toArray(),
		);

		/** @psalm-suppress UnusedClosureParam */
		$process->start(function (string $type, string $buffer): void {
			$buffer = trim($buffer);
			foreach (explode("\n", $buffer) as $line) {
				if ($closingBracketPos = strpos($line, ']')) {
					$line = substr($line, $closingBracketPos + 2);
				}

				$this->logger->info($line);
			}
		});

		$this->logger->info('Server process started', ['pid' => $process->getPid()]);

		return $process;
	}

	private function getEnvironment(string $documentRoot, CommandInvocation $command): MemoryEnvironment
	{
		$envName = $command->getArgument('env')->value;
		$workers = $command->getArgument('workers')->value;

		$environment = new MemoryEnvironment($documentRoot);
		$environment->loadFromEnvFile();
		$environment->loadFromEnvFile(local: true);

		if (!is_string($envName)) {
			throw new InvalidArgumentException('Environment must be a string');
		}

		if ($envName !== 'null') {
			$environment['APP_ENV'] = $envName;

			$environment->loadFromEnvFile($envName);
			$environment->loadFromEnvFile($envName, true);
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
