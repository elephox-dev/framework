<?php
declare(strict_types=1);

namespace Elephox\Web\Commands;

use Elephox\Collection\Enumerable;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Web\Routing\Contract\RouteHandler;
use Elephox\Web\Routing\Contract\Router;
use Psr\Log\LoggerInterface;

class RoutesCommand implements CommandHandler
{
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly Router $router,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder
			->setName('routes')
			->setDescription('List all routes')
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$routes = Enumerable::from($this->router->getRouteHandlers());

		$this->logger->info('Following routes where found:');
		$this->logger->info('<cyan>weight</cyan> <blue>path regex</blue> => <yellow>handler</yellow>');

		foreach ($routes->orderByDescending(fn (RouteHandler $r): int => $r->getSourceAttribute()->getWeight()) as $route) {
			$attribute = $route->getSourceAttribute();

			$this->logger->info(sprintf(
				'<cyan>%s</cyan> <blue>%s</blue> => <yellow>%s</yellow>',
				$attribute->getWeight(),
				$route->getPathRegex(),
				(string) $route,
			));
		}

		return 0;
	}
}
