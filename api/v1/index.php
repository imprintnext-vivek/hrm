<?php
/**
 * Author : Tanmaya Patra/India
 * Inkxe-X Microservice Framework
 *
 * PHP version 5.6
 * 
 * @date 01 nov 2019
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, Inkxe Systems Pvt Ltd
 * 
 * @category  Framework
 * @package   Inkxe-X_Microservice_Framework
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   GIT: release100
 * @link      http://inkxe-v10.inkxe.io/xetool/admin ProductsController
 */
require __DIR__ . '/config/constants.php';

// Application blocks if the server has PHP version lower than 7.1
if (!version_compare(PHP_VERSION, '5.6', '>=')) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Warning: Minimum PHP version 5.6 is required. 
        PHP version 7.2.x will suit best';
    exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT) {
case 'development':
    // error_reporting(-1);
    // ini_set('display_errors', 1);
    break;

case 'testing':
case 'production':
    // Surpress all error and warnings
    ini_set('display_errors', 0);
    // error_reporting(
    //     E_ALL 
    //     & ~E_NOTICE 
    //     & ~E_DEPRECATED 
    //     & ~E_STRICT 
    //     & ~E_USER_NOTICE 
    //     & ~E_USER_DEPRECATED
    // );
    break;
default:
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'The application environment is not set correctly.';
    exit(1); // EXIT_ERROR
}
 
// Initialize the config file
require __DIR__ . '/config/bootstrap.php';

// Run app
$app->run();

