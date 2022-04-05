<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\Logging\Contract\Logger;

class ReleaseCommand implements CommandHandler
{
	public const VERSION_REGEX = '/^(?<major>\d+)(?:\.(?<minor>\d+)(?:\.(?<patch>\d+))?)?(?<flag>-\w+)?$/';
	public const BASE_BRANCH = 'develop';
	public const RELEASE_BRANCH_PREFIX = 'release/';
	public const RELEASE_TYPES = ['major', 'minor', 'patch', 'preview'];
	public const CLONE_ORIGIN_PREFIX = "https://github.com/elephox-dev/";

	public function __construct(
		private readonly Logger $logger,
	)
	{
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder
			->name('release')
			->description('Release a new version of the framework and its modules.')
			->argument('type', 'The type of release (' . implode(', ', self::RELEASE_TYPES) . ')')
			->argument('version', 'The version to release')
			->argument('dry-run', 'Whether to perform a dry run (no changes will be pushed)', false, false)
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$type = $command->getArgument('type')->value;
		if (!in_array($type, self::RELEASE_TYPES, true)) {
			$this->logger->error(sprintf("Invalid release type: <cyan>%s</cyan>", is_string($type) ? $type : get_debug_type($type)));

			return 1;
		}

		$version = $command->getArgument('version')->value;
		if (!is_string($version)) {
			$this->logger->error("The version must be a string.");

			return 1;
		}

		if (!preg_match(self::VERSION_REGEX, $version, $versionParts)) {
			$this->logger->error("Invalid version: <yellow>$version</yellow>");
			$this->logger->error("The version must be in the format: <major>[.<minor>[.<patch>]]");

			return 1;
		}

		if ($type === 'preview' && !array_key_exists('flag', $versionParts)) {
			$this->logger->error("The <green>preview</green> release type can only be used on preview releases. <yellow>$version</yellow> is missing a flag (e.g. 1.0<yellowBack>-alpha1</yellowBack>).");

			return 1;
		}

		if ($type === 'patch' && !array_key_exists('patch', $versionParts)) {
			$this->logger->error("The <green>patch</green> release type can only be used on patch releases. <yellow>$version</yellow> is missing a patch number (e.g. 1.0<yellowBack>.2</yellowBack>).");

			return 1;
		}

		if ($type === 'minor' && !array_key_exists('minor', $versionParts)) {
			$this->logger->error("The <green>minor</green> release type can only be used on minor releases. <yellow>$version</yellow> is missing a minor number (e.g. 1.<yellowBack>2</yellowBack>).");

			return 1;
		}

		$versionParts['major'] = (int) $versionParts['major'];
		$versionParts['minor'] = (int) ($versionParts['minor'] ?? 0);
		$versionParts['patch'] = (int) ($versionParts['patch'] ?? 0);
		$versionParts['flag'] = $versionParts['flag'] ?? '';

		$versionName = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'] . $versionParts['flag'];
		$targetBranch = match ($type) {
			'major', 'minor', 'patch' => self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'],
			'preview' => $versionParts['patch'] === 0 ? self::BASE_BRANCH : self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'],
		};

		$baseBranch = match ($type) {
			'major', 'minor' => self::BASE_BRANCH,
			'patch' => $targetBranch,
			'preview' => $versionParts['patch'] === 0 ? self::BASE_BRANCH : self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'],
		};

		$dryRun = (bool)$command->getArgument('dry-run')->value;
		if ($dryRun) {
			$this->logger->warning("Performing a dry run. No changes will be made.");
		}

		$this->logger->debug("Full version: <yellow>$versionName</yellow>");
		$this->logger->debug("Expected base branch: <green>$baseBranch</green>");

		$currentBranch = $this->executeGetLastLine("git rev-parse --abbrev-ref HEAD");
		if ($currentBranch !== $baseBranch) {
			$this->logger->error("You must be on the <green>$baseBranch</green> branch to release this <yellow>$type</yellow> version.");
			$this->logger->error("You are currently on the <red>$currentBranch</red> branch.");

			return 1;
		}

		if (!empty($this->executeGetLastLine("git status --porcelain"))) {
			$this->logger->error("Your working directory is dirty. Please commit or stash your changes.");

			return 1;
		}

		if ($this->executeGetLastLine("git rev-parse HEAD") !== $this->executeGetLastLine("git rev-parse origin/$baseBranch")) {
			$this->logger->error("Your local branch is not up to date with the remote branch. Please pull or push first.");

			return 1;
		}

		if (!$this->executeRequireSuccess(
			"The framework modules are not in sync:",
			"composer modules:check --namespaces"
		)) {
			return 1;
		}

		$versionReleaseBranch = self::RELEASE_BRANCH_PREFIX . $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];

		if (!$this->executeRequireSuccess(
			"Failed to create the release branch: <green>$versionReleaseBranch</green>",
			"git switch -c %s", $versionReleaseBranch
		)) {
			return 1;
		}

		$this->logger->warning("You are now on the release branch for <yellow>$version</yellow> (<green>$versionReleaseBranch</green>).");
		$this->logger->warning("You can make last-minute adjustments now and commit them.");
		$this->logger->warning("This branch will be merged into the <green>$baseBranch</green> branch and deleted afterwards.");
		$this->logger->warning("When you are done, press enter and the release will continue.");
		fgets(STDIN);

