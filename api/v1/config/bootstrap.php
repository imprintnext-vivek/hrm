<?php
/**
 * Autoload File
 *
 * PHP version 5.6
 *
 * @category  Bootstrap
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */

// Autoload Composer
$thisDIR = getcwd();
$composerVendorPath = $thisDIR.'/vendor/autoload.php';
if (!file_exists($composerVendorPath)) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Warning! (You have not installed vendor inside your project directory)';
    exit(1); // EXIT_ERROR
}
$vendor = require $composerVendorPath;

// Instantiate the app
$settings = include $thisDIR . '/config/settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();
// Autoload components of applciation
require $thisDIR . '/config/autoload.php';

// Autoload Common Routes
require $thisDIR . '/config/routes.php';

// Set default timezone
 ini_set('date.timezone', 'Asia/Kolkata');

// Configure Eloquent Capsule and run the applciation
$dbSettings = $container->get('settings')['db'];
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($dbSettings);
$capsule->bootEloquent();
$capsule->setAsGlobal();
