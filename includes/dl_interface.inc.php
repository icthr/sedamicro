<?php
/**
 * @file
 * Drupal interface for SedaMicro.
 *
 * This class serves as an intreface between
 * SedaMicro and the CMS it is loaded into.
 *
 * @category Interface
 *
 * @package sedaMicro
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link http://www.ict4hr.net
 */

require_once __DIR__ . "/cms_interface.inc.php";
/**
 * Class to implement the Drupal interface based on CMSInterface.
 *
 * @category Interface
 *
 * @package SendMicro
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://www.ict4hr.net.
 */
class SedaMicroDLInterface implements SedaMicroCMSInterface {
  /**
   * A method to get variables stored through the CMS in the database.
   *
   * @param string $name
   *   Name of the "variable" to be retrieved.
   *
   * @return array
   *    Value of said variable according to CMS DB interface.
   */
  public function getOption($name) {

    $arr = array(
      'private'     => variable_get("seda_private"),
      'public'     => variable_get("seda_public"),
      'symmetric'    => variable_get("seda_symmetric"),
      'type'         => '',
    );
    return $arr;
  }

  /**
   * A method to set variables stored through the CMS in the database.
   *
   * @param string $name
   *   Name of the "variable" to be set.
   * @param string $value
   *   Value of the variable to be set. It may be an object.
   *
   * @return bool
   *   Success or failure.
   */
  public function setOption($name, $value) {

    $ret = 0;
    $ret &= variable_set("seda_private", $value["private"]);
    $ret &= variable_set("seda_public", $value["public"]);
    $ret &= variable_set("seda_symmetric", $value["symmetric"]);
    return $ret;
  }
  /**
   * Handle a file upload through CMS.
   *
   * If hooked correctly, CMS can send to S3 (putObject) or store locally
   * with the recent support changes for S3, file_save_data also adds the
   * DB data.
   *
   * @param string $infile
   *   The path to file to be handled.
   * @param string $overrides
   *   The CMS dependent tags to pass.
   *
   * @return string
   *   Path to temporary file.
   */
  public function handleUpload($infile, $overrides) {

    $infile["file"] = $infile["tmp_name"];
    return $infile;
  }
  /**
   * Add to DB, move to S3.
   *
   * If hooked correctly, CMS can send to S3 or store locally
   * and add it to local media library.
   *
   * @param array $file_array
   *   Array pointing to the file uploaded.
   * @param int $post_id
   *   Not used in Drupal.
   * @param string $desc
   *   Description of file that is uploaded.
   *
   * @return array
   *   An array (code, message, uri) showing success/failure and URI.
   */
  public function mediaSideload(array $file_array, $post_id, $desc) {

    $fl = file_get_contents($file_array['tmp_name']);
    $fileuri = "s3://" . time() . "-" . $file_array["name"];
    // No need to use bucket name here, uses global config.
    $s3_file = file_save_data($fl, $fileuri);
    if ($s3_file->filesize > 0) {
      $ret_val = array(
        "code" => 0,
        "message" => "File uploaded.",
        "uri" => $fileuri,
      );
    }
    else {
      $ret_val = array(
        "code" => 1,
        "message" => "Unable to upload file.",
        "uri" => "",
      );
    }
    // Echo file_create_url($fileuri);
    return $ret_val;
  }


  /**
   * See if the given param is an error.
   *
   * @param array $thing
   *   Array containing error id, message.
   *
   * @return bool
   *   True if error, false if not.
   */
  public function isError(array $thing) {

    return ($thing['code'] != 0);
  }

  /**
   * Translate an error number to a message.
   *
   * @param int $id
   *   Array containing error id, message.
   *
   * @return string
   *   Error message.
   */
  public function errorMessage($id) {

    return $id['message'];
  }
  /**
   * Get direct URL to the file uploaded.
   *
   * @param array $id
   *   Array containing error id, message, URI.
   *
   * @return string
   *   URL to that file (on S3 if correctly connected).
   */
  public function getAttachmentUrl(array $id) {

    return file_create_url($id['uri']);
  }

}
