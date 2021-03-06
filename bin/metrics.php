<?php
require_once __DIR__ . '/../vendor/autoload.php';

$filename = 'demo/app1.php';

// path is given as last
$path = isset($argv[$argc - 1]) ? $argv[$argc - 1] : false;
$options = getopt('', array('json', 'extensions::'));
$extensions = isset($options['extensions']) ? $options['extensions'] : 'php';

if(is_dir($path)) {
    $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $directory = new RecursiveDirectoryIterator($path);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.('. $extensions .')$/i', RecursiveRegexIterator::GET_MATCH);
    $files = array();
    foreach($regex as $file) {
        $files[] = $file[0];
    }

} elseif(is_file($path)) {
    $files = array($path);
} else {
    die("PHP Metrics by Jean-François Lépine\nUsage: \n\tphp ".basename(__FILE__)." [--json] [--extensions=\"php|php5|inc|...\"] <directory or filename>\n");
}


// choose formater
$format = isset($options['json']) ? 'json' : 'cli';
$classname = '\Hal\Formater\\'.ucfirst($format);
$formater = new $classname;

foreach($files as $filename) {

    // calculates
    $halstead = new \Hal\Halstead\Halstead(new \Hal\Token\TokenType());
    $rHalstead = $halstead->calculate($filename);

    $loc = new \Hal\Loc\Loc();
    $rLoc = $loc->calculate($filename);

    $maintenability = new \Hal\MaintenabilityIndex\MaintenabilityIndex;
    $rMaintenability = $maintenability->calculate($rHalstead, $rLoc);

    // formats
    $resultSet = new \Hal\Result\ResultSet($filename);
    $resultSet
        ->setLoc($rLoc)
        ->setHalstead($rHalstead)
        ->setMaintenabilityIndex($rMaintenability);

    $formater->pushResult($resultSet);
}
$formater->terminate();




