<?php
/**
 * ImageManipulator
 *
 * Copyright(c) 2013 Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 *
 * MIT Licensed
 */

/**
 * ImageManipulator class
 *
 * @author Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 */
class ImageManipulator
{
    const MAX_IMAGE_WIDTH  = 2048;
    const MAX_IMAGE_HEIGHT = 2048;

    /**
     * @var Imagick
     */
    private $image;

    /**
     * @var Imagick
     */
    private $imagick;

    /**
     * @var SplFileInfo
     */
    private $sourceFile;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * Array of supported target image output formats
     *
     * @var array
     */
    private $formatList = array('jpeg', 'png');

    /**
     * Constructor
     *
     * @param Imagick     $imagick    Instance of Imagick
     * @param SplFileInfo $sourceFile SplFileInfo instance of the source file (to be converted)
     */
    public function __construct(Imagick $imagick, SplFileInfo $sourceFile)
    {
        $this->imagick    = $imagick;
        $this->sourceFile = $sourceFile;

        $this->loadFile($this->sourceFile);
    }

    /**
     * Return an array of supported target image output formats
     *
     * @return array
     */
    public function getFormatList()
    {
        return $this->formatList;
    }

    /**
     * Return an error message (contains last error)
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Convert sourceFile to a given output format
     *
     * @param  string $toFormat Format on the output image
     *
     * @return boolean
     */
    public function convertTo($toFormat)
    {
        if ( ! $this->checkConversionFormat($toFormat)) {
            return false;
        }

        try {
            $this->imagick->setImageFormat($toFormat);
        } catch (Exception $e) {
            $this->errorMessage = sprintf('Failed to convert to "%s" or this is an unknown format. Available formats are: "%s". Error message is "%s"',
                $toFormat,
                implode(", ", $this->getFormatList()),
                $e->getMessage()
            );

            return false;
        }

        return true;
    }

    /**
     * Resize sourceFile using given width and height
     *
     * @param  integer $width
     * @param  integer $height
     *
     * @return boolean
     */
    public function resizeTo($width, $height)
    {
        if ( ! $this->validateDimension($width, $height)) {
            return false;
        }

        try {
            $this->imagick->thumbnailImage($width, $height,  true);
        } catch (Exception $e) {
            $this->errorMessage = sprintf('Failed to resize the image to "%s" x "%s". Error message is "%s"',
                $width,
                $height,
                $e->getMessage()
            );

            return false;
        }

        return true;
    }

    /**
     * Return an image (in its current state)
     *
     * @return Imagick|false An instance of Imagick which contains an associated image, or 'false' in case of error
     */
    public function getImage()
    {
        try {
            $image = $this->imagick->getImage();
        } catch (Exception $e) {
            $this->errorMessage = sprintf('Failed to get the image from Imagick. Error message is "%s"',
                $e->getMessage()
            );

            return false;
        }

        return $image;
    }

    /**
     * Clear and destroy Imagick instance
     */
    public function clear()
    {
        try {
            $this->imagick->clear();
            $this->imagick->destroy();
        } catch (Exception $e) {

        }
    }

    /**
     * Validate if given width and height are not exceed allowed maxim values
     *
     * @param  integer $width
     * @param  integer $height
     *
     * @return boolean
     */
    private function validateDimension($width, $height) {
        if ($width > self::MAX_IMAGE_WIDTH || $height > self::MAX_IMAGE_HEIGHT) {
            $this->errorMessage = sprintf('Maxim allowed width is "%s" and maximum allowed height is "%s". Yours are "%s" and "%s"',
                self::MAX_IMAGE_WIDTH,
                self::MAX_IMAGE_HEIGHT,
                $width,
                $height
            );

            return false;
        }

        return true;
    }

    /**
     * Check given format to define if it's supported to be converted
     *
     * @param  string $toFormat
     *
     * @return boolean
     */
    private function checkConversionFormat($toFormat)
    {
        if ( ! in_array($toFormat, $this->formatList)) {
            $this->errorMessage = sprintf('"%s" is an unknown format. Available formats are: "%s"',
                $toFormat,
                implode(", ", $this->getFormatList())
            );

            return false;
        }

        return true;
    }

    /**
     * Load a given file into an instance of Imagick
     *
     * @param  SplFileInfo $file
     *
     * @return boolean
     */
    private function loadFile(SplFileInfo $file)
    {
        $filePath = $file->getPath() .'./'. $file->getFilename();

        try {
            $this->imagick->readImage($filePath);
        } catch (Exception $e) {
            $this->errorMessage = sprintf('Failed to load the image to Imagick. Error message is "%s"',
                $e->getMessage()
            );

            return false;
        }

        return true;
    }
}
