<?php
/**
 * Category Model
 *
 * PHP version 5.6
 *
 * @category  Component
 * @package   Component
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
namespace App\Components\Models;

/**
 * Asset Type Class
 *
 * @category Component
 * @package  Component
 * @author   Tanmaya Patra <tanmayap@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */
class Employee extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'xe_id';
    protected $table = 'employee_details';
    protected $guarded = ['xe_id'];
    public $timestamps = false;
}
