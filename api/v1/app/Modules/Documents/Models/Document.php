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

namespace App\Modules\Documents\Models;

use App\Components\Controllers\Component as ParentController;

/**
 * Fonts
 *
 * @category Fonts
 * @package  Assets
 * @author   Satyabrata <satyabratap@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */

class Document extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'document';
    protected $primaryKey = 'doc_id';
    protected $fillable = ['emp_id','doc_name','doc_url','request_date','date_of_post','status','approved_date'];
}
