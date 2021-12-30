<?php
/**
 * This Routes holds all the individual route for the EMS
 *
 * PHP version 7.3
 *
 * @category  Fonts
 * @package   Assets
 * @author    Satyabrata <satyabratap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
use App\Modules\Employees\Controllers\EmployeeController;
use App\Modules\Employees\Controllers\LeaveController;

// Instantiate the Container
$container = $app->getContainer();

//Font Routes List
$app->group(
    '/employee', function () use ($app) {
        $app->get('/fetch', EmployeeController::class . ':getEmployeeList');
        $app->post('/insert', EmployeeController::class . ':saveEmployeeList');
        $app->post('', EmployeeController::class . ':saveLoginData');
        $app->get('/delete/{id}', EmployeeController::class . ':deleteEmployee');
        $app->post('/updatemp', EmployeeController::class . ':updateEmployeeData');
        $app->get('/fetch/{id}', EmployeeController::class . ':fetchSingle');
        $app->post('/login', EmployeeController::class . ':validateLogin');
        $app->post('/update', EmployeeController::class . ':updateLoginData');
        $app->get('/leaverecord', LeaveController::class . ':getLeaveRecord');
        $app->post('/updateleave', LeaveController::class . ':updateLeaveData');
    }
    
);


// Categories Routes List
// $app->delete('/categories/fonts/{id}',  FontController::class . ':deleteCategory')
//     ->add(new ValidateJWT($container));
