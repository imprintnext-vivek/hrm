<?php
/**
 * PHP upload class that is able to validate requirements and limitations, real
 * file's mime type check, detect the errors and report.
 *
 * PHP version 5.6
 *
 * @category  Upload
 * @package   File_Upload
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */

namespace App\Dependencies;

/**
 * Print Profile Controller
 *
 * @category Upload
 * @package  File_Upload
 * @author   Tanmaya Patra <tanmayap@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */
class Upload
{

    /**
     * Allowed file extensions. Example: array('jpg', 'gif', 'png').
     *
     * @var array
     */
    public $allowedFileExt;

    /**
     * Array|null The array set of file extensions and its valid mime types for
     * check when process the uploaded files.<br> Example:
     * <pre>
     * array(
     *     'jpg' => array('image/jpeg', 'image/pjpeg'),
     *     'txt' => array('text/plain'),
     * );
     * </pre>
     * If you don't want to validate mime type, set this property to an empty
     * array. Example: `$Upload->fileExtMimeTypes = array();` Set to
     * NULL to use default mime types in
     * `setupFileExtensionsMimeTypesForValidation()` method.
     *
     * @var array
     */
    public $fileExtMimeTypes;

    /**
     * Contain all values from $_FILES['inputFileName'] for works with upload
     * process. This will be very useful when upload multiple files.<br>
     * Example:
     * $Upload->files['inputFileName']; is same as
     * $_FILES['inputFileName']
     *
     * @var array
     */
    protected $files = array();

    /**
     * The input file name. ($_FILES['inputFileName']).
     *
     * @var string
     */
    protected $inputFileName;

    /**
     * Set max file size to upload. This file size unit is in bytes only.
     *
     * @var integer
     */
    public $maxFileSize;

    /**
     * The array of max image width and height. The value must be array of
     * width, height by order and the number must be integer. Example:
     * `array(500, 300)` mean width 500 pixels and height 300 pixels. Set to
     * empty array for not validate.
     *
     * @var array
     */
    public $maxImageDimensions = array();

    /**
     * The queue for move uploaded file(s). This is very useful when upload
     * multiple files.
     *
     * @var array
     */
    protected $moveUploadedQueue = array();

    /**
     * Path to store files that was uploaded to move to. Do not end with
     * trailing slash.
     *
     * @var string
     */
    public $moveUploadedTo = '.';

    /**
     * Set new file name, just set the file name only. No extension.<br>
     * Important! This property is not recommend to set it if you upload
     * multiple files with same input file name. It is recommended to leave this
     * as null and set overwrite property to true or false.<br> If you want to
     * set the name while upload multiple files, it is recommended that you set
     * overwrite property to false.
     *
     * @var string
     */
    public $newFileName;

    /**
     * To overwrite the uploaded file set it to true, otherwise set
     * it to false.
     *
     * @var boolean
     */
    public $overwrite = false;

    /**
     * To rename uploaded file name to safe for web set it to true, otherwise
     * set it to false.<br> The safe for web file name is English and number
     * chacters, no space (replaced with dash), no special characters, allowed
     * dash and underscore.
     *
     * @var boolean
     */
    public $webSafeFileName = true;

    /**
     * Set to true to enable security scan such as php open tag (<?php). Set to
     * false to not scan. This is optional security.
     *
     * @var boolean
     */
    public $securityScan = false;

    /**
     * If you upload multiple files and there is at least one file that did not
     * pass the validation, do you want it to stop?<br> Set to true to stop and
     * delete all uploaded files (all uploaded files must pass validation).<br>
     * Set to false to skip the error files (failed validation files are report
     * as error, success validation files continue the process).
     *
     * @var boolean
     */
    public $stopOnFailedUpload = false;

    /**
     * Contain error codes.
     *
     * @var array If there is at least one error, it will be set to here.
     */
    public $errorCodes = array();

    /**
     * If there is at least one error message it will be set to here.
     *
     * @var array
     */
    public $errorMessages = array();

    /**
     * Begins upload class.
     *
     * @param string $inputFileName The name of input file.
     */
    public function __construct($inputFileName)
    {
        $this->clear();
        $this->setInputFileName($inputFileName);
    }

    /**
     * Class destructor. Works at end of class (unset class's variable).
     */
    public function __destruct()
    {
        $this->clear();
    }

    /**
     * Placeholder method for language editor program like Poedit to lookup for
     * the words that is using this method.<br> This method does nothing but for
     * who use program like Poedit to search/lookup the words that is using this
     * method to create translation.<br> Example:<br> There is this code in
     * generator class. <code>static::__('Hello');</code><br> Use Poedit to
     * search for __ function to update/retreive the source text and translate
     * it.
     *
     * @param string $string The message to use.
     *
     * @return string Return the same string.
     */
    protected static function __($string)
    {
        return $string;
    } // __

    /**
     * Clear all properties to its default values.
     *
     * @return null
     */
    public function clear()
    {
        $this->allowedFileExt = null;
        $this->errorMessages = array();
        $this->fileExtMimeTypes = null;
        $this->files = array();
        $this->inputFileName = null;
        $this->maxFileSize = null;
        $this->maxImageDimensions = array();
        $this->moveUploadedQueue = array();
        $this->moveUploadedTo = '.';
        $this->newFileName = null;
        $this->overwrite = false;
        $this->webSafeFileName = true;
        $this->securityScan = false;
        $this->stopOnFailedUpload = true;
    } // clear

