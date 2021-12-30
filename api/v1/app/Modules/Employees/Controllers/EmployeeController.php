<?php
/**
 * Manage Fonts
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

namespace App\Modules\Employees\Controllers;

use App\Components\Controllers\Component as ParentController;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\LoginInfo;
use Illuminate\Database\Capsule\Manager as DB;
// use App\Modules\Fonts\Models\FontCategory as Category;
// use App\Modules\Fonts\Models\FontCategoryRelation;
// use App\Modules\Fonts\Models\FontTagRelation;

/**
 * Fonts Controller
 *
 * @category Fonts
 * @package  Assets
 * @author   Satyabrata <satyabratap@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */
class EmployeeController extends ParentController
{
    /**
     * GET: Employee details with pagination
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   08 dec 2021
     * @return All employee List
     */
    public function getEmployeeList($request, $response) {
        $serverStatusCode = 200;
        $jsonResponse = [
            'status' => 1,
            'message' => 'Employee Details found.',
        ];
        $page = $request->getQueryParam('page');
        $perpage = $request->getQueryParam('per_page');
        $order = $request->getQueryParam('order');
        $empList = new Employee();
        $result = $empList->select('*');
        $totalCounts = $result->count();
        if ($totalCounts > 0) {
        $offset = 0;
        $totalItem = empty($perpage) ? PAGINATION_MAX_ROW : $perpage;
        $offset = $totalItem * ($page - 1);
        $result->skip($offset)->take($totalItem);
        $empData = $result->orderBy('emp_name', $order)->get();
        $jsonResponse = [
            'status' => 1,
            'total_records' => $totalCounts,
            'records' => count($empData),
            'data' => $empData,
            
         ];
      }     
        return response(
            $response, ['data' => $jsonResponse,  'status' => $serverStatusCode]
        );        
}
  /**
     * POST: Save employee details
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   08 dec 2021
     * @return true/false
     */
    public function saveEmployeeList($request, $response){
        $serverStatusCode = 200;
        $jsonResponse = [
            'status' => 1,
            'message' => 'Data inserted successfully.',
        ];
        $postData = $request->getParsedBody();
        $profilePic = do_upload('image',ASSET_IMAGE);
        $saveEmpData = [ 
            'type_id' => $postData['typeid'],
            'emp_name' => $postData['ename'],
            'emp_code' => $postData['ecode'],
            'office_email' => $postData['officemail'],
            'department' => $postData['department'],
            'designation' => $postData['designation'],
            'joining_date' => $postData['joindate'],
            'gender' => $postData['gender'],
            'dob' => $postData['dob'],
            'country' => $postData['country'],
            'state' => $postData['state'],
            'city' => $postData['city'],
            'pincode' => $postData['pincode'],
            'current_address' => $postData['caddress'],
            'permanent_address' => $postData['paddress'],
            'qualification' => $postData['qualification'],
            'personal_email' => $postData['pmail'],
            'contact_no' => $postData['phone'],
            'guardian_contact_no' => $postData['pcontact'],
            'pancard_no' => $postData['pan'],
            'aadhar_card' => $postData['aadhar'],
            'driving_license' => $postData['dl'],
            'profile_image' => $profilePic,
            'status' => $postData['status'],
        ];
        $saveData = new Employee();
        $saveData->insert($saveEmpData);
        return response(
            $response, ['data' => $jsonResponse, 'status' => $serverStatusCode]
        );
}
     /**
     * POST: Save employee login Informations
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   08 dec 2021
     * @return true/false
     */
    public function saveLoginData($request, $response) {
      $serverStatusCode =200;
      $jsonResponse = [
          'status' => 1,
          'message' => 'Login information saved.'
      ];
      $postData = $request->getParsedBody();
      $loginData = [];
      $loginData = [
          'emp_id' => $postData['eid'],
          'user_id' => $postData['officemail'],
         'password' => md5($postData['password']),
          'user_role' => $postData['role'],
      ];
      $loginfo = new LoginInfo();
      $loginfo->insert($loginData);
      return response(
          $response, ['data' => $jsonResponse, 'status' => $serverStatusCode]
      );
  }
    /**
     * GET: Delete login Informations
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   08 dec 2021
     * @return true/false
     */
  public function deleteEmployee($response, $args) {
      $serverStatusCode = 200;
      $jsonResponse = [
          'status' => 1,
          'messsage' => 'Deleted successfully.',
      ];
     if(!empty($args)) {
         $deleteEmp = new Employee();
         $employeeId = $args['id'];
         $deleteEmp->where('emp_id', $employeeId)->delete();
        }
      return response(
        $response, ['data' => $jsonResponse, 'status' => $serverStatusCode]
    );
  }
    /**
     * GET: Update employee required Informations
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   09 dec 2021
     * @return true/false
     */
  public function updateEmployeeData($request, $response) {
    $serverStatusCode = 200;
    $jsonResponse = [
        'status' => 1,
        'message' => 'Updated Successfully.',
    ];
    $postData = $request->getParsedBody();
    $profilePic = do_upload('image',ASSET_IMAGE);
    $updateEmployee = [
        'emp_id' => $postData['eid'],
        'type_id' => $postData['typeid'],
        'emp_name' => $postData['ename'],
        'emp_code' => $postData['ecode'],
        'office_email' => $postData['officemail'],
        'department' => $postData['department'],
        'designation' => $postData['designation'],
        'joining_date' => $postData['joindate'],
        'gender' => $postData['gender'],
        'dob' => $postData['dob'],
        'country' => $postData['country'],
        'state' => $postData['state'],
        'city' => $postData['city'],
        'pincode' => $postData['pincode'],
        'current_address' => $postData['caddress'],
        'permanent_address' => $postData['paddress'],
        'qualification' => $postData['qualification'],
        'personal_email' => $postData['pmail'],
        'contact_no' => $postData['phone'],
        'guardian_contact_no' => $postData['pcontact'],
        'pancard_no' => $postData['pan'],
        'aadhar_card' => $postData['aadhar'],
        'driving_license' => $postData['dl'],
        'profile_image' => $profilePic,
        'status' => $postData['status'],
    ];

         $updateEmp = new Employee();
         $updateEmp->where('emp_id', '=', $postData['eid'])
                         ->update($updateEmployee);
         return response(
             $response, ['data'=> $jsonResponse, 'status'=> $serverStatusCode]
         );

}
   /**
     * POST: Validate User/Admin login 
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   09 dec 2021
     * @return true/false
     */
public function validateLogin($request, $response){
      $serverStatCode = 400;
      $errorResponse = [
          'status' => 0,
          'message' => 'Login Failed'
      ];
      $serverStatusCode = 200;
      $jsonResponse = [
          'status' => 1,
          'message' => 'Login Successfull',
      ];
      $postData = $request->getParsedBody();
      $loginCheck = new LoginInfo();
      $result = $loginCheck->select('*')->where(
          [
              'user_id' => $postData['officemail'],
             'password' => md5($postData['password']),
              ]
            );
            if($result->count() == 0){
                return response(
                    $response, ['data' => $errorResponse, 'status' => $serverStatCode]
                );
            }else{
                return response(
                    $response, ['data' => $jsonResponse, 'status' => $serverStatusCode]
                );
            }
        }
    /**
     * GET: Display a single Employee's Informations 
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   09 dec 2021
     * @return true/false
     */
    public function fetchSingle($args) {
    $employeeId = $args['id'];
    $result = "SELECT * FROM `employee_details` INNER JOIN `user_type` on `user_type`.`type_id` = `employee_details`.`type_id`
    WHERE employee_details.emp_id =". $employeeId;
    $userRoleJoin =  DB::select($result);
    if(count($userRoleJoin)) {
        $type_id = $userRoleJoin[0]->type_id;
        $newResult = "SELECT * FROM `user_type` 
        JOIN `user_module_relation` on `user_type`.`type_id` = `user_module_relation`.`type_id` 
        JOIN `user_module` on `user_module`.`module_id` = `user_module_relation`.`module_id` 
        WHERE user_type.type_id =". $type_id;
        $userTypeJoin = DB::select($newResult);
        $moduleData = [];
        if(count($userTypeJoin) > 0) {
            foreach($userTypeJoin  as $value){
                $moduleData['id'] = $value->type_id;
                $moduleData['name'] = $value->module_name;
            }
            $userRoleJoin[0]->modulelist = $moduleData;
            return json_encode($userRoleJoin);
        
       }
    }
}
    /**
     * GET: Update employee's password
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author divya@imprintnext.com
     * @date   10 dec 2021
     * @return true/false
    */
  public function updateLoginData($request, $response) {
      $serverStatusCode = 200;
      $jsonResponse = [
          'status' => 1,
          'message' => 'Updated Successsfully',
      ];
      $postData = $request->getParsedBody();
      $updateLogin = [
        'user_id' => $postData['officemail'],
       'password' => md5($postData['password']),
      ];
      $loginUpdate = new LoginInfo();
      $loginUpdate->where('user_id', '=', $postData['officemail'] )
                       ->update($updateLogin);
           return response(
         $response, ['data' => $jsonResponse, 'status' => $serverStatusCode]
    );
}

}