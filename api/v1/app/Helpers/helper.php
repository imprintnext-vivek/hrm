<?php
/**
 * Inkxe File Helpers
 *
 * PHP version 5.6
 *
 * @category                Helper
 * @package                 Helper
 * @author                  Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright               2019-2020 Riaxe Systems
 * @license                 http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link                    http://inkxe-v10.inkxe.io/xetool/admin
 * @SuppressWarnings(PHPMD)
 */
use App\Components\Controllers\Component as ParentController;
use App\Dependencies\BarcodeGeneratorPNG as BarcodeGeneratorPNG;
use App\Dependencies\Upload as Upload;
use Dompdf\Dompdf;
use Dompdf\Options;
use Intervention\Image\ImageManagerStatic as ImageManager;
use PHPMailer\PHPMailer as XEMailer;

/**
 * Check is a provided email is valid or not, if not valid then this function try to
 * convert it to a valid string. or else send false if can not convert to  a valid
 * string
 *
 * @param $email Input email address
 *
 * @author tanmayap@riaxe.com
 * @date   11 Feb 2020
 * @return boolean or string
 */
function valid_email($email) {
	$cleanEmailAddress = trim(preg_replace('/\s+/', ' ', $email));
	$cleanEmailAddress = filter_var($cleanEmailAddress, FILTER_SANITIZE_EMAIL);
	if (filter_var($cleanEmailAddress, FILTER_VALIDATE_EMAIL)) {
		return strtolower($cleanEmailAddress);
	}
	return false;
}

/**
 * Call directly a API with Guzzle HTTP Client
 *
 * @param $endPoint  Api Endpoint
 * @param $method    The Method name
 * @param $getParams Parameter list
 *
 * @author tanmayap@riaxe.com
 * @date   31 Jan 2020
 * @return json response wheather data is deleted or not
 */
function call_api($endPoint, $method, $getParams) {
	$responseData = [];
	$requestMethod = trim($method);
	$requestMethod = strtoupper($requestMethod);
	$allowedMethods = ['GET', 'POST', 'DELETE', 'HEAD', 'OPTIONS', 'PATCH', 'PUT'];
	$requestURI = BASE_URL . $endPoint;
	$requestURI = preg_replace('/([^:])(\/{2,})/', '$1/', $requestURI);
	$headers = server_request_headers();
	// Set Headers with valid parameters
	$clientHeader = [
		'Content-Type' => 'application/json',
	];
	if (!empty($headers['TOKEN'])) {
		$clientHeader += [
			'token' => trim($headers['TOKEN']),
		];
	}
	// Initialize Guzzle Client
	$guzzle = new \GuzzleHttp\Client(
		['headers' => $clientHeader]
	);
	// Hit requested API and get response from the API
	if (!empty($endPoint) && in_array($requestMethod, $allowedMethods)) {
		if (!empty($getParams) && count($getParams) > 0) {
			$getApiResponse = $guzzle->request(
				$requestMethod,
				$requestURI,
				['form_params' => $getParams]
			);
		} elseif (empty($getParams) || count($getParams) == 0) {
			$getApiResponse = $guzzle->request(
				$requestMethod,
				$requestURI
			);
		}
		$responseBody = $getApiResponse->getBody();
		if ($getApiResponse->getStatusCode() == 200) {
			$getProductDetailsJson = $responseBody->getContents();
			$responseData = json_clean_decode($getProductDetailsJson, true);
		}
	}

	return $responseData;
}
/**
 * Call directly a API with CURL process
 * Not tested properly
 *
 * @param $post   Parameters required for Post/Put
 * @param $url    The URL which will be hit (Full URL)
 * @param $method Method Name
 * @param $json   Return type
 * @param $ssl    Is secure call
 *
 * @author tanmayap@riaxe.com
 * @date   16 Mar 2020
 * @return json response wheather data is deleted or not
 */
function call_curl($post = [], $url, $method = 'GET', $json = false, $ssl = false) {
	$token = '';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, BASE_URL . $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

	// Set Headers with valid parameters
	$headers = server_request_headers();
	if (!empty($headers['TOKEN'])) {
		$token = trim($headers['TOKEN']);
	}

	if ($method == 'POST') {
		curl_setopt($ch, CURLOPT_POST, 1);
	}
	if ($json == true) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt(
			$ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json', 'token:' . $token, 'Content-Length: ' . strlen($post))
		);
		//echo $token;exit;
	} else {
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSLVERSION, 6);
	if ($ssl == false) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	// curl_setopt($ch, CURLOPT_HEADER, 0);
	//print_r($ch);exit;
	$curlResponse = curl_exec($ch);
	if (curl_error($ch)) {
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_error($ch);
		print_r('Error: ' . $err . ' Status: ' . $statusCode);
		// Add error
	}
	curl_close($ch);
	return json_clean_decode($curlResponse, true);
}

