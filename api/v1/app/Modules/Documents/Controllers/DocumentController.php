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

namespace App\Modules\Documents\Controllers;

use App\Components\Controllers\Component as ParentController;
use App\Modules\Documents\Models\Document;
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
class DocumentController extends ParentController
{
    /**
     * GET: Document details
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   08 dec 2021
     * @return get AllCommonDocument List
     */
    
    public function getAllDocuments() {
        $docList = new Document();
        $result = $docList->select('*')->where('emp_id',NULL);
        if ($result->count() > 0) {
            return json_encode($result->get()->toArray());
        }
    }
   /**
     * POST: Set Document details
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   08 dec 2021
     * @return Set AllDocument List
     */
    public function setAllDocuments($request, $response){
        $serverStatusCode =200;
        $nullValue = [
          'status' => 1,
          'message' => 'Document Insert with null value.'
        ];
        $Value = [
            'status' => 1,
            'message' => 'Document Insert with out  null value.'
        ];
        $allFileNames = do_upload('file', ASSET_DOC);
        foreach ($allFileNames as $value) {
            $url = $value;
        }
        $allPostPutValue = $request->getParsedBody();
        $saveDocumentList = [
            'doc_name' => $allPostPutValue['docname'],
            'doc_url' => $url,
            'request_date' => $allPostPutValue['reqdate'],
            'date_of_post' => $allPostPutValue['dateofpost'],
            'status' => $allPostPutValue['status'],
            'approved_date' => $allPostPutValue['approvedate'],
        ];
        $DocumentList = [
            'emp_id' => $allPostPutValue['empid'],
            'doc_name' => $allPostPutValue['docname'],
            'doc_url' => $url,
            'request_date' => $allPostPutValue['reqdate'],
            'date_of_post' => $allPostPutValue['dateofpost'],
            'status' => $allPostPutValue['status'],
            'approved_date' => $allPostPutValue['approvedate'],
        ];
        $saveAllDocument = new Document();
        if($allPostPutValue['empid'] == 0){
            if($saveAllDocument->insert($saveDocumentList)){
                return response(
                   $response, ['data' => $nullValue, 'status' => $serverStatusCode]
                );
            }
        }else{
            if($saveAllDocument->insert($DocumentList)){
                return response(
                    $response, ['data' => $Value, 'status' =>$serverStatusCode]
                );
             }
            }
        }
    /**
     * DELETE: Delete Document 
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   08 dec 2021
     * @return Delete AllDocument 
     */
    public function deleteAllDocument($response, $args){
        $serverStatusCode =200;
        $nullValue = [
          'status' => 1,
          'message' => 'Document Remove Succesfully.'
        ];
        $docId = $args['id'];
        $deleteDocs = new Document();
        $result = $deleteDocs->select('doc_url')
                             ->where('doc_id',$docId);
                             $res = $result->get()->toArray();
        foreach ($res as $value) {
            $filePath = ASSET_DOC.$value['doc_url'];
        } 
        if($result){
            $deleteFile = $deleteDocs->where('doc_id', $docId)->delete(); 
            if($deleteFile){
                unlink($filePath);
                return response(
                    $response, ['data' => $nullValue, 'status' =>$serverStatusCode]
                );
            }
        }
    }
    /**
     * POST: Set Holiday Calender 
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   08 dec 2021
     * @return true/false
     */
    public function setHolidayCalander($request, $response,$args){
        $serverStatusCode =200;
        $csvFilenot = [
          'status' => 1,
          'message' => 'Choose csv file.'
        ];
        $csvFile = [
            'status' => 1,
            'message' => 'Holiday list added..'
          ];
        $year = $args['year'];
        $fileName = basename($_FILES["file"]["name"]);
        $txt = pathinfo($fileName,PATHINFO_EXTENSION);
        $allPostPutValue = $request->getParsedBody();
        if($txt == 'csv'){
            $rename = 'holiday'.$year.'.'.$txt;
            $setHoliday = do_upload('file', ASSET_DOC);
            foreach ($setHoliday as $value) {
                $url = $value;
            }
            $saveHolidayList = [
                'emp_id' => $allPostPutValue['empid'],
                'doc_name' => $rename,
                'doc_url' => $url,
                'request_date' => $allPostPutValue['reqdate'],
                'date_of_post' => $allPostPutValue['dateofpost'],
                'status' => $allPostPutValue['status'],
                'approved_date' => $allPostPutValue['approvedate'],
            ];
            $saveHoliday = new Document();
            if($saveHoliday->insert($saveHolidayList)){
               return response(
                    $response, ['data' => $csvFile, 'status' =>$serverStatusCode]
                );
            }
        }else{
           return response(
                $response, ['data' => $csvFilenot, 'status' =>$serverStatusCode]
            );
        }
    }
    /**
     * GET: Get Holiday Calender Details
     *
     * @param $request  Slim's Request object
     * @param $response Slim's Response object
     *
     * @author sonali@imprintnext.com
     * @date   08 dec 2021
     * @return true/false
     */
    public function getHolidayCalander($response,$args){
        $serverStatusCode =200;
        $csvFilenot = [
          'status' => 1,
          'message' => 'File not found.'
        ];
        $year = $args['year'];
        $docName = 'holiday'.$year.'.csv';
        $getFile = new Document();
        $result =  $getFile->select('doc_url')->where('doc_name',$docName);
        $resultToArray = $result->get()->toArray();
        foreach ($resultToArray as  $value) {
           $url = $value;
        }
        if ($open = fopen(ASSET_DOC.$url['doc_url'], "r")){
            while (! feof($open)){
                $csvs[] = (fgetcsv($open));
                $data = [];
                $columnNames = [];
                foreach ($csvs[0] as $single_csv){
                    $columnNames[] = $single_csv;
                }
                foreach ($csvs as $key => $csv){
                    foreach ($columnNames as $column_key => $columnName){
                        if ($key > 0){
                            $data[$key][$columnName] = $csv[$column_key];
                        }
                    }
                }
            }
            return response(
                $response, ['data' => $data, 'status' =>$serverStatusCode]
            );
        }else{
            return response(
                $response, ['data' => $csvFilenot, 'status' =>$serverStatusCode]
            );
        }
    }
}
