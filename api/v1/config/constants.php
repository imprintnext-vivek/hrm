<?php
/**
 * Global Constants for the Application
 *
 * PHP version 5.6
 *
 * @category  Constants
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
/*
|--------------------------------------------------------------------------
| Switch between : production or development
|--------------------------------------------------------------------------
|
 */
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');

/*
|--------------------------------------------------------------------------
| Base Site URL. No need to change
|--------------------------------------------------------------------------
 */
$domainUrl = (isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$domainUrl .= "://" . $_SERVER['HTTP_HOST'];

// Read XML File
$baseDIR = getcwd();


define("BASE_URL", "https://dev.imprintnext.io/ems/assets/document/");
define("ASSET", $_SERVER['DOCUMENT_ROOT'].'/ems/assets/');
define("ASSET_DOC", ASSET.'document/');
define("ASSET_IMAGE", ASSET.'uploads/');
define("PAGINATION_MAX_ROW", 10);