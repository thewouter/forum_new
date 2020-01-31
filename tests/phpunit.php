<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use VanillaTests\NullContainer;

// Use consistent timezone for all tests.
date_default_timezone_set("UTC");

error_reporting(E_ALL);
// Alias classes for some limited PHPUnit v5 compatibility with v6.
$classCompatibility = [
    'PHPUnit\\Framework\\TestCase' => 'PHPUnit_Framework_TestCase', // See https://github.com/php-fig/log/pull/52
];
foreach ($classCompatibility as $class => $legacyClass) {
    if (!class_exists($legacyClass) && class_exists($class)) {
        class_alias($class, $legacyClass);
    }
}

// Define some constants to help with testing.
define('APPLICATION', 'Vanilla Tests');
define('PATH_ROOT', realpath(__DIR__.'/..'));
define("PATH_FIXTURES", PATH_ROOT . DIRECTORY_SEPARATOR . "tests" . DIRECTORY_SEPARATOR . "fixtures");

// Copy the cgi-bin files.
$dir = PATH_ROOT.'/cgi-bin';
if (!file_exists($dir)) {
    mkdir($dir);
}

$files = glob(PATH_ROOT."/.circleci/scripts/templates/vanilla/cgi-bin/*.php");
foreach ($files as $file) {
    $dest = $dir.'/'.basename($file);
    $r = copy($file, $dest);
    echo "Copy $file to $dest";
}

// ===========================================================================
// Adding the minimum dependencies to support unit testing for core libraries
// ===========================================================================
require PATH_ROOT.'/environment.php';

// Allow a test before.
$bootstrapTestFile = PATH_CONF . '/bootstrap.tests.php';
if (file_exists($bootstrapTestFile)) {
    require_once $bootstrapTestFile;
}

// This effectively disable the auto instanciation of a new container when calling Gdn::getContainer();
Gdn::setContainer(new NullContainer());

// Clear the test cache.
\Gdn_FileSystem::removeFolder(PATH_ROOT.'/tests/cache');

require_once PATH_LIBRARY_CORE.'/functions.validation.php';

require_once PATH_LIBRARY_CORE.'/functions.render.php';

// Include test utilities.
$utilityFiles = array_merge(
    glob(PATH_ROOT.'/plugins/*/tests/Utils/*.php'),
    glob(PATH_ROOT.'/applications/*/tests/Utils/*.php')
);
foreach ($utilityFiles as $file) {
    require_once $file;
}
