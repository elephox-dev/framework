<?php
declare(strict_types=1);

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;

function gatherSourceFiles(string $dir, array &$results = []): array
{
	$files = scandir($dir);
	if (!$files) {
		return $results;
	}

	foreach ($files as $value) {
		$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		if (is_file($path) && str_ends_with($path, '.php')) {
			$results[] = $path;
		} else if ($value !== "." && $value !== "..") {
			gatherSourceFiles($path, $results);
		}
	}

	return $results;
}

$sourceFiles = gatherSourceFiles($root);
$pattern = '/\N*(TODO|FIXME|MAYBE|IDEA):?\s*(\N*)/i';

$matches = [];
foreach ($sourceFiles as $sourceFile) {
	$contents = file_get_contents($sourceFile);
	preg_match_all($pattern, $contents, $fileMatches);
	if (empty($fileMatches)) {
		continue;
	}

	$matches[$sourceFile] = $fileMatches;
}

var_dump(count($sourceFiles), count($matches));