/**
 * Server Request header for all Server types
 *
 * @author tanmayap@riaxe.com
 * @date   13 Aug 2019
 * @return json response wheather data is deleted or not
 */
function server_request_headers() {
	$arrayOfHeader = array();
	$rxHttp = '/\AHTTP_/';
	foreach ($_SERVER as $key => $server) {
		if (preg_match($rxHttp, $key)) {
			$arrayOfHeaderKey = preg_replace($rxHttp, '', $key);
			$rxMatches = array();
			$rxMatches = explode('_', $arrayOfHeaderKey);
			if (count($rxMatches) > 0 and strlen($arrayOfHeaderKey) > 2) {
				foreach ($rxMatches as $akKey => $akVal) {
					$rxMatches[$akKey] = ucfirst($akVal);
				}

				$arrayOfHeaderKey = implode('-', $rxMatches);
			}
			$arrayOfHeader[$arrayOfHeaderKey] = $server;
		}
	}
	return ($arrayOfHeader);
}

/**
 * Check if a Array is valid or not
 *
 * @param $data the data for processing
 *
 * @author tanmayap@riaxe.com
 * @date   17 sept 2019
 * @return boolean
 */
function is_valid_array($data) {
	if (is_object($data)) {
		$data = $data->toArray();
	}
	if (!is_object($data)) {
		if (!empty($data) && is_array($data) && count($data)) {
			return true;
		}
	}

	return false;
}

/**
 * Check if a Array is valid or not. For checking variable just send $data, For
 * checking Integer send $data, 'int', 'int'
 *
 * @param $data   the data for processing
 * @param $type   string or integer
 * @param $return If it returns boolean or integer type value
 *
 * @author tanmayap@riaxe.com
 * @date   17 sept 2019
 * @return boolean
 */
function is_valid_var($data, $type = 'var', $return = 'bool') {
	if ($type == 'int') {
		if (!empty($data) && $data > 0) {
			return $return == 'int' ? 1 : true;
		}
	} else if ($type == 'var') {
		if (!empty($data) && $data != '') {
			return $return == 'int' ? 1 : true;
		}
	}

	return $return == 'int' ? 0 : false;
}

/**
 * Check if Image Magick enabled or not
 *
 * @param $module Module name to check
 *
 * @author tanmayap@riaxe.com
 * @date   17 sept 2019
 * @return boolean
 */
function is_installed($module) {
	if (!empty($module)) {
		$loadedExts = get_loaded_extensions();
		if (in_array($module, $loadedExts)) {
			return true;
		}
	}
	return false;
}

/**
 * Convert a numeric value to a valid decimal number
 *
 * @param $decimal      Decimal
 * @param $decimalpoint Decimal Point
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Decimal formated number
 */
function to_decimal($decimal = 0, $decimalpoint = 2) {
	if (!empty($decimal) && $decimal > 0) {
		return number_format($decimal, $decimalpoint);
	}
	return 0;
}

/**
 * Convert any value to integer value
 *
 * @param $data data, which will be converted into int
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return integer or boolean
 */
function to_int($data) {
	if (!empty($data)) {
		return intval($data);
	}
	return 0;
}
/**
 * Check if a slug is in a valid format or not
 *
 * @param $slug Slim's Request object
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return true or false
 */
function validate_slug($slug) {
	if (!empty($slug) && preg_match('/^[a-z][-a-z0-9]*$/', $slug)) {
		return true;
	}
	return false;
}
/**
 * Fetch each key from app's config/settings.php file
 *
 * @param $settingKey Setting's Array key from settings.php file
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Array value corresponding to the Array key of settings file
 */
function get_app_settings($settingKey) {
	if (!empty($settingKey)) {
		$setting = include RELATIVE_PATH . '/config/settings.php';
		return $setting['settings'][$settingKey];
	}
	return false;
}

/**
 * Send Email with Preset HTML Formats
 * Parameter's Array format :-
 * $emailFormat=['from'=>['email'=>'tanmayap@riaxe.com','name'=>'Tanmaya
 * Riaxe'],'recipients'=>['to'=>['email'=>'tanmayapatra09@gmail.com','name'=>'Tanmaya
 * Personal 1'],'reply_to'=>['email'=>'tanmaya4u12@gmail.com','name'=>'Tanmaya
 * Personal 2'],'cc'=>['email'=>'tanmayasmtpdev@gmail.com','name'=>'Tanmaya
 * Personal 3'],'bcc'=>['email'=>'satyabratap@riaxe.com','name'=>'Satyabrata
 * Riaxe'],],'attachments'=>['','',],'subject'=>'This is a test mail with a test
 * subject','body'=>'This is a test mail with a test body',];
 *
 * @param $params Slim's Request object
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Email Response
 */
