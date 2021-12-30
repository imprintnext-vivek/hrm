<?php
/**
 * Manage Common routes
 *
 * PHP version 5.6
 *
 * @category  Common_Routes
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
use App\Components\Controllers\Component as ParentController;
use App\Middlewares\ValidateJWTToken as ValidateJWT;

/**
 * Common Categories Routes List
 */
// $app->group(
// 	'/categories/{slug}', function () use ($app) {
// 		$app->get('', ParentController::class . ':getCategories');
// 		$app->get('/{id}', ParentController::class . ':getCategories');
// 		$app->post('', ParentController::class . ':saveCategory');
// 		$app->get('/print-profiles/{id}', ParentController::class . ':getCategoryByPrintProfile');
// 		$app->get('/disable/{id}', ParentController::class . ':disableCategory');
// 		$app->post('/sort', ParentController::class . ':sortCategory');
// 		$app->post('/default', ParentController::class . ':setDefault');
// 		$app->post('/reset-default', ParentController::class . ':resetDefault');
// 		// Update Category
// 		$app->post('/{id}', ParentController::class . ':updateCategory');
// 	}
// )->add(new ValidateJWT($container));


//For Email Log
// $app->get('/email-log', ParentController::class . ':getDataForEmailLog')->add(new ValidateJWT($container));
// $app->post('/email-log-clear', ParentController::class . ':clearDataForEmailLog')->add(new ValidateJWT($container));
// $app->get('/email-log-download/{id}', ParentController::class . ':createCsvForEmailLog')->add(new ValidateJWT($container));
