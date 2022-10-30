<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Psr\Log\LoggerInterface;

class ReleaseCommand implements CommandHandler
{
	public const VERSION_REGEX = '/^(?<major>\d+)(?:\.(?<minor>\d+)(?:\.(?<patch>\d+))?)?(?<flag>-\w+)?$/';
	public const BASE_BRANCH = 'develop';
	public const RELEASE_BRANCH_PREFIX = 'release/';
	public const RELEASE_TYPES = ['major', 'minor', 'patch', 'preview'];
	public const DEFAULT_CLONE_ORIGIN_PREFIX = 'https://github.com/elephox-dev/';

	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->setName('release')->setDescription('Release a new version of the framework and its modules.');
		$builder->addArgument('type', description: 'The type of release (' . implode(', ', self::RELEASE_TYPES) . ')', validator: static fn (mixed $value) => in_array($value, self::RELEASE_TYPES, true) ? true : sprintf('Invalid release type: %s', is_string($value) ? $value : get_debug_type($value)));
		$builder->addArgument('version', description: 'The version to release');
		$builder->addOption('dry-run', description: 'Whether to perform a dry run (no changes will be pushed)');
		$builder->addOption('origin', default: self::DEFAULT_CLONE_ORIGIN_PREFIX, description: 'The git origin to use for all modules and the framework.', validator: static fn (mixed $v) => is_string($v) && str_ends_with($v, '/') ? true : 'Origin url must end with /');
	}

	private function extractVersionParts(string $type, string $version): ?array
	{
		if (!preg_match(self::VERSION_REGEX, $version, $versionParts)) {
			$this->logger->error("Invalid version: <yellow>$version</yellow>");
			$this->logger->error('The version must be in the format: <major>[.<minor>[.<patch>]]');

			return null;
		}

		if ($type === 'preview' && !array_key_exists('flag', $versionParts)) {
			$this->logger->error("The <green>preview</green> release type can only be used on preview releases. <yellow>$version</yellow> is missing a flag (e.g. 1.0<yellowBack>-alpha1</yellowBack>).");

			return null;
		}

		if ($type === 'patch' && !array_key_exists('patch', $versionParts)) {
			$this->logger->error("The <green>patch</green> release type can only be used on patch releases. <yellow>$version</yellow> is missing a patch number (e.g. 1.0<yellowBack>.2</yellowBack>).");

			return null;
		}

		if ($type === 'minor' && !array_key_exists('minor', $versionParts)) {
			$this->logger->error("The <green>minor</green> release type can only be used on minor releases. <yellow>$version</yellow> is missing a minor number (e.g. 1.<yellowBack>2</yellowBack>).");

			return null;
		}

		$versionParts['major'] = (int) $versionParts['major'];
		$versionParts['minor'] = (int) ($versionParts['minor'] ?? 0);
		$versionParts['patch'] = (int) ($versionParts['patch'] ?? 0);
		$versionParts['flag'] ??= '';

		return $versionParts;
	}

	private function validateGitStatus(string $baseBranch, string $releaseType): bool
	{
		$currentBranch = $this->executeGetLastLine('git rev-parse --abbrev-ref HEAD');
		if ($currentBranch !== $baseBranch) {
			$this->logger->error("You must be on the <green>$baseBranch</green> branch to release this <yellow>$releaseType</yellow> version.");
			$this->logger->error("You are currently on the <underline>$currentBranch</underline> branch.");

			return false;
		}

		if (!empty($this->executeGetLastLine('git status --porcelain'))) {
			$this->logger->error('Your working directory is dirty. Please commit or stash your changes.');

			return false;
		}

		if ($this->executeGetLastLine('git rev-parse HEAD') !== $this->executeGetLastLine("git rev-parse origin/$baseBranch")) {
			$this->logger->error('Your local branch is not up to date with the remote branch. Please pull or push first.');

			return false;
		}

		return true;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$releaseType = $command->arguments->get('type')->string();
		$version = $command->arguments->get('version')->string();
		$versionParts = $this->extractVersionParts($releaseType, $version);
		if ($versionParts === null) {
			return 1;
		}

		/**
		 * @var int $major
		 * @var int $minor
		 * @var int $patch
		 * @var string $flag
		 */
		['major' => $major, 'minor' => $minor, 'patch' => $patch, 'flag' => $flag] = $versionParts;

		$versionName = $major . '.' . $minor . '.' . $patch . $flag;
		$targetBranch = match ($releaseType) {
			'major', 'minor', 'patch' => self::RELEASE_BRANCH_PREFIX . $major . '.' . $minor,
			'preview' => $patch === 0 ? self::BASE_BRANCH : self::RELEASE_BRANCH_PREFIX . $major . '.' . $minor,
		};

		$baseBranch = match ($releaseType) {
			'major' => self::BASE_BRANCH,
			'minor' => self::RELEASE_BRANCH_PREFIX . $major . '.' . $minor,
			'patch' => $targetBranch,
			'preview' => $patch === 0 ? self::BASE_BRANCH : self::RELEASE_BRANCH_PREFIX . $major . '.' . $minor,
		};

		$dryRun = $command->options->get('dry-run')->bool();
		if ($dryRun) {
			$this->logger->warning('Performing a dry run. No changes will be pushed.');
		}

		$this->logger->debug("Full version: <yellow>$versionName</yellow>");
		$this->logger->debug("Expected base branch: <green>$baseBranch</green>");

		if (!$this->validateGitStatus($baseBranch, $releaseType)) {
			return 1;
		}

		if (!$this->executeRequireSuccess(
			'Local CI was not successful',
			'composer local-ci',
		)) {
			return 1;
		}

		$this->logger->info("Tagging framework <cyan>$releaseType</cyan> version <yellow>$version</yellow>");

		if (!$dryRun && !$this->executeRequireSuccess(
			'Failed to push to the remote repository',
			'git push --all --force',
		)) {
			return 1;
		}

		if (!$dryRun && !$this->executeRequireSuccess(
			'Failed to push tags to the remote repository',
			'git push --tags',
		)) {
			return 1;
		}

		$this->logger->info('Release successful!');

		return 0;
	}

	/**
	 * @return array{int, list<string>, list<string>}
	 *
	 * @param string[] $args
	 * @param string $commandLine
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

	private function executeRequireSuccess(string $failedMessage, string $commandLine, string ...$args): bool
	{
		[$resultCode, $output] = $this->execute($commandLine, ...$args);
		if ($resultCode === 0) {
			return true;
		}

		$this->logger->error($failedMessage);
		$this->logger->error(PHP_EOL . implode(PHP_EOL, $output));

		return false;
	}
}