function email($params = []) {
	// Email functionality starts here
	$mailResponse = [];
	$mail = new XEMailer\PHPMailer(true);
	try {
		//Server settings
		$smptData = $params['smptData'];
		/*This section for development testing*/
		// if (!empty($smptData['smtp_debug']) && $smptData['smtp_debug'] == 1) {
		//     $mail->SMTPDebug = XEMailer\SMTP::DEBUG_SERVER;
		// }
		/*This section for development testing*/

		$mail->isSMTP();
		$mail->Host = $smptData['smtp_host'];
		if (!empty($smptData['smtp_auth']) && $smptData['smtp_auth'] == 1) {
			$mail->SMTPAuth = true;
		} else {
			$mail->SMTPAuth = false;
		}

		$mail->Username = $smptData['smtp_user'];
		$mail->Password = $smptData['smtp_pass'];
		$mail->SMTPSecure = XEMailer\PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port = $smptData['smtp_port'];

		// From Email settings
		if (!empty($params['from']) && count($params['from']) > 0) {
			$mail->setFrom($params['from']['email'], $params['from']['name']);
		}

		// Recipients Setup
		if (!empty($params['recipients']) && count($params['recipients']) > 0) {
			if (!empty($params['recipients']['to']['email'])) {
				$mail->addAddress(
					$params['recipients']['to']['email'],
					$params['recipients']['to']['name']
				);
			}
			// Add a Reply To

			if (!empty($params['recipients']['reply_to']['email'])) {
				$mail->addReplyTo(
					$params['recipients']['reply_to']['email'],
					$params['recipients']['reply_to']['name']
				);
			}
			// Add a CC

			if (!empty($params['recipients']['cc']['email'])) {
				$mail->addCC(
					$params['recipients']['cc']['email'],
					$params['recipients']['cc']['name']
				);
			}
			// Add a BCC

			if (!empty($params['recipients']['bcc']['email'])) {
				$mail->addBCC(
					$params['recipients']['bcc']['email'],
					$params['recipients']['bcc']['name']
				);
			}
			// Add a recipient
		}

		// Attachments linking
		if (!empty($params['attachments']) && count($params['attachments']) > 0) {
			foreach ($params['attachments'] as $attachment) {
				if (!empty($attachment)) {
					$mail->addAttachment($attachment);
				}

			}
		}

		// Content
		$mail->isHTML(true); // Set email format to HTML
		if (!empty($params['subject']) && $params['subject'] != "") {
			$mail->Subject = $params['subject'];
		}

		if (!empty($params['body']) && $params['body'] != "") {
			$mail->Body = $params['body'];
		}

		$mail->send();
		$mailResponse = [
			'status' => 1,
			'message' => 'Email sent successfully',
		];
	} catch (XEMailer\Exception $e) {
		$mailResponse = [
			'status' => 0,
			'message' => "Message could not be sent. Mailer Error:
                {$mail->ErrorInfo}",
		];
	}
	return $mailResponse;
}

/**
 * Get the status for Show Exception
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Store ID in Array Format
 */
function show_exception() {
	$getExceptionStatus = get_app_settings('show_exception');
	return $getExceptionStatus;
}
/**
 * A Custom json_decode function which will remove extra comments and decode to
 * array
 *
 * @param $json    Json Code
 * @param $assoc   If return Associated Array or not
 * @param $depth   User specified recursion depth
 * @param $options Bitmask of JSON_BIGINT_AS_STRING, JSON_INVALID_UTF8_IGNORE,
 *                 JSON_INVALID_UTF8_SUBSTITUTE, JSON_OBJECT_AS_ARRAY,
 *                 JSON_THROW_ON_ERROR.
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Array
 */
function json_clean_decode($json, $assoc = true) {
	$assoc = (empty($assoc) || $assoc == null) ? false : $assoc;
	// search and remove comments like /* */ and //
	$json = preg_replace(
		"#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#",
		'', $json
	);
	$json = json_decode($json, $assoc);
	return $json;
}
/**
 * A Custom json_encode
 *
 * @param $array an Array
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Array
 */
function json_clean_encode($array) {
	if (!empty($array)) {
		return json_encode($array);
	}
	return false;
}
/**
 * Craete Logs according to provided information create_log('file', 'info',
 * ['message' => 'This is a test file Delete info', 'extra' => ['module' =>
 * 'Template', 'file_name' => '213353459890.png', 'directory' =>
 * 'assets/template']]);
 *
 * @param $type     By this name it craeted log file
 * @param $flagType DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT,
 *                  EMERGENCY
 * @param $logData  Data those will be logged into the file
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return boolean
 */