		$this->logger->info("Releasing framework <cyan>$type</cyan> version <yellow>$version</yellow>");

		if (
			!$this->executeIsSuccess('git checkout -B %s', $targetBranch) ||
			!$this->executeIsSuccess('git merge --no-ff --no-edit %s', $versionReleaseBranch)
		) {
			$this->logger->error("Failed to merge the version release branch (<green>$versionReleaseBranch</green>) into the target branch (<green>$targetBranch</green>).");

			return 1;
		}

		if (!$this->executeRequireSuccess(
			"Failed to delete version release branch (<green>$versionReleaseBranch</green>)",
			'git branch -d %s', $versionReleaseBranch
		)) {
			return 1;
		}

		if (!$this->executeRequireSuccess(
			"Failed to tag current release (<yellow>$versionName</yellow>)",
			'git tag -a %s -m %s', $versionName, "Release $versionName",
		)) {
			return 1;
		}

		if (
			!$this->executeIsSuccess('git checkout -B %s', $baseBranch) ||
			!$this->executeIsSuccess('git merge --no-ff --no-edit %s', $targetBranch)
		) {
			$this->logger->error("Failed to back-merge the release commit into the base branch (<green>$baseBranch</green>).");

			return 1;
		}

		$modulesDir = APP_ROOT . "/modules";
		$this->logger->info("Releasing modules...");

		$dirs = scandir($modulesDir);
		if ($dirs === false) {
			$this->logger->error("Failed to scan the modules directory.");

			return 1;
		}

		$tmpDir = APP_ROOT . "/tmp/release/";
		if (!$this->mkdir($tmpDir)) {
			return 1;
		}

		foreach (array_filter($dirs, static function ($dir) use ($modulesDir) {
			return is_dir($modulesDir . DIRECTORY_SEPARATOR . $dir) && $dir[0] !== '.';
		}) as $moduleDir) {
			$moduleName = strtolower(basename($moduleDir));

			$result = $this->releaseModule($tmpDir, $moduleName, $baseBranch, $targetBranch, $versionReleaseBranch, $versionName);
			if ($result !== 0) {
				return $result;
			}
		}

		$this->rmdirRecursive($tmpDir);

		return 0;
	}

	private function releaseModule(string $tmpFolder, string $name, string $baseBranch, string $targetBranch, string $versionReleaseBranch, string $versionName): int
	{
		$this->logger->info("<bold>Releasing module <magenta>$name</magenta></bold>");

		$moduleFolder = $tmpFolder . $name;
		if (!$this->executeRequireSuccess(
			"Failed to clone the module repository",
			"git clone --depth=1 %s %s", self::CLONE_ORIGIN_PREFIX . $name, $moduleFolder
		)) {
			return 1;
		}

		chdir($moduleFolder);

		if (!$this->executeRequireSuccess(
			"Failed to checkout base branch (<green>$baseBranch</green>)",
			"git checkout -B %s", $baseBranch
		)) {
			return 1;
		}

		if (!$this->executeRequireSuccess(
			"Failed to create the release branch: <green>$versionReleaseBranch</green>",
			"git switch -c %s", $versionReleaseBranch
		)) {
			return 1;
		}

		if (
			!$this->executeIsSuccess('git checkout -B %s', $targetBranch) ||
			!$this->executeIsSuccess('git merge --no-ff --no-edit %s', $versionReleaseBranch)
		) {
			$this->logger->error("Failed to merge the version release branch (<green>$versionReleaseBranch</green>) into the target branch (<green>$targetBranch</green>).");

			return 1;
		}

		if (!$this->executeRequireSuccess(
			"Failed to delete version release branch (<green>$versionReleaseBranch</green>)",
			'git branch -d %s', $versionReleaseBranch
		)) {
			return 1;
		}

		if (!$this->executeRequireSuccess(
			"Failed to tag current release (<yellow>$versionName</yellow>)",
			'git tag -a %s -m %s', $versionName, "Release $versionName",
		)) {
			return 1;
		}

		chdir(APP_ROOT);
		$this->rmdirRecursive($moduleFolder);

		return 0;
	}

	private function rmdirRecursive(string $dir): bool
	{
		if (PHP_OS === "WINNT") {
			$this->execute("rd /s /q %s", $dir);
		} else {
			$this->execute("rm -rf %s", $dir);
		}

		return !is_dir($dir);
	}

	private function mkdir(string $dir): bool
	{
		if (is_dir($dir)) {
			return true;
		}

		$this->logger->debug("<green>$</green> <gray>mkdir -p $dir</gray>");
		if (!mkdir($dir, recursive: true) || !is_dir($dir)) {
			$this->logger->error(sprintf('Directory "%s" was not created', $dir));

			return false;
		}

		return true;
	}

	/**
	 * @param string $commandLine
	 * @param float|int|string ...$args
	 * @return array{int, list<string>, list<string>}
	 */
	private function execute(string $commandLine, float|int|string ...$args): array
	{
		$commandLine = sprintf($commandLine, ...array_map('escapeshellarg', array_map(static fn ($v) => (string)$v, $args)));
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
