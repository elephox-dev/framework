<?php
declare(strict_types=1);

if ($argc < 2) {
	echo "Usage: release.php <version>\n";
	exit(1);
}

$releaseBranch = "main";
$currentBranch = trim(shell_exec("git rev-parse --abbrev-ref HEAD"));
$version = $argv[1];

// check if we are on the release branch
if ($releaseBranch !== $currentBranch) {
	echo "Release branch ($releaseBranch) does not match the current active branch ($currentBranch).\n";

	exit(1);
}

// check the given version format
if (!preg_match('/^\d+\.\d+(?:\.\d+)?$/', $version)) {
	echo "Invalid version format. Should be x.x[.x]\n";

	exit(1);
}

// make sure version starts with v
$version = "v$version";

// make sure the working directory is clean
if (trim(shell_exec("git status --porcelain")) !== "") {
	echo "Your working directory is dirty. Did you forget to commit your changes?\n";

	exit(1);
}

// make sure the release branch is in sync with origin
if (trim(shell_exec("git rev-parse HEAD")) !== trim(shell_exec("git rev-parse origin/$releaseBranch"))) {
	echo "Your release branch is not in sync with origin. Did you forget to push your changes?\n";

	exit(1);
}

shell_exec("git tag $version");
shell_exec("git push origin --tags");

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "elephox-release";
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
	echo "Releasing $remote\n";

	unlink($tmpDir);
	if (!mkdir($tmpDir) && !is_dir($tmpDir)) {
		throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpDir));
	}

	chdir($tmpDir);

	$remoteUrl = "git@github.com:elephox-dev/$remote.git";

	if (
		!shell_exec("git clone $remoteUrl .") ||
		!shell_exec("git checkout $releaseBranch") ||
		!shell_exec("git tag $version") ||
		!shell_exec("git push origin --tags")
	) {
		echo "Failed to release $remote\n";

		exit(1);
	}

	chdir($cwd);
}