function create_log($type = 'activity', $flagType = 'info', $logData) {
	if (!empty($type) && $type != "" && !empty($logData)) {
		$logFileName = $type . '_logs.json';
		$logger = new \Monolog\Logger('inkxe_logger');
		$formatter = new \Monolog\Formatter\JsonFormatter();
		$fileHandler = new \Monolog\Handler\StreamHandler(RELATIVE_PATH . '/logs/' . $logFileName);
		$fileHandler->setFormatter($formatter);
		$logger->pushHandler($fileHandler);
		if ($logger->{$flagType}($logData['message'], $logData['extra'])) {
			return true;
		}
	}
	return false;
}
/**
 * Get the Log file contents and use it
 *
 * @param $type Specify log file prefixed name
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Store ID in Array Format
 */
function read_logs($type = 'activity') {
	$logFileName = 'logs.json';
	if (!empty($type) && $type != "") {
		$logFileName = $type . '_' . 'logs.json';
	}
	$logPath = RELATIVE_PATH . '/logs/' . $logFileName;
	$getLogJson = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	return $getLogJson;
}
/**
 * Get dynamic read/write path for modules
 *
 * @param $mode   Read or Write
 * @param $module Module's Slug name
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return A Valid Read or Write URL
 */
function path($mode, $module) {
	$moduleFolder = strtoupper($module) . "_FOLDER";
	// If requested Directory not present then, Create that Directory
	$checkDirectory = ASSETS_PATH_W . constant($moduleFolder);
	if (!file_exists($checkDirectory)) {
		create_directory($checkDirectory);
	}
	if ($mode === 'abs') {
		return ASSETS_PATH_W . constant($moduleFolder);
	} elseif ($mode === 'read') {
		return ASSETS_PATH_R . constant($moduleFolder);
	}
}
/**
 * Send Json formatted data with Headers and Origins
 *
 * @param $response    Slim's Response object
 * @param $apiResponse Response Data
 *
 * @author tanmayap@riaxe.com
 * @date   09 sep 2019
 * @return Slim's Json Formatted Json Response
 */
function response($response, $apiResponse) {
	if (!empty($apiResponse)) {
		return $response->withJson(
			$apiResponse['data'],
			$apiResponse['status'],
			JSON_NUMERIC_CHECK
		);
	}

	return $response->withJson([], 200);
}

/**
 * Get Store ID by manupulating POST, PUT and Databse
 *
 * @param $request Slim's Request object
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Store ID in Array Format
 */
function get_store_details($request) {
	$parent = new ParentController();
	$storeDetails = [
		'store_id' => 1,
	];
	$getClientSideData = "";
	$method = $request->getMethod();
	if (isset($method) && $method == 'POST') {
		$getClientSideData = $request->getParsedBody();
	} elseif (isset($method) && $method == 'PUT') {
		$getClientSideData = $parent->parsePut();
	} elseif (isset($method) && $method == 'GET') {
		// Get store ID from Query String
		$getClientSideData = [
			'store_id' => to_int(filter_input(INPUT_GET, 'store_id')),
		];
	}
	if (!empty($getClientSideData['store_id'])) {
		// Store id exist in request data
		$storeDetails = [
			'store_id' => $getClientSideData['store_id'],
		];
	} else {
		$getStoreTableDetails = $parent->getActiveStoreDetails();
		if (!empty($getStoreTableDetails['store_id'])) {
			$storeDetails = [
				'store_id' => $getStoreTableDetails['store_id'],
			];
		}
	}
	// Store Id return as an Array format, because in controller during where
	// condition, you dont need to specify store_id again
	return $storeDetails;
}

/**
 * Dynamically messages according to Module Name
 *
 * @param $moduleName Module Name
 * @param $type       Error Type
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Message in string format
 */
function message($moduleName, $type) {
	$returnMessage = '{message} not set correctly. Please check config';
	if (!empty($moduleName)) {
		$messages = include RELATIVE_PATH . '/config/message.php';
		// dynamic set the string according to the provided module name
		if (!empty($messages[$type])) {
			$returnMessage = str_replace('[MODULE]', $moduleName, $messages[$type]);
		}
	}
	return $returnMessage;
}
/**
 * Convert the stdClass() object Array to Normal Associative array
 *
 * @param $arrayObj StdObject Array
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Array
 */
function object_to_array($arrayObj) {
	if (is_object($arrayObj)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$arrayObj = get_object_vars($arrayObj);
	}

	if (is_array($arrayObj)) {
		/*
			         * Return array converted to object
			         * Using __FUNCTION__ (Magic constant)
			         * for recursive call
		*/
		return array_map(__FUNCTION__, $arrayObj);
	} else {
		// Return array
		return $arrayObj;
	}
}

