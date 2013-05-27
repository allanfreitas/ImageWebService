<?php
/**
 * WebService
 *
 * Copyright(c) 2013 Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 *
 * MIT Licensed
 */

require_once('FileUploader.php');
require_once('ImageManipulator.php');

/**
 * WebService class
 *
 * @author Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 */
class WebService
{
    /**
     * Response to be sent back from the service. Depends on the action and its result could be different type
     *
     * @var mixed
     */
    private $responseBody;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $action;

    /**
     * Array of parameters which would be initialized using incoming parameters
     *
     * @var array
     */
    private $actionParameterList;

    /**
     * Array of default parameters for 'upload' action
     *
     * @var array
     */
    private $uploadParameterArray = array(
        'action'
    );

    /**
     * Array of default parameters for 'thumbnail' action
     *
     * @var array
     */
    private $thumbnailParameterArray = array(
        'action',
        'fileName',
        'format',
        'width',
        'height'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        if ( ! isset($_POST['action'])) {
            $this->sendErrorResponse($this->getXmlErrorMessage('$_POST[\'action\'] must be specified'));
        }

        if ( ! $this->parseParameterArray($_POST)) {
            die();
        }
    }

    /**
     * Return an XML wrapped string for the error response
     *
     * @param  string $message
     *
     * @return string
     */
    private function getXmlErrorMessage($message) {
        return
            "<response><error>"
            . $message
            . "</error></response>";
    }

    /**
     * Return an XML wrapped string for the success response
     *
     * @param  string $message
     *
     * @return string
     */
    private function getXmlSuccesMessage($message) {
        return
            "<response><message>"
            . $message
            . "</message></response>";
    }

    /**
     * Make an comparison of required parameters versus given parameters.
     * In case it's matched make initialization of $this->actionParameterList with given parameters
     *
     * @param  array $givenParameterList    Parameters coming from the request
     * @param  array $requiredParameterList Array of required parameters (for comparison)
     *
     * @return boolean
     */
    private function initializeActionParameterList($givenParameterList, $requiredParameterList)
    {
        if ( ! $this->validateGivenParameterListLength(sizeof($givenParameterList), sizeof($requiredParameterList), $requiredParameterList)) {
            return false;
        }

        foreach ($requiredParameterList as $parameterName) {
            if ( ! $this->validateRequiredParameter($parameterName, $givenParameterList, $requiredParameterList)) {
                return false;
            }
        }

        $this->actionParameterList = $givenParameterList;

        return true;
    }

    /**
     * Validate if a certain parameter exist in the given parameters list
     *
     * @param  string $requiredParameterName
     * @param  array $givenParameterList
     * @param  array $requiredParameterList Needs to be passed to simplify error message creation
     *
     * @return boolean
     */
    private function validateRequiredParameter($requiredParameterName, $givenParameterList, $requiredParameterList)
    {
        if ( ! isset($givenParameterList[$requiredParameterName])) {
            $errorMessage = sprintf(
                "Parameter '%s' for action '%s' must be defined. The whole list of required parameters is '%s'",
                $requiredParameterName,
                $this->action,
                implode(", ", $requiredParameterList)
            );

            $this->errorMessage = $this->getXmlErrorMessage($errorMessage);

            return false;
        }

        return true;
    }

    /**
     * Validate that length of the given parameters list matches with the length of the required parameters list
     *
     * @param  integer $givenParameterListLength
     * @param  integer $requiredParameterListLength
     *
     * @param  array $requiredParameterList Needs to be passed to simplify error message creation
     *
     * @return boolean
     */
    private function validateGivenParameterListLength($givenParameterListLength, $requiredParameterListLength, $requiredParameterList)
    {
        if ($givenParameterListLength !== $requiredParameterListLength) {
            $errorMessage = sprintf(
                "Action '%s' is expecting exactly '%s' parameters. '%s' are given. The whole list of required parameters is '%s'",
                $this->action,
                $requiredParameterListLength,
                $givenParameterListLength,
                implode(", ", $requiredParameterList)
            );

            $this->errorMessage = $this->getXmlErrorMessage($errorMessage);

            return false;
        }

        return true;
    }

