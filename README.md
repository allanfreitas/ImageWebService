ImageWebService
===============

Another web-service which has two api: to upload an image file and to get a thumbnail in certain format from the previously uploaded one

It uses two classes which implement that functionality (ImageManipulator and FileUploader). Both are a part of this project

TODO before pushing this web-service onto a real server:
1) Make sure 'uploads' directory is defined and it proper access level
2) Make sure to define the maximum allowed size of the file to be uploaded
3) Make sure you have Imagick PHP extension installed and PHP properly configured
4) Make sure you are passing the right file path to the service (to avoid extra loading)
5) Make sure you defined maximum allowed thumbnail size