/**
 * Sometimes while printing an array we need to write print_r and pre tags to
 * display it in a readable format. So by using this method, this method will
 * help you to print the array in a better readable format
 *
 * @param $array Slim's Request object
 * @param $abort Abort flag true or false
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return A readable array
 */
function debug($array, $abort = true) {
	echo '<pre>';
	print_r($array);
	echo '</pre>';
	if ($abort === true) {
		die("<br>-- End of Debug --");
	}
}

/**
 * Get random numbers based on timestamp
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return A random timestamp based number
 */
function getRandom() {
	$randomNumber = date('Ymdhis') . rand(99, 9999);
	return $randomNumber;
}

/*
|--------------------------------------------------------------------------
| Directory and Files related Helpers
|--------------------------------------------------------------------------
|
 */
/**
 * Delete a Directory and it's contents
 *
 * @param $dir Directory name
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return boolean
 */
function delete_directory($dir) {
	if (!file_exists($dir)) {
		return true;
	}
	// If the requested path is a file, then delete that file with dedicated function
	if (is_file($dir)) {
		return delete_file($dir);
	}

	foreach (scandir($dir) as $item) {
		if ($item == '.' || $item == '..') {
			continue;
		}

		if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
			return false;
		}

	}

	// return rmdir($dir);
	return true;
}

/**
 * File upload for single or multiple files. If you give multiple dimensions
 * then first dim will be treated as thumb and others as normal images with
 * different resolutions
 *
 * @param $fileKey File uploading key name
 * @param $dir     Directory Location for Save Files
 * @param $res     Resolutions of child Images for generation
 * @param $return  Returning format. Either array or String. For single file
 *                 upload it will be string and for multiple file uploading it
 *                 will be array
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Array or String
 */
function do_upload($fileKey, $dir, $resolutions = [], $return = 'array') {
	$uploadFileNames = [];
	$errorLogs = [];
	if (!empty($fileKey) && !empty($_FILES)) {
		if (!file_exists($dir)) {
			create_directory($dir);
		}
		$uploadInit = new Upload($fileKey);
		$uploadInit->moveUploadedTo = $dir;
		$uploadInit->newFileName = getRandom();
		$uploadInit->securityScan = false;
		$uploadResult = $uploadInit->upload();
		// Get the uploaded file's data.
		$uploadedFiles = $uploadInit->getUploadedData();
		if ($uploadResult === true) {
			foreach ($uploadedFiles as $uploadedFile) {
				$uploadFileNames[] = $uploadedFile['new_name'];
				if (is_array($resolutions) && count($resolutions) > 0) {
					// Create thumbs for each successfully uploaded files
					create_thumbs(
						$uploadedFile['full_path_new_name'],
						$uploadedFile['new_name'],
						$dir,
						$resolutions
					);
				}
			}
		} else {
			$message = 'File could not uploaded due to some error(s)';
			foreach ($uploadInit->errorMessages as $error) {
				$errorLogs[] = $error;
			}
			create_log('file', 'info', ['message' => $message, 'extra' => $errorLogs]);
		}
	}
	if ($return == 'string') {
		$uploadFileNames = !empty($uploadFileNames[0]) ? $uploadFileNames[0] : '';
	}

	return $uploadFileNames;
}
/**
 * Create thumbnails or other resolution images from a Primary Source
 *
 * @param $sourceUrl    File uploading key name
 * @param $originalname in which directory we will going to save the file
 * @param $directory    In which resolutions files will be saved
 * @param $resolutions  The format you want as a response after uplaod
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return none
 */
function create_thumbs($sourceUrl, $originalname, $directory, $resolutions) {
	$extensions = ['JPEG', 'JPG', 'GIF', 'WEBP', 'PNG'];
	$getSrcExtension = pathinfo($sourceUrl, PATHINFO_EXTENSION);
	if (in_array(strtoupper($getSrcExtension), $extensions)) {
		$resizeDimentions = $resolutions;
		// Get minimum dimension from array of dimension for thumbnail size
		$thumbDimention = min($resizeDimentions);
		// Create thumb image with prefixed "thumb_" key
		$imageResizer = new ImageManager();
		$img = $imageResizer->make($sourceUrl);
		$img->resize($thumbDimention, $thumbDimention);
		$img->save($directory . '/' . 'thumb_' . $originalname);
		// Convert Other Sized Images Other than thumb images
		$convertDimentions = array_diff($resizeDimentions, [$thumbDimention]);
		// If one dimention was given then no need to run below code
		if (!empty($convertDimentions) && count($convertDimentions) > 0) {
			$imageResizer = new ImageManager();
			$img = $imageResizer->make($sourceUrl);
			foreach ($convertDimentions as $dimension) {
				$img->resize($dimension, $dimension);
				$img->save($directory . $dimension . 'x' . $dimension . '-' . $originalname);
			}
		}
	}
}

