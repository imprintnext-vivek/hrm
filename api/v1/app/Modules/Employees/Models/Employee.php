<?php
/**
 * Fonts Model
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

namespace App\Modules\Employees\Models;

use App\Components\Controllers\Component as ParentController;

/**
 * @author   divya@imprintnext.com
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */

class Employee extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'employee_details'; 
    protected $primaryKey = 'emp_id';
    protected $fillable = ['type_id','emp_name','emp_code','office_email','department','designation','joining_date','gender','dob',
    'country','state','city','pincode','current_address','permanent_address','qualification','personal_email','contact_no','guardian_contact_no','pancard_no','aadhar_card','driving_license','profile_image','status'];
}
