<?php
/**
 * Carbon JSON to HTML converter
 *
 * Autoloader - to be used when there is no composer around
 *
 * @author  Adam McCann (@AssembledAdam)
 * @license MIT (see LICENSE file)
 */
if (function_exists('spl_autoload_register')) {

    spl_autoload_register(function ($class) {

        $class = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Candybanana\CarbonJsonToHtml\\', '', $class)) . '.php';

        if (file_exists($class)) {
            require $class;
        }
    });
}