    /**
     * Clear uploaded files at temp folder. (if it is able to write/delete).
     *
     * @return array
     */
    protected function clearUploadedAtTemp()
    {
        foreach ($this->moveUploadedQueue as $key => $queueItem) {
            if (is_array($queueItem) && isset($queueItem['tmp_name'])) {
                if (is_file($queueItem['tmp_name'])
                    && is_writable($queueItem['tmp_name'])
                ) {
                    unlink($queueItem['tmp_name']);
                }
            }
        } // endforeach;
        unset($key, $queueItem);
        $this->moveUploadedQueue = array();
    } // clearUploadedAtTemp

    /**
     * Get the uploaded data.
     *
     * @return array Return array set of successful uploaded files and its data.
     */
    public function getUploadedData()
    {
        if (empty($this->moveUploadedQueue)
            || !is_array($this->moveUploadedQueue)
        ) {
            return array();
        }

        $output = array();

        foreach ($this->moveUploadedQueue as $key => $queueItem) {
            if (is_array($queueItem)
                && array_key_exists('name', $queueItem)
                && array_key_exists('tmp_name', $queueItem)
                && array_key_exists('new_name', $queueItem)
                && array_key_exists('move_uploaded_to', $queueItem)
                && array_key_exists('move_uploaded_status', $queueItem)
                && $queueItem['move_uploaded_status'] === 'success'
            ) {
                // get file extension only
                $fileNameExplode = explode('.', $queueItem['name']);
                $fileExtension = (isset(
                    $fileNameExplode[
                        count($fileNameExplode) - 1
                    ]
                ) ? $fileNameExplode[
                    count($fileNameExplode) - 1
                ] : null
                );
                unset($fileNameExplode);

                // get file info
                $fileInfo = new \finfo();
                $mime = $fileInfo->file(
                    $queueItem['move_uploaded_to'], FILEINFO_MIME_TYPE
                );
                unset($fileInfo);

                $output[$key] = array();
                $output[$key]['name'] = $queueItem['name'];
                $output[$key]['extension'] = $fileExtension;
                $output[$key]['size'] = (
                    is_file(
                        $queueItem['move_uploaded_to']
                    ) ? filesize(
                        $queueItem['move_uploaded_to']
                    ) : 0
                );
                $output[$key]['new_name'] = $queueItem['new_name'];
                $output[$key]['full_path_new_name'] = $queueItem[
                    'move_uploaded_to'
                ];
                $output[$key]['mime'] = $mime;
                $output[$key]['md5_file'] = (
                    is_file(
                        $queueItem['move_uploaded_to']
                    ) ? md5_file(
                        $queueItem['move_uploaded_to']
                    ) : null
                );

                unset($fileExtension, $mime);
            }
        }

        return $output;
    } // getUploadedData

    /**
     * Move the uploaded file(s).
     *
     * @return boolean Return true on success, false on failure.
     */
    protected function moveUploadedFiles()
    {
        $i = 0;
        if (is_array($this->moveUploadedQueue)) {
            foreach ($this->moveUploadedQueue as $key => $queueItem) {
                if (is_array($queueItem) && isset($queueItem['name'])
                    && isset($queueItem['tmp_name'])
                    && isset($queueItem['new_name'])
                ) {
                    $destinationName = $queueItem['new_name'];

                    if ($this->overwrite === false) {
                        // verify file exists and set new name.
                        $destinationName = $this->renameDuplicateFile(
                            $destinationName
                        );
                    }
                    //
                    $moveResult = move_uploaded_file(
                        $queueItem['tmp_name'],
                        $this->moveUploadedTo
                        . DIRECTORY_SEPARATOR . $destinationName
                    );
                    if ($moveResult === false) {
                        $moveResult = copy(
                            $queueItem['tmp_name'],
                            $this->moveUploadedTo
                            . DIRECTORY_SEPARATOR . $destinationName
                        );
                    }

                    if ($moveResult === true) {
                        // Move uploaded file success.
                        $this->moveUploadedQueue[$key] = array_merge(
                            $this->moveUploadedQueue[$key],
                            array(
                                'new_name' => $destinationName,
                                'move_uploaded_status' => 'success',
                                'move_uploaded_to' => $this->moveUploadedTo
                                . DIRECTORY_SEPARATOR . $destinationName,
                            )
                        );
                        $i++;
                    } else {
                        $this->setErrorMessage(
                            sprintf(
                                static::__(
                                    'Unable to move uploaded file. (%s =&gt; %s)'
                                ), $queueItem['name'], $this->moveUploadedTo
                                . DIRECTORY_SEPARATOR . $destinationName
                            ),
                            'RDU_MOVE_UPLOADED_FAILED',
                            $queueItem['name'] . '=&gt; ' . $this->moveUploadedTo
                            . DIRECTORY_SEPARATOR . $destinationName
                        );
                    }

                    unset($destinationName, $moveResult);
                }
            } // endforeach;
            unset($key, $queueItem);
        }

        if ($i == count($this->moveUploadedQueue) && $i > 0) {
            return true;
        } else {
            return false;
        }
    } // moveUploadedFiles

