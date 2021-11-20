<?php
declare(strict_types=1);

use Elephox\Collection\ArrayList;
use Elephox\Files\Directory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$root = new Directory(dirname(__DIR__));
$rootComposer = $root->getFile('composer.json');
$rootConfig = json_decode($rootComposer->getContents(), true);
$rootRequirements = $rootConfig['require'] ?? [];
$rootReplacements = $rootConfig['replace'] ?? [];

$visitedRootRequirements = array_fill_keys(array_keys($rootRequirements), false);

/** @var ArrayList<Directory>|null $modules */
$modules = $root->getDirectory('modules')?->getDirectories();
if ($modules === null) {
	echo 'No modules found.' . PHP_EOL;

	exit(1);
}

foreach ($modules as $module) {
	$moduleComposer = $module->getFile('composer.json');
	if (!$moduleComposer) {
		echo 'Module ' . $module->getName() . ' has no composer.json file.' . PHP_EOL;

		continue;
	}

	$moduleConfig = json_decode($moduleComposer->getContents(), true);
	$moduleRequirements = $moduleConfig['require'] ?? [];

	// check if the module has any requirements
	if (count($moduleRequirements) === 0) {
		continue;
	}

	// check if the module has any requirements that are not in the root composer.json
	foreach ($moduleRequirements as $moduleRequirement => $moduleVersion) {
		if (array_key_exists($moduleRequirement, $rootRequirements) && $rootRequirements[$moduleRequirement] === $moduleVersion) {
			$visitedRootRequirements[$moduleRequirement] = true;

			continue;
		}

		if (array_key_exists($moduleRequirement, $rootReplacements) && $rootReplacements[$moduleRequirement] === "self.version") {
			continue;
		}

		echo "Module {$module->getName()} has a requirement $moduleRequirement@$moduleVersion that is not in the root composer.json" . PHP_EOL;

		exit(1);
	}
}

$unvisitedRequirements = array_filter($visitedRootRequirements, static function ($visited) {
	return !$visited;
});

if (!empty($unvisitedRequirements)) {
	echo 'Following root requirements were not found in any module:' . PHP_EOL;

	foreach (array_keys($unvisitedRequirements) as $requirement) {
		echo '- ' . $requirement . PHP_EOL;
	}

	exit(1);
}

echo 'All module requirements checked.' . PHP_EOL;

exit(0);
