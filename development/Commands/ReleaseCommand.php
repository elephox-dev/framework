<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Logging\Contract\Logger;
use ricardoboss\Console;

class ReleaseCommand implements CommandHandler
{
	public const VERSION_REGEX = '/^(?<major>\d+)(?:\.(?<minor>\d+)(?:\.(?<patch>\d+))?)?(?:-(?<flag>\w+))?$/';
	public const BASE_BRANCH = 'develop';
	public const RELEASE_BRANCH_PREFIX = 'release/';

	public function __construct(
		private readonly Logger $logger,
	)
	{
	}

	public function configure(CommandTemplateBuilder $builder): CommandTemplateBuilder
	{
		return $builder
			->name('release')
			->description('Release a new version of the framework and its modules.')
			->argument('type', 'The type of release (patch, minor, major)')
			->argument('version', 'The version to release')
			->argument('dry-run', 'Whether to perform a dry run (no changes will be made)', false, false)
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$type = $command->getArgument('type')->value;
		if (!is_string($type)) {
			$this->logger->error('The release type must be a string.');

			return 1;
		}

		if (!in_array($type, ['patch', 'minor', 'major'])) {
			$this->logger->error('Invalid release type: ' . Console::grayBack($type));

			return 1;
		}

		$version = $command->getArgument('version')->value;
		if (!is_string($version)) {
			$this->logger->error('The version must be a string.');

			return 1;
		}

		if (!preg_match(self::VERSION_REGEX, $version, $versionParts)) {
			$this->logger->error('Invalid version: ' . Console::grayBack($version));
			$this->logger->error('The version must be in the format: <major>[.<minor>[.<patch>]]');

			return 1;
		}

		if ($type === 'patch' && !array_key_exists('patch', $versionParts)) {
			$this->logger->error("The patch release type can only be used on patch releases. " . Console::grayBack($version) . " is not a patch release.");

			return 1;
		}

		if ($type === 'minor' && !array_key_exists('minor', $versionParts)) {
			$this->logger->error("The minor release type can only be used on minor releases. " . Console::grayBack($version) . " is not a minor release.");

			return 1;
		}

		$versionParts['major'] = (int) $versionParts['major'];
		$versionParts['minor'] = (int) ($versionParts['minor'] ?? 0);
		$versionParts['patch'] = (int) ($versionParts['patch'] ?? 0);
		$versionParts['flag'] = $versionParts['flag'] ? ('-' . $versionParts['flag']) : '';

		$versionName = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'] . $versionParts['flag'];
		$majorReleaseBaseBranch = self::BASE_BRANCH;
		$minorReleaseBaseBranch = self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'];
		$patchReleaseBaseBranch = self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];
		$previewReleaseBaseBranch = self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'] . '-' . $versionParts['flag'];

		$this->logger->debug('Full version: ' . Console::yellow($versionName));

		$currentBranch = $this->executeGetLastLine('git rev-parse --abbrev-ref HEAD');
		if (($type === 'minor' || $type === 'major') && $currentBranch !== self::BASE_BRANCH) {
			$this->logger->error('You must be on the ' . Console::greenBack(self::BASE_BRANCH) . ' branch to release a major/minor version.');

			return 1;
		}

		if ($type === 'patch' && $currentBranch !== self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor']) {
			$this->logger->error('You must be on the ' . Console::greenBack(self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor']) . ' branch to release a patch version.');

			return 1;
		}

		if (!empty($this->executeGetLastLine('git status --porcelain'))) {
			$this->logger->error('Your working directory is dirty. Please commit or stash your changes.');

			return 1;
		}

		if ($this->executeGetLastLine('git rev-parse HEAD') !== $this->executeGetLastLine('git rev-parse origin/')) {
			$this->logger->error('Your local branch is not up to date with the remote branch. Please pull or push first.');

			return 1;
		}

		if ($this->executeRequireSuccess(
			'The framework modules are not in sync:',
			'composer modules:check --namespaces'
		)) {
			return 1;
		}

		$releaseBranch = self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];

		if (!$this->executeRequireSuccess(
			'Failed to create the release branch: ' . Console::yellow($releaseBranch),
			"git checkout -b %s", $releaseBranch)
		) {
			return 1;
		}

		$this->logger->warning('You are now on the release branch for ' . Console::green($version) . ' (' . Console::yellow($releaseBranch) . ').');
		$this->logger->warning('You can make last-minute adjustments now and commit them.');
		$this->logger->warning('When you are done, press enter and the release will continue.');
		fgets(STDIN);

		$this->logger->info('Releasing ' . Console::greenBack($type) . ' version ' . Console::yellow($versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch']));

		if (
			!$this->executeIsSuccess('git checkout -B %s --track origin/%s', $releaseBranch, $releaseBranch) ||
			!$this->executeIsSuccess('git merge --no-ff --no-edit %s %s', $currentBranch)
		) {
			$this->logger->error('Failed to merge the current branch into the release branch.');

			return 1;
		}

		return 0;
	}

	private function rmdirRecursive(string $dir): void
	{
		if (PHP_OS === "WINNT") {
			exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
		} else {
			exec(sprintf("rm -rf %s", escapeshellarg($dir)));
		}
	}

	/**
	 * @param string $commandLine
	 * @param float|int|string ...$args
	 * @return array{int, list<string>, list<string>}
	 */
	private function execute(string $commandLine, float|int|string ...$args): array
	{
		$commandLine = sprintf($commandLine, ...array_map('escapeshellarg', array_map(static fn ($v) => (string)$v, $args)));
		$this->logger->info(Console::green("$ ") . Console::light_gray($commandLine));

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

	private function executeGetLastLine(string $commandLine, float|int|string ...$args): string
	{
		/**
		 * @var string[] $output
		 */
		[, $output,] = $this->execute($commandLine, ...$args);

		return (string)end($output);
	}

	private function executeRequireSuccess(string $failedMessage, string $commandLine, float|int|string ...$args): bool
	{
		[$resultCode, $output] = $this->execute($commandLine, ...$args);
		if ($resultCode === 0) {
			return true;
		}

		$this->logger->error($failedMessage);
		$this->logger->error(PHP_EOL . implode(PHP_EOL, $output));

		return false;
	}

	private function executeIsSuccess(string $commandLine, float|int|string ...$args): bool
	{
		[$resultCode] = $this->execute($commandLine, ...$args);

		return $resultCode === 0;
	}
}
