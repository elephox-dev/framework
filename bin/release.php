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
$developBranch = "develop";
$currentBranch = executeGetOutput("git rev-parse --abbrev-ref HEAD");
$version = $argv[1];

// check if we are on the release branch
if ($developBranch !== $currentBranch) {
	echo "Develop branch ($developBranch) does not match the current active branch ($currentBranch)." . PHP_EOL;

	exit(1);
}

// check the given version format
if (!preg_match('/^\d+\.\d+(?:\.\d+)?$/', $version)) {
	echo "Invalid version format. Should be x.x[.x]" . PHP_EOL;

	exit(1);
}

$versionBranch = "release/$version";

// make sure the working directory is clean
if (!empty(executeGetOutput("git status --porcelain"))) {
	echo "Your working directory is dirty. Did you forget to commit your changes?" . PHP_EOL;

	exit(1);
}

// make sure the release branch is in sync with origin
if (executeGetOutput("git rev-parse HEAD") !== executeGetOutput("git rev-parse origin/%s", $developBranch)) {
	echo "Your release branch is not in sync with origin. Did you forget to push your changes?" . PHP_EOL;

	exit(1);
}

register_shutdown_function(static function () use ($currentBranch) {
	executeSilent("git checkout %s --force", $currentBranch);
});

if (!executeSilent("git checkout -b %s", $versionBranch)) {
	echo "Failed to create $versionBranch branch." . PHP_EOL;

	exit(1);
}

echo sprintf("You are now on the version branch for v%s (%s).", $version, $versionBranch) . PHP_EOL;
echo "Increase version numbers and update changelogs NOW." . PHP_EOL;
echo PHP_EOL;
echo "Press enter to continue." . PHP_EOL;
fgets(STDIN);

echo "Enter a release tag message: ";
$message = trim(fgets(STDIN));

if (
	!executeSilent("git checkout -B %s --track origin/%s", $releaseBranch, $releaseBranch) ||
	!executeSilent("git merge %s --commit --no-ff --quiet -m \"%s\"", $versionBranch, $message)
) {
	echo "Failed to merge $versionBranch branch into $releaseBranch branch." . PHP_EOL;

	exit(1);
}

if (
	!executeSilent("git tag v%s", $version) ||
	!executeSilent("git branch -D %s", $versionBranch)
) {
	echo "Failed to tag framework!" . PHP_EOL;

	exit(1);
}

executeSilent("git checkout %s", $developBranch);
if (!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $releaseBranch, $releaseBranch, $developBranch)) {
	echo "Failed to merge $releaseBranch branch into $developBranch branch." . PHP_EOL;

	exit(1);
}

executeSilent("git push --all");
executeSilent("git push --tags");

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "elephox-release";
if (!is_dir($tmpDir) && !mkdir($tmpDir) && !is_dir($tmpDir)) {
	throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpDir));
}

register_shutdown_function(static function () use ($tmpDir) {
	echo "Cleaning up..." . PHP_EOL;
	rmdirRecursive($tmpDir);
});

echo "Working in $tmpDir" . PHP_EOL . PHP_EOL;

$cwd = getcwd();
foreach ([
	'collection',
	'core',
	'di',
	'events',
	'files',
	'http',
	'logging',
	'oor',
	'stream',
	'support'
] as $remote) {
	echo "============================================================" . PHP_EOL;
	echo "Releasing $remote v$version" . PHP_EOL;

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
		!executeSilent("git checkout -b %s", $versionBranch) ||
		!executeSilent("git checkout -b %s --track origin/%s", $releaseBranch, $releaseBranch) ||
		!executeSilent("git merge %s --commit --no-ff --quiet -m \"%s\"", $versionBranch, $message) ||
		!executeSilent("git tag v%s", $version) ||
		!executeSilent("git branch -D %s", $versionBranch) ||
		!executeSilent("git checkout %s", $developBranch) ||
		!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $releaseBranch, $releaseBranch, $developBranch) ||
		!executeSilent("git push --all") ||
		!executeSilent("git push --tags")
	) {
		echo "Failed to release $remote" . PHP_EOL;

		exit(1);
	}

	chdir($cwd);
}
