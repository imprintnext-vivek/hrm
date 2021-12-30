<?php
/**
 * Operation zip file system on various endpoints
 *
 * PHP version 5.6
 *
 * @category  Zipper
 * @package   Zipper
 * @author    Radhanatha Mohapatra <radhanatham@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */

namespace App\Dependencies;

require __DIR__ . DIRECTORY_SEPARATOR . 'Filesystems.php';
use \Exception;
use \ZipArchive;

 /**
  * Filesystems Zipper
  *
  * @category Class
  * @package  Zipper
  * @author   Radhanatha Mohapatra <radhanatham@riaxe.com>
  * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
  * @link     http://inkxe-v10.inkxe.io/xetool/admin
  */
class Zipper
{
    /**
     * String The archive cuuernt zip object
     */
    public $archive;

    /**
     * Check zip extension enable or not
     * Call PHP zip archive class  
     * 
     * @param $archive The is archive is string
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return \Zipper
     **/
    public function __construct($archive = null)
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception(
                'Error: Your PHP version is not compiled with zip support'
            );
        }
        $this->archive = $archive ? $archive : new ZipArchive();
    }

    /**
     * Create Zip file on corresponding folder
     * 
     * @param $filePath The string filePath is zip file path
     * @param $create   The boolean create is a status
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function make($filePath, $create = true)
    {
        $zipSatus = $this->archive->open(
            $filePath, (
            $create ? ZipArchive::CREATE : null)
        );
        if ($zipSatus !== true) {
            throw new Exception(
                "Error: Failed to open $filePath! Error: " . $this->getErrorMessage(
                    $zipSatus
                )
            );
        }
        return $zipSatus;
    }

    /**
     * Add empty directory on current zip file
     * 
     * @param $dirName The string dirName is zip file directroy name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function addEmptyDir($dirName)
    {
        $this->archive->addEmptyDir($dirName);
    }

    /**
     * Add a file to the opened Archive
     * 
     * @param $pathToFile    The string pathToFile
     * @param $pathInArchive The string pathInArchive
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function add($pathToFile, $pathInArchive = null)
    {
        $this->archive->addFile($pathToFile, $pathInArchive);
    }

    /**
     * Add prticular file extension in zip
     * 
     * @param $pathExtension The string zip path with file extension
     * @param $options       The array zip path remove path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function addGlob($pathExtension, $options = null)
    {
        $this->archive->addGlob($pathExtension, GLOB_BRACE, $options);
    }

    /**
     * Zip file closed
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function close()
    {
        @$this->archive->close();
    }

    /**
     * Add a file to the opened Archive using its contents
     * 
     * @param $name    The  name string and the file name
     * @param $content The content is string an file contents
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function addFromString($name, $content)
    {
        $this->archive->addFromString($name, $content);
    }

    /**
     * Remove a file permanently from the Archive
     * 
     * @param $pathInArchive The pathInArchive string and the file name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function removeFile($pathInArchive)
    {
        $this->archive->deleteName($pathInArchive);
    }

    /**
     * Get the content of a file
     * 
     * @param $pathInArchive The pathInArchive string and the file name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     **/
    public function getFileContent($pathInArchive)
    {
        return $this->archive->getFromName($pathInArchive);
    }

    /**
     * Get the stream of a file
     * 
     * @param $pathInArchive The pathInArchive string and the file name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return mixed
     **/
    public function getFileStream($pathInArchive)
    {
        return $this->archive->getStream($pathInArchive);
    }

    /**
     * Checks whether the file is in the archive
     * 
     * @param $fileInArchive The fileInArchive string and the file name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function fileExists($fileInArchive)
    {
        return $this->archive->locateName($fileInArchive) !== false;
    }

    /**
     * Sets the password to be used for decompressing
     * function named usePassword for clarity
     * 
     * @param $password The password is string
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     **/
    public function usePassword($password)
    {
        return $this->archive->setPassword($password);
    }

    /**
     * Returns the status of the archive as a string
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     **/
    public function getStatus()
    {
        return $this->archive->getStatusString();
    }

    /**
     * Returns the error message
     * 
     * @param $resultCode The resultCode integer
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     **/
    private function getErrorMessage($resultCode)
    {
        switch ($resultCode) {
        case ZipArchive::ER_EXISTS:
            return 'ZipArchive::ER_EXISTS - File already exists.';
        case ZipArchive::ER_INCONS:
            return 'ZipArchive::ER_INCONS - Zip archive inconsistent.';
        case ZipArchive::ER_MEMORY:
            return 'ZipArchive::ER_MEMORY - Malloc failure.';
        case ZipArchive::ER_NOENT:
            return 'ZipArchive::ER_NOENT - No such file.';
        case ZipArchive::ER_NOZIP:
            return 'ZipArchive::ER_NOZIP - Not a zip archive.';
        case ZipArchive::ER_OPEN:
            return 'ZipArchive::ER_OPEN - Can\'t open file.';
        case ZipArchive::ER_READ:
            return 'ZipArchive::ER_READ - Read error.';
        case ZipArchive::ER_SEEK:
            return 'ZipArchive::ER_SEEK - Seek error.';
        default:
            return "An unknown error [$resultCode] has occurred.";
        }
    }

    /**
     * Create a new zip archive or open an existing one
     * 
     * @param $pathToFile The pathToFile is a string
     * 
     * @throws \Exception
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return $this
     **/
    public function zip($pathToFile)
    {
        $this->make($pathToFile);

        return $this;
    }

    /**
     * Create a new phar file or open one
     * 
     * @param $pathToFile The pathToFile is a string
     * 
     * @throws \Exception
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return $this
     **/
    public function phar($pathToFile)
    {
        $this->make($pathToFile, 'phar');

        return $this;
    }

    /**
     * Create a new rar file or open one
     * 
     * @param $pathToFile The pathToFile is a string
     * 
     * @throws \Exception
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return $this
     **/
    public function rar($pathToFile)
    {
        $this->make($pathToFile, 'rar');

        return $this;
    }

    /**
     * Extracts the opened zip archive to the specified location <br/>
     * you can provide an array of files and folders 
     * and define if they should be a white list
     * or a black list to extract. 
     * By default this method compares file names using "string starts with" logic
     *
     * @param $path string The path to extract to
     *
     * @throws \Exception
     * 
     * @return string
     **/
    public function extractTo($path)
    {
        $this->archive->extractTo($path);
    }
}
