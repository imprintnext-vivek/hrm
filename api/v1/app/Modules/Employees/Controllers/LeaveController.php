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
use App\Modules\Employees\Models\Leave;
use App\Modules\Employees\Models\Leavestatus;
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
class LeaveController extends ParentController
{
    /**
     * GET: Get Leave Record
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   09 dec 2021
     * @return true/false.
     */
    public function getLeaveRecord() {
        $sql = "SELECT `leave_record`.`leave_id`,`leave_record`.`emp_id`,`leave_record`.`leave_apply_date`,`leave_record`.`leave_from`,`leave_record`.`leave_to`,`leave_record`.`days`,`leave_record`.`leave_type`,`leave_record`.`reason`,`leave_record`.`mail_to`,`leave_record`.`contact_no`,`leave_record`.`status`,`leave_status`.`allowed_plan_leave`,`leave_status`.`taken_plan_leave`,`leave_status`.`allowed_casual_leave`,`leave_status`.`taken_plan_leave`,`leave_status`.`taken_casual_leave`,`employee_details`.`emp_name` FROM `leave_record` INNER JOIN `leave_status` ON `leave_record`.`emp_id` = `leave_status`.`emp_id`
        INNER JOIN `employee_details` ON `leave_record`.`emp_id` = `employee_details`.`emp_id`";
        $result = DB::select($sql);
        return json_encode($result,true);
     }
    /**
     * GET:  Leave details display
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   09 dec 2021
     * @return true/false.
     */
    public function updateLeaveData($request){
        $postData = $request->getParsedBody();
        $sql = "SELECT * FROM `leave_record` INNER JOIN `leave_status`  WHERE `leave_record`.`leave_id` =".$postData['id']."  AND `leave_status`.`emp_id` = `leave_record`.`emp_id`";
        $result = DB::select($sql);
        foreach($result as $value){
            $emid = $value->emp_id;
            $reqdays = $value->days;
            $leaveType = $value->leave_type;
            $avPlanleave = $value->allowed_plan_leave - $value->taken_plan_leave;
            $totalPlanLeave = $reqdays + $value->taken_plan_leave;
            $canclePlan = $value->taken_plan_leave - $reqdays;
            $avCasualLeave = $value->allowed_casual_leave - $value->taken_casual_leave;
            $totalCasualLeave = $reqdays + $value->taken_casual_leave; 
            $cancleCasual = $value->taken_casual_leave - $reqdays;
        }
        $leaveRecord = new Leave();
        $leaveStatus = new Leavestatus();
        if ($leaveType == "Plan Leave"){
            if ($avPlanleave > $reqdays){
                $sql = $leaveRecord->select('status')->where('leave_id',$postData['id']);
                $result = $sql->get()->toArray();
                foreach ($result as $value) {
                    $getStatus = $value['status'];
                }
                $status = $postData['status'];
                $message = "";
                if($status == "Approved"){
                    $updateData['status'] = $status;
                    $planLeave = $leaveRecord->where('leave_id',$postData['id'])->update($updateData);
                    if($planLeave){
                        $updatePlan['taken_plan_leave'] = $totalPlanLeave;
                        $leaveStatus->where('emp_id',$emid)->update($updatePlan);
                        $message ="your Plan leave approved";
                    }
                }
                if($status == "Cancelled"){
                    if ($getStatus == "Approved"){
                        $cancle['status'] = $status;
                        $planCancel = $leaveRecord->where('leave_id',$postData['id'])->update($cancle);
                        if($planCancel){
                            $cancleLeave['taken_plan_leave'] = $canclePlan;
                            $leaveStatus->where('emp_id',$emid)->update($cancleLeave);
                            $message = "your Plan leave cancelled";
                        }
                    }else{
                        $reject['status'] = 'Rejected';
                        $leaveRecord->where('leave_id',$postData['id'])->update($reject);
                        $message = "your Plan leave rejected";
                    }
                }
                return json_encode(array($message));
            }
        }else{
            if($avCasualLeave > $reqdays){
                $sql = $leaveRecord->select('status')->where('leave_id',$postData['id']);
                $result = $sql->get()->toArray();
                foreach ($result as  $value) {
                    $getStatus = $value['status'];
                }
                $status = $postData['status'];
                $message = "";
                if($status == "Approved"){
                    $updateData['status'] = $status;
                    $planLeave = $leaveRecord->where('leave_id',$postData['id'])->update($updateData);
                    if($planLeave){
                        $updatePlan['taken_casual_leave'] = $totalCasualLeave;
                        $leaveStatus->where('emp_id',$emid)->update($updatePlan);
                        $message ="your Casual leave approved";
                    }
                }
                if($status == "Cancelled"){
                    if ($getStatus == "Approved"){
                        $cancle['status'] = $status;
                        $CasualCancel = $leaveRecord->where('leave_id',$postData['id'])->update($cancle);
                        if($CasualCancel){
                            $cancleCasualLeave['taken_casual_leave'] = $cancleCasual;
                            $leaveStatus->where('emp_id',$emid)->update($cancleCasualLeave);
                            $message ="your Casual leave Canclled";
                        }
                    }else{
                        $reject['status'] = 'Rejected';
                        $leaveRecord->where('leave_id',$postData['id'])->update($reject);
                        $message = "your Casual leave rejected";
                    }
                }
                return json_encode(array($message));
            }
        }
    }
}
  

    
  
 
    

