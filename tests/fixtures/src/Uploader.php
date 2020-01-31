<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
namespace VanillaTests\Fixtures;

use Exception;
use Vanilla\UploadedFile;
use Gdn_Upload;

class Uploader {

    protected static $uploadsPath = PATH_ROOT.'/tests/cache/uploads';

    /**
     * Verify the uploads directory exists. Attempt to create it, if not.
     *
     * @throws Exception
     */
    protected static function ensureDirectory() {
        if (!file_exists(static::$uploadsPath)) {
            $result = mkdir (static::$uploadsPath , 0777, true);
            if (!$result) {
                throw new Exception('Unable to create uploads directory: '.self::$uploadsPath);
            }
        }
    }

    /**
     * Generate a valid, random filename for an upload.
     *
     * @return string
     */
    protected static function generateFilename() {
        do {
            $name = randomString(12);
            $path = static::$uploadsPath."/{$name}";
        } while (file_exists($path));
        return $path;
    }

    /**
     * Clear the uploads directory and reset the $_FILES global..
     */
    public static function resetUploads() {
        if (file_exists(self::$uploadsPath)) {
            $files = glob(self::$uploadsPath.'/*.*');
            array_walk($files, 'unlink');
        }

        $_FILES = [];
    }

    /**
     * Copy a file into the uploads directory and add its details to the current $_FILES superglobal.
     *
     * @param string $name A field name associated with this file upload.
     * @param string $file Path to the file.
     * @throws Exception if the file does not exist.
     * @throws Exception if the file is not actually a file (i.e. is a directory).
     * @throws Exception if the file is not readable.
     * @return UploadedFile
     */
    public static function uploadFile($name, $file) {
        if (!file_exists($file)) {
            throw new Exception("{$file} does not exist.");
        }
        if (!is_file($file)) {
            throw new Exception("{$file} is not a file.");
        }
        if (!is_readable($file)) {
            throw new Exception("{$file} could not be read.");
        }

        static::ensureDirectory();
        $destination = static::generateFilename();

        if (!copy($file, $destination)) {
            throw new Exception('Unable to copy file to destination: '.$destination);
        }

        $info = [
            'name' => basename($file),
            'type' => mime_content_type($file),
            'size' => filesize($file),
            'tmp_name' => $destination,
            'error' => UPLOAD_ERR_OK
        ];

        $_FILES[$name] = $info;

        $result = new UploadedFile(
            new Gdn_Upload(),
            $info['tmp_name'],
            $info['size'],
            $info['error'],
            $info['name'],
            $info['type']
        );
        return $result;
    }
}
