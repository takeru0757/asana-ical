<?php
/**
 * bootstrap.php
 *
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */

/**
 * Set default timezone.
 */
date_default_timezone_set('UTC');

/**
 * Loading vendor classes.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * PSR-0 autoloader
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 */
spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require __DIR__ . DIRECTORY_SEPARATOR . $fileName;
});
