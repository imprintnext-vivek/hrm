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

class Leavestatus extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'leave_status'; 
    protected $primaryKey = 'status_id';
    protected $fillable = ['emp_id','allowed_plan_leave','allowed_casual_leave','taken_plan_leave','taken_casual_leave'];
}
