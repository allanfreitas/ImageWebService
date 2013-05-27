<?php
/**
 * Example of using WebService to upload a file
 *
 * Copyright(c) 2013 Oleksandr Kovalov <oleksandr.kovalov@gmail.com>
 *
 * MIT Licensed
 */

// First of all check if curl is installed
if ( ! function_exists('curl_init')){
    die('Sorry cURL is not installed!');
}

// Define a file name here
$fullFilePath = 'test.jpg';

// Define a web service url
$uploadUrl    = 'http://localhost/service.php';

// Here a list of parameters with examples of values which needs to be sent into the web-service
$postParameterArray = array(
    'fileToUpload' => "@$fullFilePath",
    'action'       => 'upload'
);

// Curl routine
$ch = curl_init();

curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postParameterArray);

$response = curl_exec($ch);

// We need to know headers size to be able to split headers and body of the response
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

// Close curl right away since we don't need that anymore
curl_close($ch);

// Now we're gotta split headers and body of the response
$headerString = substr($response, 0, $headerSize);
$body         = substr($response, $headerSize);
$headerToken  = strtok($headerString, "\n\t");

// Print the headers
while ($headerToken !== false) {
    header($headerToken);
    $headerToken = strtok("\n\t");
}

// Print the body
echo $body;
