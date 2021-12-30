<?php
/**
 * Global Configuration for the Application
 *
 * PHP version 5.6
 *
 * @category  GLobal_Configurations
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
$thisDIR = getcwd();
$databaseSettings = include $thisDIR . '/config/database.php';
return [
    'settings' => [
        // set to false in production
        'displayErrorDetails' => true,
        // Allow the web server to send the content-length header
        'addContentLengthHeader' => false,
        'db' => $databaseSettings,
        'pagination' => [
            // Default value for per page item showing
            'per_page' => 40,
        ],
        // Enable or Disable JWT Authentication
        'do_load_jwt' => false,
        'jwt_secret' => "SgUkXp2s5v8y/B?E(H+MbQeThWmYq3t6w9z^C&F)J@NcRfUjXn2r4u7x!A%D*G-K",
        'show_exception' => true,
        'custom_loader_directory' => 'Custom',
        'jwtRoutesCheck' => [
            
        ],
    ],
];
