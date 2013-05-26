<?php
/**
 * FileUploader
 *
 * Copyright(c) 2013 Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 *
 * MIT Licensed
 */

/**
 * FileUploader class
 *
 * @author Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 */
class FileUploader
{
    const UPLOAD_DIR    = 'uploads/';
    const MAX_FILE_SIZE = 77777;

    /**
     * Array of allowed to be uploaded file extensions
     *
     * @var array
     */
    private $allowedExtensionArray = array(".svg", ".png", ".jpg");

    /**
     * @var string
     */
    private $uploadError;

    /**
     * @var string
     */
    private $temporatyFileName;

    /**
     * @var string
     */
    private $realFileName;

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * @var integer
     */
    private $fileSize;

    /**
     * Constructor
     *
     * @param array $file Instance of array ($_FILES['fileToUpload'])
     */
    public function __construct($file)
    {
        if ( ! isset($file['tmp_name']) || ! isset($file['name'])) {
            die('FileUploader must have a file as a parameter');
        }

        $this->setFileInformation($file);
    }

    /**
     * Upload a file, making validation before moving the uploaded file into the upload destination
     *
     * @return boolean
     */
    public function upload()
    {
        if ( ! $this->validateUploadedFile()) {
            return false;
        }

        if ( ! move_uploaded_file($this->temporatyFileName, self::UPLOAD_DIR . $this->realFileName)) {
            $this->uploadError = "Something went wrong. Moving of the uploaded file has failed.";
            return false;
        }

        return true;
    }

    /**
     * Return an upload error
     *
     * @return string
     */
    public function getUploadError()
    {
        return $this->uploadError;
    }

    /**
     * Store the uploaded file information into the instance properties
     *
     * @param array $file
     */
    private function setFileInformation($file)
    {
        $this->temporatyFileName = trim($file['tmp_name']);
        $this->realFileName      = trim(strtolower($file['name']));
        $this->fileExtension     = strtolower(strrchr($this->realFileName,"."));
        $this->fileSize          = filesize($this->temporatyFileName);
    }

    /**
     * Validate a few conditions based on those file would be either accepted or declined of being uploaded
     *
     * @return boolean
     */
    private function validateUploadedFile()
    {
        if ( ! $this->getUploadDrectory() || ! $this->fileSizeIsValid() || ! $this->fileExtensionIsValid()) {
            return false;
        }

        if ( ! is_uploaded_file($this->temporatyFileName)) {
            $this->uploadError = "Something went wrong. Looks like uploaded is not a really uploaded one.";

            return false;
        }

        return true;
    }

    /**
     * Validate file extension
     *
     * @return boolean
     */
    private function fileExtensionIsValid() {
        if ( ! in_array($this->fileExtension, $this->allowedExtensionArray)) {
            $this->uploadError = sprintf(
                "File extension is wrong. '%s' is not allowed to be uploaded",
                $this->fileExtension
            );

            return false;
        }

        return true;
    }

    /**
     * Validate file size
     *
     * @return boolean
     */
    private function fileSizeIsValid() {
        if ($this->fileSize > self::MAX_FILE_SIZE) {
            $this->uploadError = sprintf(
                "The file size is invalid. The maximum file size is: %s , and your file was: %s",
                $this->getReadableFileSize(self::MAX_FILE_SIZE),
                $this->getReadableFileSize($this->fileSize)
            );

            return false;
        }

        return true;
    }

    /**
     * Generate a human readable file size representation
     *
     * @param  integer $size
     *
     * @return string Rounded and readable file size
     */
    private function getReadableFileSize($size) {
        $kb = 1024;
        $mb = $kb * $kb;
        $gb = $kb * $mb;
        $tb = $kb * $gb;

        switch ($size) {
            case $size < $kb:
                $readableFileSize = "$size Bytes";
                break;
            case $size < $mb:
                $roundedFileSize  = round($size / $kb, 2);
                $readableFileSize = "$roundedFileSize KB";
                break;
            case $size < $gb:
                $roundedFileSize  = round($size / $mb, 2);
                $readableFileSize = "$roundedFileSize MB";
                break;
            case $size < $tb:
                $roundedFileSize  = round($size / $gb, 2);
                $readableFileSize = "$roundedFileSize GB";
                break;
            case $size >= $tb:
                $roundedFileSize  = round($size / $tb, 2);
                $readableFileSize = "$roundedFileSize GB";
                break;
            default:
                $readableFileSize = "Something went wrong. It's impossible to find the file size.";
                break;
        }

        return $readableFileSize;
    }

    /**
     * Verify if upload directory is available for writing
     *
     * @return boolean
     */
    private function getUploadDrectory() {
        $handle = @opendir(self::UPLOAD_DIR);

        if ( ! $handle) {
            $this->uploadError = "Upload directory is not writable or does not exist";

            return false;
        }

        closedir($handle);

        return true;
    }
}


