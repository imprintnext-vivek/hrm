<?php
/**
 * Operation file system on various endpoints
 *
 * PHP version 5.6
 *
 * @category  Filesystems
 * @package   Filesystems
 * @author    Radhanatha Mohapatra <radhanatham@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */

 /**
  * Filesystems Controller
  *
  * @category Class
  * @package  Filesystems
  * @author   Radhanatha Mohapatra <radhanatham@riaxe.com>
  * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
  * @link     http://inkxe-v10.inkxe.io/xetool/admin
  */
class Filesystems
{
    /**
     * GET:  Determine if a file or directory exists.
     *
     * @param $path This is a string path current file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * GET:  Get the contents of a file.
     *
     * @param $path This is a string path current file path
     * @param $lock path status
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function get($path, $lock = false)
    {
        if ($this->isFile($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }
    }
     
    /**
     * GET: Get contents of a file with shared access.
     *
     * @param $path This is a string path current file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }
    
    /**
     * GET: Get the returned value of a file.
     *
     * @param $path This is a string path current file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return mixed
     */
    public function getRequire($path)
    {
        if ($this->isFile($path)) {
            return include $path;
        }
    }

    /**
     * GET: Get the returned value of a file.
     *
     * @param $file This is a string current file
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return mixed
     */
    public function requireOnce($file)
    {
        include_once $file;
    }

    /**
     * GET: Get the MD5 hash of the file at the given path.
     *
     * @param $path This is a string current path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return mixed
     */
    public function hash($path)
    {
        return md5_file($path);
    }

    /**
     * POST: Write the contents of a file.
     *
     * @param $path     This is a string current file
     * @param $contents The string of file contents
     * @param $lock     boolean path status
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return int
     */
    public function put($path, $contents, $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * POST: Prepend to a file.
     *
     * @param $path This is a string current file
     * @param $data The string of file data
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return int
     */
    public function prepend($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * POST: Append to a file.
     *
     * @param $path This is a string current file
     * @param $data The string of file data
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return int
     */
    public function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * GET: Get or set UNIX mode of a file or directory.
     *
     * @param $path This is a string current file
     * @param $mode The string of file permission code
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return mixed
     */
    public function chmod($path, $mode = null)
    {
        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * POST: Delete the file at a given path.
     *
     * @param $paths This is a string/array current file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $success = false;
                }
            } catch (\ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * GET: Move a file to a new location.
     *
     * @param $path   This is a string current file path
     * @param $target This is a string target file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function move($path, $target)
    {
        return rename($path, $target);
    }

    /**
     * GET: Copy a file to a new location.
     *
     * @param $path   This is a string current file path
     * @param $target This is a string target file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function copy($path, $target)
    {
        return copy($path, $target);
    }

    /**
     * GET: Create a hard link to the target file or directory.
     *
     * @param $target This is a string target file path
     * @param $link   This is a string link
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return void
     */
    public function link($target, $link)
    {
        if (!windows_os()) {
            return symlink($target, $link);
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    /**
     * GET: Extract the file name from a file path.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * GET: Extract the trailing name component from a file path.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * GET: Extract the parent directory from a file path.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * GET: Extract the file extension from a file path.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * GET: Get the file type of a given file.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * GET: Get the mime-type of a given file.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return string|false
     */
    public function mimeType($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * GET: Get the file size of a given file.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return int
     */
    public function size($path)
    {
        return filesize($path);
    }
    
    /**
     * GET: Get the file's last modification time.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * GET: Determine if the given path is a directory.
     *
     * @param $directory This is a string directory
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * GET: Determine if the given path is readable.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function isReadable($path)
    {
        return is_readable($path);
    }

    /**
     * GET: Determine if the given path is writable.
     *
     * @param $path This is a string file path
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }
    
    /**
     * GET: Determine if the given path is a file.
     *
     * @param $file This is a string file name
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function isFile($file)
    {
        return is_file($file);
    }

    /**
     * GET: Find path names matching a given pattern.
     *
     * @param $pattern This is a string pattern
     * @param $flags   This is a integer 
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return array
     */
    public function glob($pattern, $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * GET: Get an array of all files in a directory.
     *
     * @param $directory This is a string directory
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return array
     */
    public function files($directory)
    {
        $glob = glob($directory . DIRECTORY_SEPARATOR . '*');

        if ($glob === false) {
            return [];
        }

        // To get the appropriate files, we'll simply glob the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter(
            $glob, function ($file) {
                return filetype($file) == 'file';
            }
        );
    }

    /**
     * GET: Get all of the files from the given directory (recursive).
     *
     * @param $directory This is a string directory
     * @param $hidden    This is a boolean status
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return array
     */
    public function allFiles($directory, $hidden = false)
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory),
            false
        );
    }

    /**
     * GET: Get all of the directories within a given directory.
     *
     * @param $directory This is a string directory
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return array
     */
    public function directories($directory)
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0) as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * POST: Create a directory.
     *
     * @param $path      This is a string path
     * @param $mode      This is a integer file status mode
     * @param $recursive This is a boolean
     * @param $force     This is a boolean
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, 
        $force = false
    ) {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * POST:  Move a directory.
     *
     * @param $from      This is a string from path
     * @param $to        This is a string to path
     * @param $overwrite This is a boolean
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return bool
     */
    public function moveDirectory($from, $to, $overwrite = false)
    {
        if ($overwrite && $this->isDirectory($to)) {
            if (!$this->deleteDirectory($to)) {
                return false;
            }
        }

        return @rename($from, $to) === true;
    }

    /**
     * POST:  Copy a directory from one location to another.
     *
     * @param $directory   This is a string path directory
     * @param $destination This is a string to file path destination
     * @param $options     This is a integer
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function copyDirectory($directory, $destination, $options = null)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: \FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        if (!$this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new \FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            // As we spin through items, we will check to see 
            //if the current file is actually
            // a directory or a file.
            //When it is actually a directory we will need to call back into 
            //this function recursively to keep copying these nested folders.
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!$this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            } else {
                // If the current items is just a regular file,
                // we will just copy this to the new
                // location and keep looping. 
                // If for some reason the copy fails we'll bail out
                // and return false,
                // so the developer is aware that the copy process failed.
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * GET:  Recursively delete a directory.
     * The directory itself may be optionally preserved.
     *
     * @param $directory This is a string path directory
     * @param $preserve  This is a boolean status
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function deleteDirectory($directory, $preserve = false)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $items = new \FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                // If the item is just a file, 
                // we can go ahead and delete it since we're
                // just looping through and waxing all of the files in this directory
                // and calling directories recursively, so we delete the real path.
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * GET: Empty the specified directory of all files and folders.
     *
     * @param $directory This is a string path directory
     * 
     * @author radhanatham@riaxe.com
     * @date   03 Jan 2020
     * @return boolean
     */
    public function cleanDirectory($directory)
    {
        return $this->deleteDirectory($directory, true);
    }
}
