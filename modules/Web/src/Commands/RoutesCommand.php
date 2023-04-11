<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Collection\Grouping;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Http\RequestMethod;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\Router;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;

readonly class RoutesCommand implements CommandHandler
{
	public function __construct(
		private LoggerInterface $logger,
		private Router $router,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder
			->setName('routes')
			->setDescription('List all routes')
			->addOption('regex', 'r', description: 'Whether to show the generated regular expression')
		;
	}

	public function handle(CommandInvocation $command): ?int
	{
		$this->router->loadRoutes();

		$warnings = [];

		$showRegex = $command->options->get('regex')->bool();

		$routes = $this->router
			->getLoadedRoutes()
			->orderBy(static fn (RouteData $r) => mb_strlen($r->getTemplate()->getSource()))
			->groupBy(static fn (RouteData $r) => $r->getTemplate()->getSource())
			->select(static function (Grouping $g) use (&$warnings, $showRegex): array {
				$path = $g->groupKey();
				$methods = $g->selectMany(static fn (RouteData $r) => $r->getMethods())->unique()->select(self::renderRequestMethod(...))->toList();
				$handlers = $g->select(static fn (RouteData $r) => $r->getHandlerName())->unique()->toList();

				if (count($handlers) > 1) {
					$warnings[] = "Path '$path' has ambiguous handlers: " . implode(', ', $handlers);
				}

				$row = [
					'Methods' => implode(', ', $methods),
					'Path' => self::renderPath($path),
					'Handler' => implode(', ', $handlers),
				];

				if ($showRegex) {
					$row['RegExp'] = implode(' ', $g->select(static fn (RouteData $r) => $r->getRegExp())->unique()->toList());
				}

				return $row;
			})
		;

		foreach (Console::table($routes, compact: true) as $line) {
			$this->logger->info($line);
		}

		if (count($warnings) > 0) {
			foreach ($warnings as $warning) {
				trigger_error($warning, E_USER_WARNING);
			}
		}

		return 0;
	}

	protected static function renderRequestMethod(string $method): string
	{
		return match ($method) {
			RequestMethod::GET->value => Console::green('GET'),
			RequestMethod::PUT->value => Console::blue('PUT'),
			RequestMethod::POST->value => Console::yellow('POST'),
			RequestMethod::DELETE->value => Console::red('DELETE'),
			RequestMethod::HEAD->value => Console::gray('HEAD'),
			RequestMethod::OPTIONS->value => Console::gray('OPTIONS'),
			default => $method,
		};
	}

	protected static function renderPath(string $path): string
	{
		return $path;
	}
}
