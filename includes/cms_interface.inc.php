<?php
/**
 * @file
 * Interface declaration for multiple CMS interfaces.
 *
 * This class serves as an intreface between SedaMicro and the CMS it is loaded
 * into. By making it into a polymorhic structure, the future updates can add
 * new CMS  support by inheriting this class and implementing it for the target
 * CMS.
 * The abstraaction hopefully helps lowering maintenance efforts.
 *
 * @category Interface
 *
 * @package SedaMicro
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link http://www.ict4hr.net
 */

/**
 * Main interface declaration.
 */
interface SedaMicroCMSInterface {
  /**
   * A method to get variables stored through the CMS in the database.
   *
   * @param string $name
   *   Name of the "variable" to be retrieved.
   *
   * @return array
   *    Value of said variable according to CMS DB interface.
   */
  public function getOption($name);
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
  public function setOption($name, $value);
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
  public function handleUpload($infile, $overrides);
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
  public function mediaSideload(array $file_array, $post_id, $desc);
  /**
   * See if the given param is an error.
   *
   * @param array $thing
   *   Array containing error id, message.
   *
   * @return bool
   *   True if error, false if not.
   */
  public function isError(array $thing);

  /**
   * Translate an error number to a message.
   *
   * @param int $id
   *   Array containing error id, message.
   *
   * @return string
   *   Error message.
   */
  public function errorMessage($id);

  /**
   * Get direct URL to the file uploaded.
   *
   * @param array $id
   *   Array containing error id, message, URI.
   *
   * @return string
   *   URL to that file (on S3 if correctly connected).
   */
  public function getAttachmentUrl(array $id);

}
