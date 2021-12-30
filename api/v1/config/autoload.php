<?php
/**
 * Autoload File
 *
 * PHP version 5.6
 *
 * @category  Autoload
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
$thisDIR = getcwd();
require $thisDIR . '/app/Helpers/helper.php';

/**
 * Registering all other child routes
 */
$modules = include $thisDIR . '/config/modules.php';
$customLoaderDir = $container->get('settings')['custom_loader_directory'];
foreach ($modules as $module => $status) {
    $routeFilePath = "";
    if (isset($status) && !empty($status) > 0) {
        // Include core files
        $routeFilePath = $thisDIR . '/app/Modules/' . $module . '/index.php';
        if (isset($status) && $status['CUSTOM'] === true) {
            // Include custom files
            $routeFilePath = $thisDIR . '/app/' . $customLoaderDir . '/' 
                . $module . '/index.php';
        }
        if (!empty($routeFilePath) && file_exists($routeFilePath)) {
            include $routeFilePath;
        }
    }
}
//End of registration of routes

// Autoload StoreSpaces

