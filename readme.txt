=== SedaMicro ===
Contributors: wp4hr
Tags: audio, images, upload
Requires at least: 4.0.1
Tested up to: 4.4.1
Stable tag: 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SedaMicro allows app-based recording of audio, and uploading it without the need to register 
to Amazon S3 account associated with WP installation.

== Description ==

SedaMicro is a lightweight web-based and mobile user interface for audio recording and uploading.  It allows direct
app-based recording of lightweight audio files, direct download of created audio files, and storage and publishing 
of generated content to Amazon S3.

SedaMicor can be installed in Drupal and WordPress alike, and has been audited for security problems. Extensive manual, 
instructions to correctly setup Amazon S3, database security, and similar are under the "manuals" folder. 

== Installation ==

See the installation manual under /manuals/ folder for more detailed information. The short version is as folows:

Step 1:
-Install Imagick and NumberFormatter, if not already installed on webserver
--http://php.net/manual/en/intl.setup.php  → for handling Farsi input from users
--http://php.net/manual/en/imagick.setup.php → for removing Image geotags from user photos

Step 2: 
-Create an S3 Bucket specifically for the uploaded content. See the manual to see the details, as S3 setup
can be tricky.

Step 3:
--Install the Amazon S3 plugin and configure it
--Install Seda-Micro plugin

Step 4:
-Install SedaMicro Android app on a cellphone with access to the internet
-Update the settings menu to point to this server
--Alternatively, to have a larger coverage of users with different setups, we enabled direct access to uploader

Step 5: 
-Users can use the app or a web browser to upload audio files (and image or videos if web server permits)
-The WP/Drupal should also be configured to accept the types. This is to prevent PHP/CGI/Exe files from being uploaded
--Settings for allowed file types are inherited from WP
-Provided Android app allows users to record audio files. They can also select existing media files from their library
-The captcha is to prevent scripted mass bombardment of storage with files

Step 6:
To display or use uploaded files:
*In Wordpress you can directly browse in the gallery, or use template pages that list gallery items
