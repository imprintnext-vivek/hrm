<?php
/**
 * Rendering html dom file on various endpoints
 *
 * PHP version 5.6
 *
 * @category  HtmlDomParser
 * @package   HtmlDomParser
 * @author    Radhanatha Mohapatra <radhanatham@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */
namespace App\Dependencies;

require 'simplehtmldom' . DIRECTORY_SEPARATOR . 'simple_html_dom.php';

/**
 * HtmlDomParser 
 *
 * @category Class
 * @package  HtmlDomParser
 * @author   Radhanatha Mohapatra <radhanatham@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */
class HtmlDomParser
{
    /**
     * GET:Get html dom from file
     *
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return \simplehtmldom\simple_html_dom
     */
    public function fileGetHtml()
    {
        return call_user_func_array('\simplehtmldom\file_get_html', func_get_args());
    }

    /**
     * GET:Get html dom from string
     *
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return \simplehtmldom\simple_html_dom
     */
    public function strGetHtml()
    {
        return call_user_func_array('\simplehtmldom\str_get_html', func_get_args());
    }
}
