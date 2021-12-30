<?php
/**
 * This Routes holds all the individual route for the Fonts
 *
 * PHP version 5.6
 *
 * @category  Fonts
 * @package   Assets
 * @author    Satyabrata <satyabratap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
use App\Modules\Documents\Controllers\DocumentController;

// Instantiate the Container
$container = $app->getContainer();

//Font Routes List
$app->group(
    '/docs', function () use ($app) {
        $app->get('', DocumentController::class . ':getAllDocuments');
        $app->post('/insert', DocumentController::class . ':setAllDocuments');
        $app->delete('/delete/{id}', DocumentController::class . ':deleteAllDocument');
        $app->post('/setholiday/{year}', DocumentController::class . ':setHolidayCalander');
        $app->get('/getholiday/{year}', DocumentController::class . ':getHolidayCalander');
    }
);
// Categories Routes List
// $app->delete('/categories/fonts/{id}',  FontController::class . ':deleteCategory')
//     ->add(new ValidateJWT($container));