    /**
     * Rename the file where it is duplicate with existing file.
     *
     * @param $fileName File name to check
     *
     * @return string Return renamed file that will not duplicate the existing file.
     */
    protected function renameDuplicateFile($fileName)
    {
        if (!file_exists(
            $this->moveUploadedTo . DIRECTORY_SEPARATOR . $fileName
        )
        ) {
            return $fileName;
        } else {
            $fileNameExplode = explode('.', $fileName);
            $fileExtension = (isset(
                $fileNameExplode[count($fileNameExplode) - 1]
            ) ? $fileNameExplode[count($fileNameExplode) - 1] : null
            );
            unset($fileNameExplode[count($fileNameExplode) - 1]);
            $fileNameOnly = implode('.', $fileNameExplode);
            unset($fileNameExplode);

            $i = 1;
            $found = true;
            do {
                $newFileName = $fileNameOnly . '_' . $i . '.' . $fileExtension;
                if (file_exists(
                    $this->moveUploadedTo . DIRECTORY_SEPARATOR . $newFileName
                )
                ) {
                    $found = true;
                    if ($i > 1000) {
                        // too many loop
                        $fileName = uniqid() . '-' . str_replace(
                            '.', '', microtime(true)
                        );
                        $found = false;
                    }
                } else {
                    $fileName = $newFileName;
                    $found = false;
                }
                $i++;
            } while ($found === true);

            unset($fileExtension, $fileNameOnly, $newFileName);
            return $fileName;
        }
    } // renameDuplicateFile