/**
 * Download a remote file at a given URL and save it to a local folder
 *
 * @param $url      URL of the remote file
 * @param $toDir    Directory where the remote file has to be saved once downloaded.
 * @param $withName The name of file to be saved as.
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return boolean
 *
 * Note : This function does not work in the Codelet due to network restrictions
 * but does work when executed from command line or from within a webserver.
 */
function download_file($url, $toDir, $withName) {
	// open file in rb mode
	if ($fpRemote = fopen($url, 'rb')) {
		// local filename
		$localFile = $toDir . "/" . $withName;
		// read buffer, open in wb mode for writing
		if ($fpLocal = fopen($localFile, 'wb')) {
			// read the file, buffer size 8k
			while ($buffer = fread($fpRemote, 8192)) {
				// write buffer in  local file
				fwrite($fpLocal, $buffer);
			}
			// close local
			fclose($fpLocal);
		} else {
			// could not open the local URL
			fclose($fpRemote);
			return false;
		}
		// close remote
		fclose($fpRemote);
		return true;
	} else {
		// could not open the remote URL
		return false;
	}
} // end

/**
 * Opens the file specified in the path and returns it as a string.
 *
 * @param $location Path to file
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return File contents
 */
function create_directory($location) {
	if (!is_dir($location)) {
		mkdir($location, 0777, true);
		return true;
	}
}

/**
 * Read File
 * Opens the file specified in the path and returns it as a string.
 *
 * @param $file Path to file
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return File contents
 */
function read_file($file) {
	if (file_exists($file)) {
		chmod($file, 0755);
		return @file_get_contents($file);
	}
	return false;
}
/**
 * Delete File
 * Delete a file with the file path according to Linux permission
 *
 * @param $location Path to file
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return File contents
 */
function delete_file($location) {
	// Path relative to where the php file is or absolute server path
	if (file_exists($location)) {
		// Comment this out if you are on the same folder
		// chdir($location);
		//Insert an Invalid UserId to set to Nobody Owner; for instance 465
		// chown($location, 465);
		if (unlink($location)) {
			return true;
		}
	}
	return false;
}
/**
 * Write File
 * Writes data to the file specified in the path.
 * Creates a new file if non-existent.
 *
 * @param $path File path
 * @param $data Data to write
 * @param $mode fopen() mode (default: 'wb')
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return bool
 */
function write_file($path, $data, $mode = 'wb') {
	if (!$openFilePath = @fopen($path, $mode)) {
		return false;
	}

	flock($openFilePath, LOCK_EX);

	for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result) {
		if (($result = fwrite($openFilePath, substr($data, $written))) === false) {
			break;
		}
	}

	flock($openFilePath, LOCK_UN);
	fclose($openFilePath);

	return is_int($result);
}
/**
 * Reads the specified directory and builds an array containing the filenames.
 * Any sub-folders contained within the specified path are read as well.
 *
 * @param $source_dir   path to source
 * @param $include_path whether to include the base path as part of the filename
 * @param $_recursion   internal variable to determine recursion status - do not
 *                      use in calls
 *
 * @author tanmayap@riaxe.com
 * @date   07 Jan 2020
 * @return bool
 */
function read_dir($sourceDir, $includePath = false, $_recursion = false) {
	static $_filedata = array();

	if ($fp = @opendir($sourceDir)) {
		// reset the array and make sure $sourceDir has a trailing slash on the initial call
		if ($_recursion === false) {
			$_filedata = array();
			$sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		}

		while (false !== ($file = readdir($fp))) {
			if (is_dir($sourceDir . $file) && $file[0] !== '.') {
				read_dir($sourceDir . $file . DIRECTORY_SEPARATOR, $includePath, true);
			} elseif ($file[0] !== '.') {
				$_filedata[] = ($includePath === true) ? $sourceDir . $file : $file;
			}
		}

		closedir($fp);
		return $_filedata;
	}

	return false;
}

/*
|--------------------------------------------------------------------------
| Date Time Related Helpers
|--------------------------------------------------------------------------
|
 */
/**
 * It will convert a long text into a short text with a elipse dots
 *
 * @param $datetime Date tand time string
 * @param $full     Show full elapsed time or short elapsed time
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Date String
 */
