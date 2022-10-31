<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Collection\Enumerable;
use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Contract\File;
use Elephox\Files\Directory;
use Psr\Log\LoggerInterface;

class ReleaseCommand implements CommandHandler
{
	public const VERSION_REGEX = '/^(?<major>\d+)(?:\.(?<minor>\d+)(?:\.(?<patch>\d+))?)?(?<flag>-\w+)?$/';
	public const BASE_BRANCH = 'develop';
	public const RELEASE_BRANCH_PREFIX = 'release/';
	public const DEFAULT_CLONE_ORIGIN_PREFIX = 'https://github.com/elephox-dev/';

	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->setName('release')
			->setDescription('Release a new version of the framework and its modules.')
		;
		$builder->addArgument(
			'type',
			description: 'The type of release (' . implode(', ', Enumerable::from(ReleaseType::cases())->select(static fn (ReleaseType $t) => $t->value)->toArray()) . ')',
			validator: static fn (mixed $value) => is_string($value) &&
			in_array(
				mb_strtolower($value),
				Enumerable::from(ReleaseType::cases())
					->select(static fn (ReleaseType $c) => $c->value)
					->toArray(),
				true,
			)
				? true
				: sprintf(
					'Invalid release type: %s',
					is_string($value) ? $value : get_debug_type($value),
				),
		);
		$builder->addArgument('version', description: 'The version to release');
		$builder->addOption(
			'dry-run',
			description: 'Whether to perform a dry run (no changes will be pushed)',
		);
		$builder->addOption(
			'origin',
			default: self::DEFAULT_CLONE_ORIGIN_PREFIX,
			description: 'The git origin to use for all modules and the framework.',
			validator: static fn (mixed $v) => is_string($v) && str_ends_with($v, '/') ? true
				: 'Origin url must end with /',
		);
		$builder->addOption(
			'modules',
			default: APP_ROOT . '/modules',
			description: 'The directory containing the framework modules',
		);
		$builder->addOption('skip-ci', description: 'Skips running local checks and tests.');
	}

	private function parseVersion(ReleaseType $type, string $version): ?Version
	{
		if (!preg_match(self::VERSION_REGEX, $version, $versionParts)) {
			$this->logger->error("Invalid version: <yellow>$version</yellow>");
			$this->logger->error('The version must be in the format: <major>[.<minor>[.<patch>]]');

			return null;
		}

		if ($type === ReleaseType::Preview && !array_key_exists('flag', $versionParts)) {
			$this->logger->error("The <green>preview</green> release type can only be used on preview releases. <yellow>$version</yellow> is missing a flag (e.g. 1.0<yellowBack>-alpha1</yellowBack>).");

			return null;
		}

		if ($type === ReleaseType::Patch && !array_key_exists('patch', $versionParts)) {
			$this->logger->error("The <green>patch</green> release type can only be used on patch releases. <yellow>$version</yellow> is missing a patch number (e.g. 1.0<yellowBack>.2</yellowBack>).");

			return null;
		}

		if ($type === ReleaseType::Minor && !array_key_exists('minor', $versionParts)) {
			$this->logger->error("The <green>minor</green> release type can only be used on minor releases. <yellow>$version</yellow> is missing a minor number (e.g. 1.<yellowBack>2</yellowBack>).");

			return null;
		}

		return new Version(
			(int) $versionParts['major'],
			(int) $versionParts['minor'],
			(int) $versionParts['patch'],
			$versionParts['flag'],
		);
	}

	private function validateGitStatus(string $currentBranch, string $baseBranch): bool
	{
		if ($currentBranch !== $baseBranch) {
			$this->logger->error("You must be on the <green>$baseBranch</green> branch to release this version.");
			$this->logger->error("You are currently on the <underline>$currentBranch</underline> branch.");

			return false;
		}

		if (!empty($this->executeGetLastLine('git status --porcelain'))) {
			$this->logger->error('Your working directory is dirty. Please commit or stash your changes.');

			return false;
		}

		if ($this->executeGetLastLine('git rev-parse HEAD') !==
			$this->executeGetLastLine("git rev-parse origin/$baseBranch")) {
			$this->logger->error('Your local branch is not up to date with the remote branch. Please pull or push first.');

			return false;
		}

		return true;
	}

	private function prepareRelease(ReleaseType $releaseType, Version $version): bool
	{
		$currentBranch = $this->executeGetLastLine('git rev-parse --abbrev-ref HEAD');

		$targetBranch = self::RELEASE_BRANCH_PREFIX . $version->major . '.' . $version->minor;

		$baseBranch = match ($releaseType) {
			ReleaseType::Major => self::BASE_BRANCH,
			ReleaseType::Minor, ReleaseType::Patch => $targetBranch,
			ReleaseType::Preview => $currentBranch,
		};

		$this->logger->debug("Version name: <yellow>$version->name</yellow>");
		$this->logger->debug("Expected base branch: <green>$baseBranch</green>");
		$this->logger->debug("Target branch: <green>$targetBranch</green>");

		if (!$this->validateGitStatus($currentBranch, $baseBranch)) {
			return false;
		}

		$this->logger->info("Switching to target branch <green>$targetBranch</green>");

		if (!$this->executeRequireSuccess(
			'Target branch does not exist. Creating',
			'git switch ' . $targetBranch,
		)) {
			return $this->executeRequireSuccess(
				'Failed to switch to target branch',
				'git switch -c ' . $targetBranch,
			);
		}

		return true;
	}

	private function shouldSkipDependency(string $dependency): bool
	{
		return !str_starts_with($dependency, 'elephox/') || $dependency === 'elephox/mimey';
	}

	public function handle(CommandInvocation $command): int|null
	{
		$releaseTypeStr = $command->arguments->get('type')->string();
		$releaseType = ReleaseType::tryFrom($releaseTypeStr);
		if ($releaseType === null) {
			return 1;
		}

		$versionStr = $command->arguments->get('version')->string();
		$version = $this->parseVersion($releaseType, $versionStr);
		if ($version === null) {
			return 1;
		}

		$skipCi = $command->options->get('skip-ci')->bool();
		if ($skipCi) {
			$this->logger->warning('Skipping tests and checks');
		} else {
			$this->logger->info('Running tests and checks');

			if (!$this->executeRequireSuccess(
				'Local CI was not successful',
				'composer local-ci',
			)) {
				return 1;
			}
		}

		if (!$this->prepareRelease($releaseType, $version)) {
			return 1;
		}

		$this->logger->info("Updating <yellow>dev-develop</yellow> dependencies to <yellow>$version->name</yellow> in modules");

		$modulesDir = $command->options->get('modules')->string();
		foreach (
			(new Directory($modulesDir))
				->directories()
				->select(fn (DirectoryContract $d) => $d->file('composer.json'))
				->where(fn (File $f) => $f->exists()) as $file
		) {
			$json = $file->contents();

			/** @var array{require: array<string, string>}|false $composer */
			$composer = json_decode($json, true);
			if ($composer === false) {
				$this->logger->error('Unable to decode ' . $file->path() . ' as JSON');

				continue;
			}

			assert(array_key_exists('require', $composer));

			$dependencies = &$composer['require'];

			foreach ($dependencies as $dependency => &$dependencyVersion) {
				if ($this->shouldSkipDependency($dependency)) {
					continue;
				}

				$dependencyVersion = $version->composerDependency;
			}
			unset($dependencyVersion);

			$json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			if ($json === false) {
				$this->logger->error('Unable to decode ' . $file->path() . ' as JSON');

				continue;
			}

			$file->writeContents($json);
		}

		$this->logger->info('Committing changes');

		if (!$this->executeRequireSuccess(
			'Failed to add changed files to commit',
			'git add **/composer.json',
		)) {
			return 1;
		}

		if (!$this->executeRequireSuccess(
			'Failed to create commit',
			"git commit -m \"Pin inter-module dependencies to $version->composerDependency\"",
		)) {
			return 1;
		}

		$this->logger->info("Tagging framework <cyan>$releaseType->value</cyan> version <yellow>$version->name</yellow>");

		if (!$this->executeRequireSuccess(
			'Failed to tag framework',
			"git tag -a $version->name -m \"Release version $version->name\"",
		)) {
			return 1;
		}

		$dryRun = $command->options->get('dry-run')->bool();
		if ($dryRun) {
			$this->logger->warning('Performing a dry run. No changes will be pushed.');
		}

		if (!$dryRun && !$this->executeRequireSuccess(
			'Failed to push to the remote repository',
			'git push --all --force',
		)) {
			return 1;
		}

		if (!$dryRun &&
			!$this->executeRequireSuccess(
				'Failed to push tags to the remote repository',
				'git push --tags',
			)) {
			return 1;
		}

		$this->logger->info('Release successful!');

		return 0;
	}

	/**
	 * @param string[] $args
	 * @param string $commandLine
	 *
	 * @return array{int, list<string>, list<string>}
	 */
	private function execute(string $commandLine, string ...$args): array
	{
		$commandLine = sprintf($commandLine, ...array_map('escapeshellarg', $args));
		$this->logger->debug("<green>$</green> <gray>$commandLine</gray>");

		ob_start();

		exec($commandLine, $output, $resultCode);

		/** @var list<string> $output */
		$error = ob_get_clean();
		if ($error === false) {
			$errors = [];
		} else {
			$errors = explode(PHP_EOL, $error);
		}

		return [$resultCode, $output, $errors];
	}

	private function executeGetLastLine(string $commandLine, string ...$args): string
	{
		/**
		 * @var string[] $output
		 */
		[, $output] = $this->execute($commandLine, ...$args);

		return (string) end($output);
	}

	private function executeRequireSuccess(
		string $failedMessage,
		string $commandLine,
		string ...$args,
	): bool {
		[$resultCode, $output] = $this->execute($commandLine, ...$args);
		if ($resultCode === 0) {
			return true;
		}

		$this->logger->error($failedMessage);
		if (!empty($output)) {
			$this->logger->error(PHP_EOL . implode(PHP_EOL, $output));
		}

		return false;
	}
}
