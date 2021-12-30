<?php
/**
 * Retrieve data stored in a TTF files 'name' table
 *
 * PHP version 5.6
 *
 * @category  Fonts
 * @package   Dependency
 * @author    Tanmaya <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 *
 * @todo: Make it Retrieve additional information from other tables
 */
namespace App\Dependencies;

error_reporting(1);

/**
 * Font Meta Class
 *
 * @category Fonts
 * @package  Dependency
 * @author   Tanmaya <tanmayap@riaxe.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://inkxe-v10.inkxe.io/xetool/admin
 */
class FontMeta
{
    /**
     * Variable $_dirRestriction
     * Restrict the resource pointer to this directory and above.
     * Change to 1 for to allow the class to look outside of it current directory
     *
     * @protected
     *
     * @var int
     */
    protected $_dirRestriction = 1;
    /**
     * Variable $_dirRestriction
     * Restrict the resource pointer to this directory and above.
     * Change to 1 for nested directories
     *
     * @protected
     *
     * @var int
     */
    protected $_recursive = 0;

    /**
     * Variable $fontsdir
     * This is to declare this variable as protected
     * don't edit this!!!
     *
     * @protected
     */
    protected $fontsdir;
    /**
     * Variable $filename
     * This is to declare this varable as protected
     * don't edit this!!!
     *
     * @protected
     */
    protected $filename;

    /**
     * Function setFontFile()
     *
     * @param $data data
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object reference to this
     **/
    public function setFontFile($data)
    {
        if ($this->_dirRestriction && preg_match('[\.\/|\.\.\/]', $data)) {
            $this->exitClass('Error: Directory restriction is enforced!');
        }

        $this->filename = $data;
        return $this;
    }
    /**
     * Function setFontsDir()
     *
     * @param $data data
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object referrence to this
     **/
    public function setFontsDir($data)
    {
        if ($this->_dirRestriction && preg_match('[\.\/|\.\.\/]', $data)) {
            $this->exitClass('Error: Directory restriction is enforced!');
        }

        $this->fontsdir = $data;
        return $this;
    }
    /**
     * Function readFontsDir()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return information contained in the TTF 'name' table of all fonts in a
     * directory
     **/
    public function readFontsDir()
    {
        if (empty($this->fontsdir)) {
            $this->exitClass(
                'Error: Fonts Directory has not been set with setFontsDir().'
            );
        }
        if (empty($this->backupDir)) {
            $this->backupDir = $this->fontsdir;
        }

        $this->array = array();
        $scanFontDir = dir($this->fontsdir);

        while (false !== ($afterRead = $scanFontDir->read())) {
            if ($afterRead != '.' && $afterRead != '..') {
                $afterRead = $this->fontsdir . $afterRead;
                if ($this->_recursive && is_dir($afterRead)) {
                    $this->setFontsDir($afterRead);
                    $this->array = array_merge($this->array, readFontsDir());
                } else if ($this->is_ttf($afterRead) === true) {
                    $this->setFontFile($afterRead);
                    $this->array[$afterRead] = $this->getFontInfo();
                }
            }
        }

        if (!empty($this->backupDir)) {
            $this->fontsdir = $this->backupDir;
        }

        $scanFontDir->close();
        return $this;
    }
    /**
     * Function setProtectedVar()
     *
     * DISABLED, NO REAL USE YET
     *
     * @param $var  var
     * @param $data data
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object referrence to this
     **/
    /*public function setProtectedVar($var, $data)
    {
        if ($var == 'filename') {
            $this->setFontFile($data);
        } else {
            //if (isset($var) && !empty($data))
            $this->$var = $data;
        }
        return $this;
    }*/