function time_elapsed($datetime, $full = false) {
	$now = new DateTime;
	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array(
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) {
		$string = array_slice($string, 0, 1);
	}

	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * - Carbon: The Carbon class is inherited from the PHP DateTime class.
 * - Carbon is used by Eloquent exclusively
 *
 * - For Date and time operations, we use Carbon and this method uses Carbon to
 *   make all such operations Add, Subtract, get Current, yesterday, tomorrow
 *   etc using Carbon
 *
 * @param $option    type flag
 * @param $condition Conditional parameters in array formats
 * @param $format    Output format
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Date String
 */
function date_time($option = 'current', $condition = [], $format = 'string') {
	$dateReturn = '';
	switch ($option) {
	case 'today':
		$dateReturn = \Carbon\Carbon::now();
		break;
	case 'tomorrow':
		$dateReturn = \Carbon\Carbon::tomorrow();
		break;
	case 'add':
		$dateObj = \Carbon\Carbon::now();
		$dateReturn = $dateObj->addDays($condition['days']);
		break;
	case 'sub':
		$dateObj = \Carbon\Carbon::now();
		$dateReturn = $dateObj->subDays($condition['days']);
		break;
	default:
		//
		break;
	}
	if ($format == 'string') {
		return $dateReturn->toDateTimeString();
	} else if ($format == 'timestamp') {
		return $dateReturn->timestamp;
	}
}
/**
 * Compare two dates and sort them
 *
 * @param $a Sort param
 * @param $b Sort param
 *
 * @author tanmayap@riaxe.com
 * @date   5 Oct 2019
 * @return Date String
 */
function date_compare($a, $b) {
	$t1 = strtotime($a['created_at']);
	$t2 = strtotime($b['created_at']);
	return $t2 - $t1;
}
/**
 * Clean a string by removing any spacial characters
 *
 * @param $string a string
 *
 * @author tanmayap@riaxe.com
 * @date   21 Mar 2020
 * @return string
 */
function clean($string) {
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/**
 * Create PDF
 *
 * @param $html        HTML content of pdf
 * @param $dir         Directory Location for Save Files
 * @param $pdfFileName pdf file name
 *
 * @author debashrib@riaxe.com
 * @date   30 Mar 2019
 * @return json response
 */
function create_pdf($html, $dir, $pdfFileName = '', $orientation = 'landscape') {
	if (!file_exists($dir)) {
		create_directory($dir);
	}
	//include autoloader
	include_once 'app/Dependencies/dompdf/autoload.inc.php';
	$options = new Options();
	$options->set('isRemoteEnabled', true);
	// instantiate and use the dompdf class
	$dompdf = new Dompdf($options);
	$dompdf->loadHtml($html);
	$dompdf->set_paper('a4', $orientation);
	// Render the HTML as PDF
	$dompdf->render();

	$output = $dompdf->output();
	$random = getRandom();
	if ($pdfFileName != '') {
		$random = $pdfFileName;
	}
	$pdfName = $random . ".pdf";
	if (file_put_contents($dir . $pdfName, $output)) {
		return $pdfName;
	}
	return false;
}

/**
 * Download file into local system
 *
 * @param $dir File location
 *
 * @author radhanatham@riaxe.com
 * @date   03 Jan 2020
 * @return boolean
 */
function file_download($dir, $isRemove = 1) {
	if (file_exists($dir)) {
		header('Content-Description: File Transfer');
		header("Content-type: application/x-msdownload", true, 200);
		header('Content-Disposition: attachment; filename=' . basename($dir));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header("Pragma: no-cache");
		header('Content-Length: ' . filesize($dir));
		readfile($dir);
		if (file_exists($dir) && $isRemove == 1) {
			unlink($dir);
		}
		$status = true;
		exit();
	} else {
		$status = false;
	}
	return $status;
}
/**
 * Generate bracode png image
 *
 * @param $value input value
 *
 * @author radhanatham@riaxe.com
 * @date   26 May 2020
 * @return string
 */
function generate_barcode($value) {
	$generatorPNG = new BarcodeGeneratorPNG();
	return $generatorPNG->getBarcode($value, $generatorPNG::TYPE_CODE_128);

}

/**
 * GET: Catalog API call
 *
 * @param $params   API parameter
 * @param $endpoint API endpoint
 *
 * @author radhanatham@riaxe.com
 * @date   15 July 2020
 * @return string
 */
function api_call_by_curl($params, $endpoint, $test = false) {
	if ($endpoint == '') {
		$url = CATALOG_API_URL . 'catalog/services/api/v1/catalogs';
	} else {
		$url = CATALOG_API_URL . 'catalog/services/api/v1/catalogs/' . $endpoint;
	}
	if (!is_array($params)) {
		$url = $endpoint;
	}
	if (!empty($params) && is_array($params)) {
		$url .= '?' . http_build_query($params);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, array());
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	$result = curl_exec($ch);
	curl_close($ch);
	if ($test) {
		print_r($url);exit();
	}
	$productData = json_clean_decode($result, true);
	return $productData;
}

/**
 *GET: Simple URl call through curl
 *
 * @param $endpoint API endpoint
 *
 * @author robert@imprintnext.com
 * @date  05 Oct 2020
 * @return Array
 */
function call_simple_curl($endpoint) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_HEADER, array());
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	$result = curl_exec($ch);
	curl_close($ch);
	return $data = json_decode($result, true);
}

