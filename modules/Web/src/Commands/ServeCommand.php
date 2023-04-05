<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\MemoryEnvironment;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Files\Contract\Directory;
use Elephox\Files\Contract\FileChangedEvent;
use Elephox\Files\File;
use Elephox\Files\FileWatcher;
use Elephox\OOR\Arr;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

readonly class ServeCommand implements CommandHandler
{
	public function __construct(
		private LoggerInterface $logger,
		private Environment $environment,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder
			->setName('serve')
			->setDescription('Starts the PHP built-in webserver for your application')
		;
		$builder->addArgument('host')
			->setDefault($this->environment['SERVER_HOST'] ?? 'localhost')
			->setDescription('Host to bind to')
		;
		$builder->addArgument('port')
			->setDefault($this->environment['SERVER_PORT'] ?? '8000')
			->setDescription('Port to bind to (>=1, <=65535)')
			->setValidator(static fn (mixed $val) => is_string($val) && ctype_digit($val) && $val >= 1 && $val <= 65535 ? true : 'Port must be a number between 1 and 65535')
		;
		$builder->addOption('root', default: null, description: 'Root directory to serve from');
		$builder->addOption('env', default: 'development', description: 'The environment to use (e.g. development, staging or production)');
		$builder->addOption('router', default: null, description: 'The router script to use');
		$builder->addOption('workers', default: 'auto', description: 'How many threads to use for the PHP server (PHP_CLI_SERVER_WORKERS)');
		$builder->addOption('no-reload', description: 'Whether to restart the server upon env file changes');
		$builder->addOption('verbose', 'v', description: 'Whether to print debug output');
	}

	public function handle(CommandInvocation $command): ?int
	{
		$host = $command->arguments->get('host')->string();
		$port = $command->arguments->get('port')->int();
		$root = $command->options->get('root')->nullableDirectory() ?? $this->environment->root()->directory('public');
		$router = $command->options->get('router')->nullableFile() ?? new File(dirname(__DIR__, 2) . '/data/router.php');
		$noReload = $command->options->get('no-reload')->bool();
		$verbose = $command->options->get('verbose')->bool();

		if (!$root->exists()) {
			throw new InvalidArgumentException("Root directory ($root) does not exist");
		}

		if (!$router->exists()) {
			if ($router->path() !== 'null') {
				throw new InvalidArgumentException('Given router file does not exist');
			}

			$router = null;
		}

		$documentRoot = $root->parent();
		$environment = $this->getEnvironment($documentRoot, $command);

		$phpExe = (new PhpExecutableFinder())->find(false);
		$serverCommand = Arr::wrap($phpExe, '-S', "$host:$port", '-t', $documentRoot->path());
		if ($router !== null) {
			$serverCommand[] = $router;
		}

		$this->logger->info('Starting PHP built-in webserver on ' . Console::link('http://' . $host . ':' . $port), ['command' => $serverCommand->implode(' ')]);

		$process = $this->startServerProcess($serverCommand, $documentRoot, $environment, $verbose);

		$onEnvFileChanged = function (FileChangedEvent $fileChangedEvent) use (&$process, $serverCommand, $documentRoot, $environment, $verbose): void {
			$this->logger->warning($fileChangedEvent->file()->name() . ' file changed. Restarting server...');

			$environment->loadFromEnvFile($fileChangedEvent->file());

			/** @var Process $process */
			$process->stop();

			usleep(1_000_000);

			$process = $this->startServerProcess($serverCommand, $documentRoot, $environment, $verbose);
		};

		$fileWatcher = new FileWatcher();
		$fileWatcher->add(
			$onEnvFileChanged,
			$environment->getDotEnvFileName(),
			$environment->getDotEnvFileName(true),
			$environment->getDotEnvFileName(envName: (string) $environment['APP_ENV']),
			$environment->getDotEnvFileName(true, (string) $environment['APP_ENV']),
		);
		$fileWatcher->poll(false);

		/** @var Process $process */
		while ($process->isRunning()) {
			if (!$noReload) {
				$fileWatcher->poll();
			}

			usleep(500_000);
		}

		$this->logger->warning(sprintf('Server process exited with code %s', $process->getExitCode() ?? '<unknown>'));

		return 0;
	}

	private function startServerProcess(Arr $serverCommand, Directory $documentRoot, Environment $environment, bool $verbose): Process
	{
		$process = new Process(
			$serverCommand->getSource(),
			$documentRoot->path(),
			$environment->asEnumerable()->toArray(),
		);

		$process->start(function (string $type, string $buffer) use ($verbose): void {
			$buffer = trim($buffer);
			foreach (explode("\n", $buffer) as $line) {
				$line = preg_replace('/^(?:\[.+?] )?\[.+?] /i', '', $line);

				if (preg_match('/^(?:(?<ip>.+):(?<port>\d{1,5}) (?:(?<action>Accepted|Closing)|\[(?<status>\d{3})]: (?<verb>\S+) (?<path>\S*)(?<message>.*)?)|(?<log>.+))$/i', $line, $matches)) {
					if (isset($matches['log']) && $matches['log'] !== '') {
						// log message
						$this->logger->info($matches['log']);
					} elseif (isset($matches['action']) && $matches['action'] !== '') {
						if ($verbose) {
							// log connection
							$this->logger->debug(sprintf('%s connection at %s:%d', $matches['action'], $matches['ip'], $matches['port']));
						}
					} else {
						// log request
						$this->logger->info(sprintf('%s %s -> %d', $matches['verb'], $matches['path'], $matches['status']), ['ip' => $matches['ip'], 'port' => $matches['port']]);
					}
				} else {
					$this->logger->notice($line);
				}
			}
		});

		$this->logger->info('Server process started', ['pid' => $process->getPid()]);

		return $process;
	}

	private function getEnvironment(Directory $documentRoot, CommandInvocation $command): MemoryEnvironment
	{
		$envName = $command->options->get('env')->string();
		$workers = $command->options->get('workers')->value;

		$environment = new MemoryEnvironment($documentRoot->path());
		$envFile = $environment->getDotEnvFileName();
		$environment->loadFromEnvFile($envFile);
		$localEnvFile = $environment->getDotEnvFileName();
		$environment->loadFromEnvFile($localEnvFile);

		if ($envName !== 'null') {
			$environment['APP_ENV'] = $envName;

			$namedEnvFile = $environment->getDotEnvFileName();
			$environment->loadFromEnvFile($namedEnvFile);
			$localNamedEnvFile = $environment->getDotEnvFileName(true);
			$environment->loadFromEnvFile($localNamedEnvFile);
		}

		if (is_string($workers)) {
			if (ctype_digit($workers)) {
				$environment['PHP_CLI_SERVER_WORKERS'] = (int) $workers;
			} elseif ($workers === 'auto') {
				$environment['PHP_CLI_SERVER_WORKERS'] = $this->getNumberOfCores();
			} elseif ($workers !== 'null') {
				throw new InvalidArgumentException('Workers must be a number, "auto" or "null"');
			}
		}

		return $environment;
	}

	private function getNumberOfCores(): int
	{
		switch (PHP_OS_FAMILY) {
			case 'Windows':
				$procCountCommand = 'echo %NUMBER_OF_PROCESSORS%';

				$this->logger->warning('PHP_CLI_SERVER_WORKERS is not supported by PHP on Windows but will be set to the number of available CPU cores anyway.');

				break;
			case 'Linux':
				$procCountCommand = 'nproc';

				break;
			case 'Solaris':
				$procCountCommand = 'psrinfo -p';

				break;
			case 'BSD':
			case 'Darwin':
				$procCountCommand = 'sysctl -n hw.ncpu';

				break;
			default:
				$this->logger->error("Unable to determine the number of available processor cores (unsupported OS '" . PHP_OS_FAMILY . "'). Defaulting to 2");

				return 2;
		}

		exec($procCountCommand, $cores, $code);

		if ($code === 0 && isset($cores[0]) && ctype_digit((string) $cores[0])) {
			return (int) $cores[0];
		}

		$this->logger->error("Unable to determine the number of available processor cores (tried '$procCountCommand'). Defaulting to 2");

		return 2;
	}
}