    /**
     * Parse an array of given parameters
     *
     * @param  array $givenParameterList Given parameters array
     *
     * @return boolean
     */
    private function parseParameterArray($givenParameterList)
    {
        if ( ! isset($givenParameterList['action'])) {
            return false;
        }

        switch ($givenParameterList['action']) {
            case 'upload':
                $this->action = 'upload';

                if ( ! $this->initializeActionParameterList($givenParameterList, $this->uploadParameterArray)) {
                    $this->sendErrorResponse($this->errorMessage);

                    return false;
                }

                break;
            case 'thumbnail':
                $this->action = 'thumbnail';

                if ( ! $this->initializeActionParameterList($givenParameterList, $this->thumbnailParameterArray)) {
                    $this->sendErrorResponse($this->errorMessage);

                    return false;
                }

                break;

            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * Send a response back to the client
     *
     * @param  mixed $responseBody Body of the response. Might have different types which is based on a certain action and its result
     * @param  string $contentType
     *
     * @return mixed
     */
    private function sendResponse($responseBody, $contentType = "text/xml") {
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . strlen($responseBody));
        echo $responseBody;
    }

    /**
     * Send a response with the error back to the client
     *
     * @param  string $errorMessage Error message wrapped to the xml
     *
     * @return string
     */
    private function sendErrorResponse($errorMessage) {
        $this->sendResponse($errorMessage, "text/xml");
    }

    /**
     * Run a file upload action
     *
     * @return boolean
     */
    private function runUploadAction()
    {
        if ( ! isset ($_FILES['fileToUpload'])) {
            $this->errorMessage = $this->getXmlErrorMessage('$_FILES[\'fileToUpload\'] must be specified.');

            return false;
        }

        $fileUploader = new FileUploader($_FILES['fileToUpload']);

        if ( ! $fileUploader->upload()) {
            $this->errorMessage = $this->getXmlErrorMessage($fileUploader->getUploadError());

            return false;
        }

        $this->responseBody = "Your file has been successfully uploaded to the server.";

        return true;
    }

    /**
     * Create an instance of Imagick
     *
     * @return Imagick|false
     */
    private function createImagickInstance()
    {
        try {
            $imagick = new Imagick();
        } catch (Exception $e) {
            $this->errorMessage = $this->getXmlErrorMessage($e->getMessage());

            return false;
        }

        return $imagick;
    }

    /**
     * Create an instance of ImageManipulator
     *
     * @param  Imagick $imagick
     * @param  SplFileInfo $file
     *
     * @return ImageManipulator|false
     */
    private function createImageManipulatorInstance($imagick, $file)
    {
        $imageManipulator = new ImageManipulator($imagick, $file);

        if ($imageManipulator->getErrorMessage()) {
            $this->errorMessage = $this->getXmlErrorMessage($imageManipulator->getErrorMessage());

            return false;
        }

        return $imageManipulator;
    }

    /**
     * Run file thumbnail creation and resize action
     *
     * @return boolean
     */
    private function runThumbnailAction()
    {
        $file    = new SplFileInfo($this->actionParameterList['fileName']);
        $imagick = $this->createImagickInstance();

        if ( ! $imagick) {
            return false;
        }

        $imageManipulator = $this->createImageManipulatorInstance($imagick, $file);

        if ( ! $imageManipulator) {
            return false;
        }

        $image = $this->getResizedThumbnail($imageManipulator);

        if ( ! $image) {
            return false;
        }

        $imageManipulator->clear();

        $this->responseBody = $image;

        return true;
    }

    /**
     * Create a resized thumbnail of the image
     *
     * @param  ImageManipulator $imageManipulator
     *
     * @return Imagick|false
     */
    private function getResizedThumbnail($imageManipulator)
    {
        if ( ! $imageManipulator->convertTo($this->actionParameterList['format'])) {
            $this->errorMessage = $this->getXmlErrorMessage($imageManipulator->getErrorMessage());

            return false;
        }

        if ( ! $imageManipulator->resizeTo($this->actionParameterList['width'], $this->actionParameterList['height'])) {
            $this->errorMessage = $this->getXmlErrorMessage($imageManipulator->getErrorMessage());

            return false;
        }

        $image = $imageManipulator->getImage();

        if ( ! $image) {
            $this->errorMessage = $this->getXmlErrorMessage($imageManipulator->getErrorMessage());

            return false;
        }

        return $image;
    }

    /**
     * Run a certain action, which is defined earlier based on parameters parsing and initialization
     *
     * @return boolean
     */
    public function runAction()
    {
        switch ($this->action) {
            case 'upload':
                if ( ! $this->runUploadAction()) {
                    $this->sendErrorResponse($this->errorMessage);

                    return;
                }

                $this->sendResponse($this->getXmlSuccesMessage($this->responseBody), 'text/xml');

                break;

            case 'thumbnail':
                if ( ! $this->runThumbnailAction()) {
                    $this->sendErrorResponse($this->errorMessage);

                    return;
                }

                $this->sendResponse($this->responseBody, 'image/'. $this->actionParameterList['format']);

                break;

            default:
                $this->responseBody = 'You must specify either "upload" or "thumbnail" as "action" POST parameter';
                $this->sendResponse($this->getXmlSuccesMessage($this->responseBody));

                break;
        }
    }
}