    /**
     * Security scan. Scan for such as embedded php code in the uploaded file.
     *
     * @return boolean Return true on safety, return false for otherwise.
     */
    protected function securityScan()
    {
        if (is_array($this->files[$this->inputFileName])
            && array_key_exists('name', $this->files[$this->inputFileName])
            && array_key_exists('tmp_name', $this->files[$this->inputFileName])
            && $this->files[$this->inputFileName]['tmp_name'] != null
        ) {
            // there is an uploaded file.
            if (is_file($this->files[$this->inputFileName]['tmp_name'])) {
                $fileContent = file_get_contents(
                    $this->files[$this->inputFileName]['tmp_name']
                );

                // scan php open tag
                if (stripos($fileContent, '<?php') !== false
                    || stripos($fileContent, '<?=') !== false
                ) {
                    // found php open tag. (<?php).
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'Error! Found php embedded in file. (%s).'
                            ), $this->files[$this->inputFileName]['name']
                        ),
                        'RDU_SEC_ERR_PHP',
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }

                // scan cgi/perl
                if (stripos($fileContent, '#!/') !== false
                    && stripos($fileContent, '/perl') !== false
                ) {
                    // found cgi/perl header.
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'Error! Found cgi/perl embedded in file. (%s).'
                            ), $this->files[$this->inputFileName]['name']
                        ),
                        'RDU_SEC_ERR_CGI',
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }

                if (stripos($fileContent, '#!/') !== false
                    && (stripos($fileContent, '/bin/sh') !== false
                    || stripos($fileContent, '/bin/bash') !== false
                    || stripos($fileContent, '/bin/csh') !== false
                    || stripos($fileContent, '/bin/tcsh') !== false)
                ) {
                    // found shell script.
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'Error! Found shell script embedded in file. (%s).'
                            ), $this->files[$this->inputFileName]['name']
                        ),
                        'RDU_SEC_ERR_CGI',
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }

                unset($fileContent);
            }
        }

        return true;
    } // securityScan

    /**
     * Set the error message into errorMessages and errorCodes properties.
     *
     * @param string $errorMessages   The error message.
     * @param string $code            The error code, start with RDU_ and follow
     *                                with number or short error message without
     *                                space.
     * @param string $errorAttributes Error attributes. For example: 9MB > 2MB
     *                                in case that limit file size to 2MB but
     *                                uploaded 9MB, or showing file name that
     *                                have problem.
     * @param string $errorFileName   The file name with extension.
     * @param string $errorFileSize   The file size in bytes.
     * @param string $errorFileMime   The file mime type.
     *
     * @return none
     */
    protected function setErrorMessage(
        $errorMessages,
        $code,
        $errorAttributes = '',
        $errorFileName = '',
        $errorFileSize = '',
        $errorFileMime = ''
    ) {
        $arg_list = func_get_args();
        $numargs = func_num_args();
        for ($i = 0; $i < $numargs; $i++) {
            if (is_array($arg_list) && array_key_exists($i, $arg_list)
                && !is_scalar($arg_list[$i])
            ) {
                return false;
            } elseif ($arg_list === false) {
                return false;
            }
        }
        unset($arg_list, $i, $numargs);

        $this->errorMessages[] = $errorMessages;
        $this->errorCodes[] = array(
            'code' => $code,
            'errorAttributes' => $errorAttributes,
            'errorFileName' => $errorFileName,
            'errorFileSize' => $errorFileSize,
            'errorFileMime' => $errorFileMime,
        );
    } // setErrorMessage

    /**
     * Set input file name.<br> If you begins new class object then you don't
     * have to call this method. You must call this method after called to the
     * clear() method.<br> Or you can call this method in case that you want to
     * process the other uploaded file next to previous one.
     *
     * @param string $inputFileName The name of input file.
     *
     * @return none
     */
    public function setInputFileName($inputFileName)
    {
        if (!is_scalar($inputFileName)) {
            throw new \InvalidArgumentException(
                static::__(
                    'The input file name must be string.'
                )
            );
        }

        $this->inputFileName = $inputFileName;
    } // setInputFileName

    /**
     * Set the new file name if it was not set.
     *
     * @return random file name
     */
    protected function setNewFileName()
    {
        $this->newFileName = trim($this->newFileName);

        if ($this->newFileName == null) {
            // if new file name was not set, set it from uploaded file name.
            if (is_array($this->files[$this->inputFileName])
                && array_key_exists('name', $this->files[$this->inputFileName])
            ) {
                $fileNameExplode = explode(
                    '.', $this->files[$this->inputFileName]['name']
                );
                unset($fileNameExplode[count($fileNameExplode) - 1]);
                $this->newFileName = implode('.', $fileNameExplode);
                unset($fileNameExplode);
            } else {
                $this->setNewFileNameToRandom();
            }
        }

        // do not allow name that contain one of these characters.
        $reservedCharacters = array(
            '\\', '/', '?', '%', '*', ':', '|', '"', '<', '>', '!', '@',
        );
        $this->newFileName = str_replace(
            $reservedCharacters, '', $this->newFileName
        );
        unset($reservedCharacters);

        if (preg_match('#[^\.]+#iu', $this->newFileName) == 0) {
            // found the name is only dots. example ., .., ..., ....
            $this->setNewFileNameToRandom();
        }

        // reserved words or reserved names. do not allow if new name is set to
        // one of these words or names.
        // make it case in-sensitive.
        $reservedWords = array(
            'CON', 'PRN', 'AUX', 'CLOCK$', 'NUL',
            'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9',
            'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9',
            'LST', 'KEYBD$', 'SCREEN$', '$IDLE$', 'CONFIG$', '$Mft', '$MftMirr',
            '$LogFile', '$Volume', '$AttrDef', '$Bitmap', '$Boot', '$BadClus',
            '$Secure', '$Upcase', '$Extend', '$Quota', '$ObjId', '$Reparse',
        );
        foreach ($reservedWords as $reservedWord) {
            if (strtolower($reservedWord) == strtolower($this->newFileName)) {
                $this->setNewFileNameToRandom();
            }
        }
        unset($reservedWord, $reservedWords);

        // in the end if it is still left new name as null... set random name to
        // it.
        if ($this->newFileName == null) {
            $this->setNewFileNameToRandom();
        }
    }

    /**
     * Set the new file name to random. (unique id and microtime).
     *
     * @return string
     */
    protected function setNewFileNameToRandom()
    {
        $this->newFileName = uniqid() . '-' . str_replace('.', '', microtime(true));
    }

    /**
     * Setup file extensions mime types for validation. (In case that it was not
     * set).
     *
     * @return array
     */
    protected function setupFileExtensionsMimeTypesForValidation()
    {
        if (!is_array($this->fileExtMimeTypes) && $this->fileExtMimeTypes == null) {
            // extensions mime types was not set and not set to NOT validate
            // (empty array).
            $defaultMimeTypeFile = __DIR__ . DIRECTORY_SEPARATOR 
                . 'FileExtensionsMimeTypes.php';
            if (is_file($defaultMimeTypeFile)) {
                $this->fileExtMimeTypes = include $defaultMimeTypeFile;
            }
            unset($defaultMimeTypeFile);
        }

        if (is_array($this->fileExtMimeTypes)) {
            // if mime types was set, change the keys to lower case.
            $this->fileExtMimeTypes = array_change_key_case(
                $this->fileExtMimeTypes, CASE_LOWER
            );
        }

        if (is_array($this->allowedFileExt)) {
            // if allowed extensions was set, change the values to lower case.
            $this->allowedFileExt = array_map('strtolower', $this->allowedFileExt);
        }
    }

    /**
     * Set the file name that is safe for web.<br> The safe for web file name is
     * English and number chacters, no space (replaced with dash), no special
     * characters, allowed dash and underscore.
     *
     * @return string
     */
    protected function setWebSafeFileName()
    {
        if ($this->newFileName == null) {
            $this->setNewFileName();
        }

        // replace multiple spaces to one space.
        $this->newFileName = preg_replace('#\s+#iu', ' ', $this->newFileName);
        // replace space to dash.
        $this->newFileName = str_replace(' ', '-', $this->newFileName);
        // replace non alpha-numeric to nothing.
        $this->newFileName = preg_replace('#[^\da-z\-_]#iu', '', $this->newFileName);
        // replace multiple dashes to one dash.
        $this->newFileName = preg_replace('#-{2,}#', '-', $this->newFileName);
    }

    /**
     * Test get the real file's mime type using finfo_file.<br> This is very
     * useful when you want to add new file extension and mime type to validate
     * uploaded files.
     *
     * @param $inputFileName The input file name
     *
     * @return string
     */
    public function testGetUploadedMimetype($inputFileName = null)
    {
        if (!is_scalar($inputFileName)) {
            throw new \InvalidArgumentException(
                static::__(
                    'The input file name must be string.'
                )
            );
        }

        if ($inputFileName == null) {
            $inputFileName = $this->inputFileName;
        }

        if (!isset($_FILES[$inputFileName]['name'])
            || (isset($_FILES[$inputFileName]['name'])
            && $_FILES[$inputFileName]['name'] == null)
            || !isset($_FILES[$inputFileName]['tmp_name'])
            || (isset($_FILES[$inputFileName]['tmp_name'])
            && $_FILES[$inputFileName]['tmp_name'] == null)
        ) {
            return static::__(
                'You did not upload any file, please upload a file to get info.'
            );
        }

        if (!function_exists('finfo_open')
            || !function_exists('finfo_file')
        ) {
            return static::__(
                'There is no finfo_open() function or
                finfo_file() function to get file\'s info.
                Please verify PHP installation.'
            );
        }

        $output = sprintf(
            static::__(
                'File name: %s'
            ), $_FILES[$inputFileName]['name']
        ) . '<br>' . "\n";
        $fileNameExp = explode('.', $_FILES[$inputFileName]['name']);
        $fileExtension = $fileNameExp[count($fileNameExp) - 1];
        unset($fileNameExp);
        $output .= sprintf(
            static::__(
                'File extension: %s'
            ), $fileExtension
        ) . '<br>' . "\n";

        $fileInfo = new \finfo();
        $fileMimetype = $fileInfo->file(
            $_FILES[$inputFileName]['tmp_name'], FILEINFO_MIME_TYPE
        );
        $output .= sprintf(
            static::__(
                'Mime type: %s'
            ), $fileMimetype
        ) . '<br>' . "\n";
        $output .= '<br>' . "\n";
        $output .= static::__(
            'The array for use with extension-mime types validation.'
        ) . '<br>' . "\n";
        $output .= 'array(<br>' . "\n";
        $output .= '&nbsp; &nbsp; \'' . $fileExtension
            . '\' =&gt; array(\''
            . $fileMimetype . '\'),<br>' . "\n";
        $output .= ');' . "\n";
        unset($fileInfo);

        if (is_writable($_FILES[$inputFileName]['tmp_name'])) {
            unlink($_FILES[$inputFileName]['tmp_name']);
        }

        unset($fileExtension, $fileMimetype);
        return $output;
    } // testGetUploadedMimetype

    /**
     * Start the upload and move uploaded files process.
     *
     * @return boolean
     */
    public function upload()
    {
        // validate that all options properties was properly set to correct type.
        $this->validateOptionsProperties();
        // setup file extensions and mime types for validation.
        $this->setupFileExtensionsMimeTypesForValidation();

        // verify that upload location.
        if (!is_dir($this->moveUploadedTo)) {
            $this->setErrorMessage(
                static::__(
                    'The target location where the uploaded file(s)
                    will be moved to is not folder or directory.'
                ),
                'RDU_MOVE_UPLOADED_TO_NOT_DIR',
                $this->moveUploadedTo
            );
            return false;
        } elseif (is_dir($this->moveUploadedTo) 
            && !is_writable($this->moveUploadedTo)
        ) {
            $this->setErrorMessage(
                static::__(
                    'The target location where the uploaded file(s)
                    will be moved to is not writable.
                    Please check the folder permission.'
                ),
                'RDU_MOVE_UPLOADED_TO_NOT_WRITABLE',
                $this->moveUploadedTo
            );
            return false;
        } else {
            // solve the move uploaded to as a real path.
            $this->moveUploadedTo = realpath($this->moveUploadedTo);
        }

        if (isset($_FILES[$this->inputFileName]['name'])) {
            if (is_array($_FILES[$this->inputFileName]['name'])) {
                // if multiple file upload.
                foreach ($_FILES[$this->inputFileName]['name'] as $key => $value) {
                    $this->files[$this->inputFileName]['input_file_key'] = $key;
                    $this->files[$this->inputFileName]['name'] 
                        = $_FILES[$this->inputFileName]['name'][$key];
                    $this->files[$this->inputFileName]['type'] 
                        = (isset($_FILES[$this->inputFileName]['type'][$key]) 
                            ? $_FILES[$this->inputFileName]['type'][$key] : null);
                    $this->files[$this->inputFileName]['tmp_name'] 
                        = (isset($_FILES[$this->inputFileName]['tmp_name'][$key]) 
                            ? $_FILES[$this->inputFileName]['tmp_name'][$key] : null);
                    $this->files[$this->inputFileName]['error'] 
                        = (isset($_FILES[$this->inputFileName]['error'][$key]) 
                            ? $_FILES[$this->inputFileName]['error'][$key] : 4);
                    $this->files[$this->inputFileName]['size'] 
                        = (isset($_FILES[$this->inputFileName]['size'][$key]) 
                            ? $_FILES[$this->inputFileName]['size'][$key] : 0);

                    $result = $this->uploadSingleFile();

                    if ($result == false && $this->stopOnFailedUpload === true) {
                        // it was set to sop on failed to upload multiple file.
                        // return false.
                        unset($result);
                        return false;
                    }
                } // endforeach;
                unset($key, $value);
            } else {
                // if single file upload.
                $this->files[$this->inputFileName] = $_FILES[$this->inputFileName];
                $this->files[$this->inputFileName]['input_file_key'] = 0;

                $result = $this->uploadSingleFile();
            }
        }

        if (isset($result) && $result == false 
            && $this->stopOnFailedUpload === true
        ) {
            // there is at lease one upload error and it was set to stop on error.
            unset($result);
            $this->clearUploadedAtTemp();
            return false;
        } elseif (count($this->errorMessages) > 0 
            && $this->stopOnFailedUpload === true
        ) {
            // there is at lease one upload error and it was set to stop on error.
            unset($result);
            $this->clearUploadedAtTemp();
            return false;
        }

        return $this->moveUploadedFiles();
    }

    /**
     * Start upload process for single file.<br> Even upload multiple file will
     * call to this method because it will be re-format the uploaded files
     * property to become a single file and then call this.
     *
     * @return boolean Return true on success, false for otherwise.
     */
    protected function uploadSingleFile()
    {
        // check if there is error while uploading from error array key.
        if (is_array($this->files[$this->inputFileName]) 
            && array_key_exists('error', $this->files[$this->inputFileName]) 
            && $this->files[$this->inputFileName]['error'] != 0
        ) {
            switch ($this->files[$this->inputFileName]['error']) {
            case 1:
                $this->setErrorMessage(
                    sprintf(
                        static::__(
                            'The uploaded file exceeds the max file size directive.
                            (%s &gt; %s).'
                        ), $this->files[$this->inputFileName]['size'], ini_get(
                            'upload_max_filesize'
                        )
                    ),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    $this->files[$this->inputFileName]['size']
                    . ' &gt; ' . ini_get('upload_max_filesize'),
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 2:
                $this->setErrorMessage(
                    static::__(
                        'The uploaded file exceeds the MAX_FILE_SIZE directive
                        that was specified in the HTML form.'
                    ),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 3:
                $this->setErrorMessage(
                    static::__('The uploaded file was only partially uploaded.'),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 4:
                $this->setErrorMessage(
                    static::__('You did not upload the file.'),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 6:
                $this->setErrorMessage(
                    static::__('Missing a temporary folder.'),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 7:
                $this->setErrorMessage(
                    static::__('Failed to write file to disk.'),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            case 8:
                $this->setErrorMessage(
                    static::__('A PHP extension stopped the file upload.'),
                    'RDU_' . $this->files[$this->inputFileName]['error'],
                    '',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            }
        }

        // validate that there is file upload.
        if (empty($this->files[$this->inputFileName])
            || (is_array($this->files[$this->inputFileName])
            && array_key_exists('name', $this->files[$this->inputFileName])
            && $this->files[$this->inputFileName]['name'] == null)
            || (is_array($this->files[$this->inputFileName])
            && array_key_exists('tmp_name', $this->files[$this->inputFileName])
            && $this->files[$this->inputFileName]['tmp_name'] == null)
        ) {
            $this->setErrorMessage(
                static::__('You did not upload the file.'),
                'RDU_4',
                '',
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['size'],
                $this->files[$this->inputFileName]['type']
            );
            return false;
        }

        // Comment on request of of Amit to not check mime type
        // validate allowed extension and its mime types.
        // $result = $this->validateExtensionAndMimeType();
        // if ($result !== true) {
        //     return false;
        // }
        // unset($result);

        // validate max file size.
        $result = $this->validateFileSize();
        if ($result !== true) {
            return false;
        }
        unset($result);

        // validate max image dimension.
        $result = $this->validateImageDimension();
        if ($result !== true) {
            return false;
        }
        unset($result);

        // security scan.
        if ($this->securityScan === true) {
            $result = $this->securityScan();
            if ($result !== true) {
                return false;
            }
            unset($result);
        }

        // set new file name (in case that it was not set) and check for
        // reserved file name.
        $tmpNewFileName = $this->newFileName;
        $this->setNewFileName();

        // check for safe web file name if this option was set to true.
        if ($this->webSafeFileName === true) {
            $this->setWebSafeFileName();
        }

        // now, it should all passed validation. add the uploaded file to move
        // uploaded queue in case that it is upload multiple file and has option
        // to stop on error. get the uploaded file extension.
        $fileNameExplode = explode('.', $this->files[$this->inputFileName]['name']);
        $fileExtension = null;
        if (is_array($fileNameExplode)) {
            $fileExtension = '.' . $fileNameExplode[count($fileNameExplode) - 1];
        }
        unset($fileNameExplode);
        // add to queue.
        $this->moveUploadedQueue = array_merge(
            $this->moveUploadedQueue,
            array(
                $this->files[$this->inputFileName]['input_file_key'] => array(
                    'name' => $this->files[$this->inputFileName]['name'],
                    'tmp_name' => $this->files[$this->inputFileName]['tmp_name'],
                    'new_name' => $this->newFileName . $fileExtension,
                ),
            )
        );
        // restore temp of new file name to ready for next loop of upload multiple.
        $this->newFileName = $tmpNewFileName;
        unset($fileExtension, $tmpNewFileName);

        // done.
        return true;
    } // uploadSingleFile

    /**
     * Validate allowed extension and its mime types (if all of these was set).
     *
     * @return boolean Return true on success, false on failure.
     */
    protected function validateExtensionAndMimeType()
    {
        if ($this->allowedFileExt == null
            && ($this->fileExtMimeTypes == null
            || empty($this->fileExtMimeTypes))
        ) {
            // it is not set to limit uploaded file extensions and mime types.
            return true;
        }

        // get only file extension of uploaded file.
        $fileNameExplode = explode('.', $this->files[$this->inputFileName]['name']);
        if (!is_array($fileNameExplode)) {
            unset($fileNameExplode);
            $this->setErrorMessage(
                sprintf(
                    static::__(
                        'Unable to validate extension for the file %s.'
                    ), $this->files[$this->inputFileName]['name']
                ),
                'RDU_UNABLE_VALIDATE_EXT',
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['size'],
                $this->files[$this->inputFileName]['type']
            );
            return false;
        }
        $fileExtension = strtolower($fileNameExplode[count($fileNameExplode) - 1]);
        unset($fileNameExplode);

        // validate allowed extensions.
        if (is_array($this->allowedFileExt) 
            && !in_array($fileExtension, $this->allowedFileExt)
        ) {
            unset($fileExtension);
            $this->setErrorMessage(
                sprintf(
                    static::__(
                        'You have uploaded the file that is not allowed extension.
                        (%s)'
                    ), $this->files[$this->inputFileName]['name']
                ),
                'RDU_NOT_ALLOW_EXT',
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['size'],
                $this->files[$this->inputFileName]['type']
            );
            return false;
        }

        // validate allowed mime types that match uploaded file's extension.
        if (is_array($this->fileExtMimeTypes) && !empty($this->fileExtMimeTypes)) {
            if (!array_key_exists($fileExtension, $this->fileExtMimeTypes)) {
                unset($fileExtension);
                $this->setErrorMessage(
                    sprintf(
                        static::__(
                            'Unable to validate the file extension and mime type.
                            (%s). This file extension was not set in the &quot;
                            file_extensions_mime_types&quot; property.'
                        ), $this->files[$this->inputFileName]['name']
                    ),
                    'RDU_UNABLE_VALIDATE_EXT_AND_MIME',
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['name'],
                    $this->files[$this->inputFileName]['size'],
                    $this->files[$this->inputFileName]['type']
                );
                return false;
            } else {
                $fileInfo = new \finfo();
                $fileMimetype = $fileInfo->file(
                    $this->files[$this->inputFileName]['tmp_name'], 
                    FILEINFO_MIME_TYPE
                );
                if (is_array($this->fileExtMimeTypes[$fileExtension]) && !in_array(
                    strtolower($fileMimetype), array_map(
                        'strtolower', $this->fileExtMimeTypes[$fileExtension]
                    )
                )
                ) {
                    unset($fileExtension, $fileInfo);
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'The uploaded file has invalid mime type. (%s : %s).'
                            ), 
                            $this->files[$this->inputFileName]['name'], 
                            $fileMimetype
                        ),
                        'RDU_INVALID_MIME',
                        $this->files[$this->inputFileName]['name']
                        . ' : ' . $fileMimetype,
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $fileMimetype
                    );
                    unset($fileMimetype);
                    return false;
                } elseif (!is_array($this->fileExtMimeTypes[$fileExtension])) {
                    unset($fileExtension, $fileMimetype, $fileInfo);
                    $this->setErrorMessage(
                        static::__(
                            'Unable to validate mime type.
                            The format of &quot;file_extensions_mime_types&quot;
                            property is incorrect.'
                        ),
                        'RDU_UNABLE_VALIDATE_MIME',
                        '',
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }
                unset($fileMimetype, $fileInfo);
            }
        }

        unset($fileExtension);
        return true;
    } // validateExtensionAndMimeType

    /**
     * Validate uploaded file must not exceed max file size limit. (if max file
     * size limit was set).
     *
     * @return boolean Return true on success, false on failure.
     */
    protected function validateFileSize()
    {
        if (!is_numeric($this->maxFileSize) && !is_int($this->maxFileSize)) {
            // it is not set max file size limitation.
            return true;
        }

        if (is_array($this->files[$this->inputFileName])
            && array_key_exists('size', $this->files[$this->inputFileName])
            && $this->files[$this->inputFileName]['size'] > $this->maxFileSize
        ) {
            $this->setErrorMessage(
                sprintf(
                    static::__(
                        'The uploaded file exceeds limit. (%s &gt; %s).'
                    ), $this->files[$this->inputFileName]['size'], $this->maxFileSize
                ),
                'RDU_1',
                $this->files[$this->inputFileName]['size'] . ' &gt; '
                . $this->maxFileSize,
                $this->files[$this->inputFileName]['name'],
                $this->files[$this->inputFileName]['size'],
                $this->files[$this->inputFileName]['type']
            );
            return false;
        } else {
            if (is_array($this->files[$this->inputFileName])
                && array_key_exists('tmp_name', $this->files[$this->inputFileName])
                && function_exists('filesize')
            ) {
                if (filesize(
                    $this->files[$this->inputFileName]['tmp_name']
                ) > $this->maxFileSize
                ) {
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'The file exceeds the max file size.(%s &gt; %s).'
                            ), $this->files[
                                $this->inputFileName
                            ]['size'], $this->maxFileSize
                        ),
                        'RDU_1',
                        $this->files[$this->inputFileName]['size'] . ' &gt; '
                        . $this->maxFileSize,
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }
            }
        }

        return true;
    } // validateFileSize

    /**
     * Validate image dimension if uploaded file is image and size is smaller
     * than specified `maxImageDimensions`.
     *
     * @return boolean Return true on success, false on failure. Also return
     * true on these conditions.
     * - No `maxImageDimensions` property set or it was set to empty array.
     * - The `getimagesize()` function return `false`. It means that this
     *   uploaded file is NOT an image. The developers need to validate it again
     *   one by one from uploaded files.
     * - Unable to find upload temp file. This is for make the upload progress
     *   passed and ready to move uploaded file. The developers need to validate
     *   it again one by one from uploaded files. Also return false on this
     *   condition.
     * - The `getimagesize()` function return 0 in width and height as noted in
     *   this page ( http://php.net/getimagesize ).
     */
    protected function validateImageDimension()
    {
        if (empty($this->maxImageDimensions)) {
            return true;
        }

        if (is_array($this->files[$this->inputFileName])
            && array_key_exists('tmp_name', $this->files[$this->inputFileName])
            && is_file($this->files[$this->inputFileName]['tmp_name'])
        ) {
            $image = getimagesize($this->files[$this->inputFileName]['tmp_name']);
            if ($image === false) {
                // this uploaded file is NOT an image. It is possible that user
                // upload mixed file types such as text with jpeg.
                return true;
            } elseif (is_array($image) && count($image) >= 2) {
                if ($image[0] <= $this->maxImageDimensions[0]
                    && $image[1] <= $this->maxImageDimensions[1]
                ) {
                    // if image dimensions are smaller or equal to max.
                    return true;
                } elseif ($image[0] <= 0
                    || $image[1] <= 0
                ) {
                    // Some formats may contain no image or may contain multiple
                    // images. In these cases, getimagesize() might not be able
                    // to properly determine the image size. getimagesize() will
                    // return zero for width and height in these cases.
                    // Reference: http://php.net/getimagesize
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'The uploaded image contain no image. (%s).'
                            ), $image[0] . 'x' . $image[1]
                        ),
                        'RDU_IMG_NO_OR_MULTIPLE_IMAGES',
                        $image[0] . 'x' . $image[1],
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                } else {
                    // if image dimensions are larger than max.
                    $this->setErrorMessage(
                        sprintf(
                            static::__(
                                'The uploaded image is of larger size. (%s &gt; %s).'
                            ), $image[0] . 'x' 
                                . $image[1], $this->maxImageDimensions[0] 
                                . 'x' . $this->maxImageDimensions[1]
                        ),
                        'RDU_IMG_DIMENSION_OVER_MAX',
                        $image[0] . 'x' . $image[1] . ' &gt; ' 
                            . $this->maxImageDimensions[0]
                        . 'x' . $this->maxImageDimensions[1],
                        $this->files[$this->inputFileName]['name'],
                        $this->files[$this->inputFileName]['size'],
                        $this->files[$this->inputFileName]['type']
                    );
                    return false;
                }
            } else {
                // this uploaded file is NOT an image (return array does not
                // meet requirement). It is possible that user upload mixed file
                // types such as text with jpeg.
                return true;
            }
        }

        return true;
    } // validateImageDimension

    /**
     * Validate that these options properties has properly set in the correct
     * type.
     *
     * @return Exception 
     */
    protected function validateOptionsProperties()
    {
        if (!is_array($this->allowedFileExt) && $this->allowedFileExt != null) {
            $this->allowedFileExt = array($this->allowedFileExt);
        }

        if (!is_array($this->fileExtMimeTypes) && $this->fileExtMimeTypes != null) {
            $this->fileExtMimeTypes = null;
        }

        if (is_numeric($this->maxFileSize) && !is_int($this->maxFileSize)) {
            $this->maxFileSize = intval($this->maxFileSize);
        } elseif (!is_int($this->maxFileSize) && $this->maxFileSize != null) {
            $this->maxFileSize = null;
        }

        if (!is_array($this->maxImageDimensions)
            || (is_array($this->maxImageDimensions)
            && (count($this->maxImageDimensions) != 2
            || (count($this->maxImageDimensions) == 2
            && count($this->maxImageDimensions) != count(
                $this->maxImageDimensions, COUNT_RECURSIVE
            ))))
        ) {
            $this->maxImageDimensions = array();
        } else {
            if (!is_int($this->maxImageDimensions[0])
                || !is_int($this->maxImageDimensions[1])
            ) {
                $this->maxImageDimensions = array();
            }
        }

        if (empty($this->moveUploadedTo)) {
            trigger_error(
                static::__(
                    'The move_uploaded_to property was not set'
                ), E_USER_ERROR
            );
        }

        if (!is_string($this->newFileName) && $this->newFileName != null) {
            $this->newFileName = null;
        }

        if (!is_bool($this->overwrite)) {
            $this->overwrite = false;
        }

        if (!is_bool($this->webSafeFileName)) {
            $this->webSafeFileName = true;
        }

        if (!is_bool($this->securityScan)) {
            $this->securityScan = false;
        }

        if (!is_bool($this->stopOnFailedUpload)) {
            $this->stopOnFailedUpload = true;
        }
    } // validateOptionsProperties

}