/**
 * File upload for single or multiple files. If you give multiple dimensions
 * then first dim will be treated as thumb and others as normal images with
 * different resolutions
 *
 * @param $fileKey File uploading key name
 * @param $dir     Directory Location for Save Files
 * @param $res     Resolutions of child Images for generation
 * @param $return  Returning format. Either array or String. For single file
 *                 upload it will be string and for multiple file uploading it
 *                 will be array
 *
 * @author satyabratap@riaxe.com
 * @date   4 Oct 2020
 * @return Array or String
 */
function do_upload_aspect($fileKey, $dir, $resolutions = [], $return = 'array') {
	$uploadFileNames = [];
	$errorLogs = [];
	if (!empty($fileKey) && !empty($_FILES)) {
		if (!file_exists($dir)) {
			create_directory($dir);
		}
		$uploadInit = new Upload($fileKey);
		$uploadInit->moveUploadedTo = $dir;
		$uploadInit->newFileName = getRandom();
		$uploadInit->securityScan = false;
		$uploadResult = $uploadInit->upload();
		// Get the uploaded file's data.
		$uploadedFiles = $uploadInit->getUploadedData();
		if ($uploadResult === true) {
			foreach ($uploadedFiles as $uploadedFile) {
				$uploadFileNames[] = $uploadedFile['new_name'];
				if (is_array($resolutions) && count($resolutions) > 0) {
					// Create thumbs for each successfully uploaded files
					create_thumb_aspect(
						$uploadedFile['full_path_new_name'],
						$uploadedFile['new_name'],
						$dir,
						$resolutions
					);
				}
			}
		} else {
			$message = 'File could not uploaded due to some error(s)';
			foreach ($uploadInit->errorMessages as $error) {
				$errorLogs[] = $error;
			}
			create_log('file', 'info', ['message' => $message, 'extra' => $errorLogs]);
		}
	}
	if ($return == 'string') {
		$uploadFileNames = !empty($uploadFileNames[0]) ? $uploadFileNames[0] : '';
	}

	return $uploadFileNames;
}
/**
 * Create thumbnails as per the aspect ratio
 *
 * @param $sourceUrl    File uploading key name
 * @param $originalname in which directory we will going to save the file
 * @param $directory    In which resolutions files will be saved
 * @param $resolutions  The format you want as a response after uplaod
 *
 * @author satyabratap@riaxe.com
 * @date   4 Oct 2020
 * @return none
 */
function create_thumb_aspect($sourceUrl, $originalname, $directory, $resolutions) {
	$extensions = ['JPEG', 'JPG', 'GIF', 'WEBP', 'PNG'];
	$getSrcExtension = pathinfo($sourceUrl, PATHINFO_EXTENSION);
	if (in_array(strtoupper($getSrcExtension), $extensions)) {
		$resizeDimentions = $resolutions;
		// Get minimum dimension from array of dimension for thumbnail size
		$thumbDimention = min($resizeDimentions);
		// Create thumb image with prefixed "thumb_" key
		$imageResizer = new ImageManager();
		$width = $thumbDimention; // your max width
		$height = $thumbDimention; // your max height
		$img = $imageResizer->make($sourceUrl);
		$img->height() > $img->width() ? $width = null : $height = null;
		$img->resize($width, $height, function ($constraint) {
			$constraint->aspectRatio();
		});
		$img->save($directory . '/' . 'thumb_' . $originalname);
	}
}
/**
 * Delete a Directory and it's contents
 *
 * @param $dir Directory name
 *
 * @author souamys@riaxe.com
 * @date   12 Jan 2019
 * @return boolean
 */
function rrmdir($dir) {
	$fileStatus = false;
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
					rrmdir($dir . DIRECTORY_SEPARATOR . $object);
				} else {
					unlink($dir . DIRECTORY_SEPARATOR . $object);
				}

			}
			$fileStatus = true;
		}
		rmdir($dir);
	}
	return $fileStatus;
}

/**
 * generate qr code
 *
 * @param $dir Directory name
 *
 * @author souamys@riaxe.com
 * @date   12 Jan 2019
 * @return boolean
 */
function generate_qrcode($text, $file, $ecc, $pixel_Size, $frame_size) {
	include_once 'app/Dependencies/phpqrcode/qrlib.php';
	QRcode::png($text, $file, $ecc, $pixel_Size, $frame_size);
}