    /**
     * Function getFontInfo()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return information contained in the TTF 'name' table
     **/
    public function getFontInfo()
    {
        $fd = fopen($this->filename, "r");
        $this->text = fread($fd, filesize($this->filename));
        fclose($fd);

        $number_of_tables = hexdec(
            $this->dec2ord($this->text[4]) . $this->dec2ord($this->text[5])
        );

        for ($i = 0; $i < $number_of_tables; $i++) {
            $tag = $this->text[12 + $i * 16] . $this->text[12 + $i * 16 + 1] 
                . $this->text[12 + $i * 16 + 2] . $this->text[12 + $i * 16 + 3];

            if ($tag == 'name') {
                $this->ntOffset = hexdec(
                    $this->dec2ord($this->text[12 + $i * 16 + 8]) 
                        . $this->dec2ord($this->text[12 + $i * 16 + 8 + 1]) 
                        . $this->dec2ord($this->text[12 + $i * 16 + 8 + 2]) 
                        . $this->dec2ord($this->text[12 + $i * 16 + 8 + 3])
                );

                $offset_storage_dec = hexdec(
                    $this->dec2ord($this->text[$this->ntOffset + 4]) 
                        . $this->dec2ord($this->text[$this->ntOffset + 5])
                );
                $num_name_rec_dec = hexdec(
                    $this->dec2ord($this->text[$this->ntOffset + 2]) 
                        . $this->dec2ord($this->text[$this->ntOffset + 3])
                );
            }
        }

        $storage_dec = $offset_storage_dec + $this->ntOffset;
        $storage_hex = strtoupper(dechex($storage_dec));
        $font_tags = array();
        for ($j = 0; $j < $num_name_rec_dec; $j++) {
            $name_id_dec = hexdec(
                $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 6]) 
                    . $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 7])
            );
            $string_length_dec = hexdec(
                $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 8]) 
                    . $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 9])
            );
            $string_offset_dec = hexdec(
                $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 10]) 
                    . $this->dec2ord($this->text[$this->ntOffset + 6 + $j * 12 + 11])
            );

            if (!empty($name_id_dec) and empty($font_tags[$name_id_dec])) {
                for ($l = 0; $l < $string_length_dec; $l++) {
                    if (ord(
                        $this->text[$storage_dec + $string_offset_dec + $l]
                    ) == '0'
                    ) {
                        continue;
                    } else {
                        if (isset($this->text[$storage_dec + $string_offset_dec + $l]) 
                            && $this->text[$storage_dec + $string_offset_dec + $l] != ""
                        ) {
                            $font_tags[$name_id_dec] .= (
                                $this->text[$storage_dec + $string_offset_dec + $l]
                            );
                        }
                    }
                }
            }
        }
        return $font_tags;
    }

    /**
     * Function getCopyright()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return 'Copyright notice' contained in the TTF 'name' table at index 0
     **/
    public function getCopyright()
    {
        $this->info = $this->getFontInfo();
        return $this->info[0];
    }

    /**
     * Function getFontFamily()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return 'Font Family name' contained in the TTF 'name' table at index 1
     **/
    public function getFontFamily()
    {
        $this->info = $this->getFontInfo();
        return $this->info[1];
    }

    /**
     * Function getFontSubFamily()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return 'Font Subfamily name' contained in the TTF 'name' table at index 2
     **/
    public function getFontSubFamily()
    {
        $this->info = $this->getFontInfo();
        return $this->info[2];
    }

    /**
     * Function getFontId()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return 'Unique font identifier' contained in the TTF 'name' table at index 3
     **/
    public function getFontId()
    {
        $this->info = $this->getFontInfo();
        return $this->info[3];
    }

    /**
     * Function getFullFontName()
     *
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return 'Full font name' contained in the TTF 'name' table at index 4
     **/
    public function getFullFontName()
    {
        $this->info = $this->getFontInfo();
        return $this->info[4];
    }

    /**
     * Function dec2ord()
     * Used to lessen redundant calls to multiple functions.
     *
     * @param $dec dec
     * 
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object
     **/
    protected function dec2ord($dec)
    {
        return $this->dec2hex(ord($dec));
    }

    /**
     * Function dec2hex()
     * private function to perform Hexadecimal to decimal with proper padding
     *
     * @param $dec dec
     * 
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object
     **/
    protected function dec2hex($dec)
    {
        return str_repeat('0', 2 - strlen(($hex = strtoupper(dechex($dec))))) . $hex;
    }
    /**
     * Function dec2hex()
     * private function to perform Hexadecimal to decimal with proper padding
     *
     * @param $message message
     * 
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object
     **/
    protected function exitClass($message)
    {
        if (!empty($message)) {
            exit("Process exited");
        }
    }
    /**
     * Function dec2hex()
     * private helper function to test in the file in question is a ttf.
     *
     * @param $file file
     * 
     * @author tanmayap@riaxe.com
     * @date   18 Dec 2019
     * @return object
     **/
    protected function is_ttf($file)
    {
        $ext = explode('.', $file);
        $ext = $ext[count($ext) - 1];
        return preg_match("/ttf$/i", $ext) ? true : false;
    }

}
