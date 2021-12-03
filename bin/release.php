<?php
declare(strict_types=1);

if ($argc < 2) {
	echo "Usage: release.php <version>" . PHP_EOL;
	exit(1);
}

function rmdirRecursive(string $dir): void
{
	if (PHP_OS === "WINNT") {
		exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
	} else {
		exec(sprintf("rm -rf %s", escapeshellarg($dir)));
	}
}

function executeEcho(string $commandLine, float|int|string ...$args): bool
{
	return execute(true, $commandLine, ...$args);
}

function executeSilent(string $commandLine, float|int|string ...$args): bool
{
	return execute(false, $commandLine, ...$args);
}

function execute(bool $echo, string $commandLine, float|int|string ...$args): bool
{
	$commandLine = sprintf($commandLine, ...$args);
	echo "$ $commandLine" . PHP_EOL;
	exec($commandLine, $output, $resultCode);

	if ($echo) {
		foreach ($output as $line) {
			echo "< $line" . PHP_EOL;
		}
	}

	if ($resultCode === 0) {
		return true;
	}

	echo "Failed with exit code $resultCode\n";

	return false;
}

function executeGetOutput(string $commandLine, float|int|string ...$args): string
{
	$commandLine = sprintf($commandLine, ...$args);
	echo "$ $commandLine" . PHP_EOL;
	$output = exec($commandLine, result_code: $resultCode);

	if ($resultCode === 0) {
		echo "< $output" . PHP_EOL;

		return $output;
	}

	echo "Failed with exit code $resultCode" . PHP_EOL;

	throw new RuntimeException();
}

$releaseBranch = "main";
$currentBranch = executeGetOutput("git rev-parse --abbrev-ref HEAD");
$version = $argv[1];

// check if we are on the release branch
if ($releaseBranch !== $currentBranch) {
	echo "Release branch ($releaseBranch) does not match the current active branch ($currentBranch)." . PHP_EOL;

	exit(1);
}

// check the given version format
if (!preg_match('/^\d+\.\d+(?:\.\d+)?$/', $version)) {
	echo "Invalid version format. Should be x.x[.x]" . PHP_EOL;

	exit(1);
}

// make sure the working directory is clean
if (!empty(executeGetOutput("git status --porcelain"))) {
	echo "Your working directory is dirty. Did you forget to commit your changes?" . PHP_EOL;

	exit(1);
}

// make sure the release branch is in sync with origin
if (executeGetOutput("git rev-parse HEAD") !== executeGetOutput("git rev-parse origin/%s", $releaseBranch)) {
	echo "Your release branch is not in sync with origin. Did you forget to push your changes?" . PHP_EOL;

	exit(1);
}

if (
	!executeSilent("git tag %s", $version) ||
	!executeSilent("git push origin --tags")
) {
	echo "Failed to tag framework!" . PHP_EOL;

	exit(1);
}

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "elephox-release";
if (!is_dir($tmpDir) && !mkdir($tmpDir) && !is_dir($tmpDir)) {
	throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpDir));
}

echo "Working in $tmpDir" . PHP_EOL . PHP_EOL;

$cwd = getcwd();
foreach ([
	'collection',
	'core',
	'database',
	'di',
	'files',
	'http',
	'logging',
	'support',
	'text',
	'elephox'
] as $remote) {
	echo "============================================================" . PHP_EOL;
	echo "Releasing $remote" . PHP_EOL;

	$currentTmpDir = $tmpDir . DIRECTORY_SEPARATOR . $remote;
	$remoteUrl = "git@github.com:elephox-dev/$remote.git";

	if (is_dir($currentTmpDir)) {
		chdir($currentTmpDir);
	} else {
		if (!mkdir($currentTmpDir) && !is_dir($currentTmpDir)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $currentTmpDir));
		}

		chdir($currentTmpDir);

		if (!executeSilent("git clone %s .", $remoteUrl)) {
			echo "Failed to check out $remote" . PHP_EOL;

			exit(1);
		}
	}

	if (
		!executeSilent("git checkout %s", $releaseBranch) ||
		!executeSilent("git tag %s", $version) ||
		!executeSilent("git push origin --tags")
	) {
		echo "Failed to release $remote" . PHP_EOL;

		exit(1);
	}

	chdir($cwd);
}

echo "Cleaning up..." . PHP_EOL;
rmdirRecursive($tmpDir);
