<?php
/**
 * Setup the environment
 */
date_default_timezone_set('UTC');

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {

    require __DIR__ . '/../vendor/autoload.php';

} else {

    require __DIR__ . '/../src/Autoload.php';
}
