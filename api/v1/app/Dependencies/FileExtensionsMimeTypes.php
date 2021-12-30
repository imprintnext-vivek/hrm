<?php
/**
 * Default file extensions and its mime types To create your own extension =>
 * mime types, please write all keys and values in lower case only.
 *
 * PHP version 5.6
 *
 * @category  MIME_Types
 * @package   Configuration
 * @author    Tanmaya Patra <tanmayap@riaxe.com>
 * @copyright 2019-2020 Riaxe Systems
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://inkxe-v10.inkxe.io/xetool/admin
 */

return array(
    '7z' => array('application/x-7z-compressed'),
    'aac' => array('audio/aac', 'audio/aacp', 'audio/x-aac'),
    'avi' => array('video/x-msvideo'),
    'glb' => array('application/octet-stream'),
    'gltf' => array('text/plain'),
    'bmp' => array('image/bmp', 'image/x-windows-bmp','image/x-ms-bmp'),
    'css' => array('text/css'),
    'csv' => array(
        'application/csv',
        'application/excel',
        'application/vnd.ms-excel',
        'application/vnd.msexcel',
        'application/x-csv',
        'text/comma-separated-values',
        'text/csv',
        'text/x-comma-separated-values',
        'text/x-csv',
        'text/plain',
    ),
    'doc' => array('application/msword'),
    'docx' => array(
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ),
    'dvi' => array('application/x-dvi'),
    'flv' => array('video/x-flv'),
    'gif' => array('image/gif'),
    'gz' => array('application/x-gzip'),
    'h264' => array('video/h264'),
    'patt' => array('text/plain'),
    'htm' => array('text/html'),
    'html' => array('text/html'),
    'ico' => array('image/x-icon'),
    
    'ai' => array('application/postscript','application/pdf'),
    'dxf' => array('application/dxf', 'image/vnd.dwg', 'image/x-dwg','text/plain'),
    'eps' => array('application/postscript','image/x-eps'),
    'psd' => array('application/octet-stream','image/vnd.adobe.photoshop'),
    'tga' => array('image/x-tgaimage/x-tga','image/x-tga'),
    'cdr' => array('image/x-coreldraw', 'application/octet-stream'),

    'jpg' => array(
        'image/jpeg',
        'image/pjpeg',
        'image/x-citrix-jpeg',
    ),
    'jpe' => array(
        'image/jpeg',
        'image/pjpeg',
        'image/x-citrix-jpeg',
    ),
    'jpeg' => array('image/jpeg',
        'image/pjpeg',
        'image/x-citrix-jpeg'),
    'js' => array(
        'application/ecmascript',
        'application/javascript',
        'application/x-javascript',
        'text/ecmascript',
        'text/javascript'
    ),
    'json' => array('application/json',
        'text/json'),
    'log' => array('text/plain', 'text/x-log'),
    'mid' => array(
        'application/midi',
        'application/x-midi',
        'audio/midi',
        'audio/x-mid',
        'audio/x-midi',
        'music/crescendo',
        'x-music/x-midi',
    ),
    'midi' => array(
        'application/midi',
        'application/x-midi',
        'audio/midi',
        'audio/x-mid',
        'audio/x-midi',
        'music/crescendo',
        'x-music/x-midi',
    ),
    'mov' => array('video/quicktime'),
    'mp3' => array(
        'audio/mp3',
        'audio/mpeg',
        'audio/mpeg3',
        'audio/mpg',
        'audio/x-mpeg-3',
    ),
    'mp4' => array(
        'application/mp4',
        'video/mp4',
    ),
    'mp4a' => array('audio/mp4'),
    'mpe' => array('video/mpeg'),
    'mpeg' => array('video/mpeg'),
    'mpg' => array('video/mpeg'),
    'obj' => array('text/plain'),
    'otf' => array(
        'application/vnd.ms-opentype',
    ),
    'pdf' => array('application/pdf'),
    'png' => array(
        'image/png',
        'image/x-citrix-png',
        'image/x-png',
    ),
    'ppt' => array(
        'application/powerpoint',
        'application/vnd.ms-powerpoint',
    ),
    'pptx' => array(
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ),
    'rar' => array('application/x-rar-compressed'),
    'shtml' => array(
        'text/html',
        'text/x-server-parsed-html',
    ),
    'svg' => array('image/svg+xml', 'image/svg'),
    'tar' => array('application/x-tar'),
    'text' => array('text/plain'),
    'tgz' => array(
        'application/x-gzip-compressed',
        'application/x-tar',
    ),
    'tif' => array(
        'image/tiff',
        'image/x-tiff',
    ),
    'tiff' => array(
        'image/tiff',
        'image/x-tiff',
    ),
    'ttf' => array(
        'application/x-font-ttf',
        'font/sfnt'
    ),
    'torrent' => array('application/x-bittorrent'),
    'txt' => array('text/plain'),
    'wav' => array(
        'audio/wav',
        'audio/wave',
        'audio/x-wav',
    ),
    'wbmp' => array('image/vnd.wap.wbmp'),
    'weba' => array('audio/webm'),
    'webm' => array(
        'audio/webm',
        'video/webm',
    ),
    'xls' => array(
        'application/excel',
        'application/msexcel',
        'application/vnd.ms-excel',
    ),
    'xlsx' => array(
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ),
    'xml' => array('text/xml'),
    'xsl' => array('text/xml'),
    'zip' => array(
        'application/x-zip',
        'application/x-zip-compressed',
        'application/zip',
    ),
);