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
	[$resultCode, $output, $error] = executeGetOutput($commandLine, ...$args);

	if ($echo) {
		foreach (array_filter($output) as $line) {
			echo sprintf("\t> %s%s", trim($line), PHP_EOL);
		}

		foreach (array_filter($error) as $line) {
			echo sprintf("\t! %s%s", trim($line), PHP_EOL);
		}
	}

	if ($resultCode === 0) {
		return true;
	}

	echo "! Failed with exit code $resultCode\n";

	return false;
}

function executeGetOutput(string $commandLine, float|int|string ...$args): array
{
	$commandLine = sprintf($commandLine, ...$args);
	echo "$ $commandLine" . PHP_EOL;

	ob_start();
	exec($commandLine, $output, $resultCode);
	$error = ob_get_clean();

	return [$resultCode, $output, explode(PHP_EOL, $error)];
}

function executeGetLastLine(string $commandLine, float|int|string ...$args): string
{
	[,$output,] = executeGetOutput($commandLine, ...$args);

	return (string)end($output);
}

function error(string ...$lines): void
{
	echo PHP_EOL;
	foreach ($lines as $line) {
		echo sprintf("! %s%s", trim($line), PHP_EOL);
	}
	echo PHP_EOL;
}

$releaseBranch = "main";
$developBranch = "develop";
$currentBranch = executeGetLastLine("git rev-parse --abbrev-ref HEAD");
$version = $argv[1];

// check if we are on the release branch
if ($developBranch !== $currentBranch) {
	error("Develop branch ($developBranch) does not match the current active branch ($currentBranch).");

	exit(1);
}

// check the given version format
if (!preg_match('/^(?<major>\d+)\.(?<minor>\d+)(?:\.(?<patch>\d+))?$/', $version, $matches)) {
	error("Invalid version format. Should be x.x[.x]");

	exit(1);
}

$versionBranch = "release/$version";
$fullVersionString = "v$version";

// make sure the working directory is clean
if (!empty(executeGetLastLine("git status --porcelain"))) {
	error("Your working directory is dirty. Did you forget to commit your changes?");

	exit(1);
}

// make sure the release branch is in sync with origin
if (executeGetLastLine("git rev-parse HEAD") !== executeGetLastLine("git rev-parse origin/%s", $developBranch)) {
	error("Your release branch is not in sync with origin. Did you forget to push your changes?");

	exit(1);
}

// TODO: check if version requirements are in sync with the version being released
if (!executeEcho("composer module:check --namespaces")) {
	error("Make sure all dependencies are in sync.");

	exit(1);
}

executeEcho("gh run list --workflow ci.yml --branch (git rev-parse --abbrev-ref HEAD) --limit 3");
echo PHP_EOL;
echo "Make sure the last CI run was successful. (Press enter to continue)" . PHP_EOL;
fgets(STDIN);

register_shutdown_function(static function () use ($currentBranch) {
	executeSilent("git checkout %s --force", $currentBranch);
});

if (!executeSilent("git checkout -b %s", $versionBranch)) {
	error("Failed to create $versionBranch branch.");

	exit(1);
}

echo PHP_EOL;
echo sprintf("You are now on the version branch for %s (%s).", $fullVersionString, $versionBranch) . PHP_EOL;
echo sprintf("In case this is a minor release, remember to update all module requirements to this version: ^%s.%s", $matches['major'], $matches['minor']) . PHP_EOL;
echo PHP_EOL;
echo "Press enter to continue with the release." . PHP_EOL;
fgets(STDIN);
echo PHP_EOL;

if (
	!executeSilent("git checkout -B %s --track origin/%s", $releaseBranch, $releaseBranch) ||
	!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $versionBranch, $versionBranch, $releaseBranch)
) {
	error("Failed to merge '$versionBranch' branch into '$releaseBranch' branch.");

	exit(1);
}

if (
	!executeSilent("git tag %s", $fullVersionString) ||
	!executeSilent("git branch -D %s", $versionBranch)
) {
	error("Failed to tag framework!");

	exit(1);
}

executeSilent("git checkout %s", $developBranch);
if (!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $releaseBranch, $releaseBranch, $developBranch)) {
	error("Failed to merge $releaseBranch branch into $developBranch branch.");

	exit(1);
}

executeSilent("git push --all");
executeSilent("git push --tags");
executeEcho("gh release create %s --generate-notes --title %s --target %s --draft", $fullVersionString, $fullVersionString, $releaseBranch);

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "elephox-release";
if (!is_dir($tmpDir) && !mkdir($tmpDir) && !is_dir($tmpDir)) {
	error('Directory "%s" was not created', $tmpDir);

	exit(1);
}

register_shutdown_function(static function () use ($tmpDir) {
	echo PHP_EOL;
	echo "============================================================" . PHP_EOL;
	echo "Cleaning up..." . PHP_EOL;
	rmdirRecursive($tmpDir);
});

echo PHP_EOL;
echo "============================================================" . PHP_EOL;
echo PHP_EOL;
echo "Working in $tmpDir" . PHP_EOL;

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
	echo PHP_EOL;
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
			error("Failed to check out $remote");

			exit(1);
		}
	}

	if (
		!executeSilent("git checkout -b %s", $versionBranch) ||
		!executeSilent("git checkout -b %s --track origin/%s", $releaseBranch, $releaseBranch) ||
		!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $versionBranch, $versionBranch, $releaseBranch) ||
		!executeSilent("git tag %s", $fullVersionString) ||
		!executeSilent("git branch -D %s", $versionBranch) ||
		!executeSilent("git checkout %s", $developBranch) ||
		!executeSilent("git merge %s --commit --no-ff --quiet -m \"Merge '%s' into '%s'\"", $releaseBranch, $releaseBranch, $developBranch) ||
		!executeSilent("git push --all") ||
		!executeSilent("git push --tags")
	) {
		error("Failed to release $remote");

		exit(1);
	}

	chdir($cwd);
}
