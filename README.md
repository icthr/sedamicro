# sedamicro
Step 1:
-Install Imagick and NumberFormatter, if not already installed on webserver
--http://php.net/manual/en/intl.setup.php  → for handling Farsi input from users
--http://php.net/manual/en/imagick.setup.php → for removing Image geotags from user photos

Step 2: 
-Create an S3 Bucket specifically for the uploaded content

Step 3:
-For Drupal installation: 
--Install and enable the modules needed for Seda-Mirco and their dependencies:
---AWS SDK for PHP 
---Chaos tools 
---Composer Manager 
---S3 File System
---Libraries
---System
--Install Seda-Micro, and make sure all dependencies are met
--Drupal does not have a default “file browser” and gallery, so pick a plugin

-For Wordpress installation:
--Install the Amazon S3 plugin
--Install Seda-Micro plugin

Step 4:
-Install SedaMicro Android app on a cellphone with access to the internet
-Update the settings menu to point to this server
--Alternatively, to have a larger coverage of users with different setups, we enabled direct access to uploader

Step 5: 
-Users can use the app or a web browser to upload audio files (and image or videos if web server permits)
-The WP/Drupal should also be configured to accept the types. This is to prevent PHP/CGI/Exe files from being uploaded
--Settings for allowed file types are inherited from WP/Druapl
-Provided Android app allows users to record audio files. They can also select existing media files from their library
-The captcha is to prevent scripted mass bombardment of storage with files

Step 6:
-To display or use uploaded files:
--In Wordpress you can directly browse in the gallery, or use template pages that list gallery items
--In Drupal, content management is left to the administrator. There are multiple plugins available that allow such gallery viewing or image insertion.
