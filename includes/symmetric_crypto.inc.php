<?php
/**
 * @file
 * Class to handle functionality relating to symmetric crypto works.
 *
 * Class comes from an excellent answer in
 * http://stackoverflow.com/questions/9262109/php-simplest-two-way-encryption
 * by Scott Arciszewski.
 *
 * @category library
 *
 * @package SedaMicro
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link http://www.ict4hr.net
 */

/**
 * Main class to implement the symmetric hash+uuencode+key handling.
 */
class SedaMicroSymmetricCrypto {
  const HASH_ALGO = 'sha256';
  const METHOD = 'aes-256-ctr';

  /**
   * Encrypts then MACs a message.
   *
   * @param string $message
   *   Plaintext message.
   * @param string $key
   *   Encryption key (raw binary expected).
   * @param bool $encode
   *   Set to TRUE to return a base64-encoded string.
   *
   * @return string
   *   Raw binary of encrypted text.
   */
  public static function encrypt($message, $key, $encode = FALSE) {

    list($enc_key, $auth_key) = self::splitKeys($key);

    $nonce_size = openssl_cipher_iv_length(self::METHOD);
    $is_strong = FALSE;
    $nonce = openssl_random_pseudo_bytes($nonce_size, $is_strong);

    if (!$is_strong) {
      die("pseudo random bytes not strong; system might be too old");
    }
    $ciphertext = openssl_encrypt(
          $message,
          self::METHOD,
          $enc_key,
          OPENSSL_RAW_DATA,
          $nonce
      );

    // Pass to UnsafeCrypto::encrypt.
    $ciphertext = $nonce . $ciphertext;

    // Calculate a MAC of the IV and ciphertext.
    $mac = hash_hmac(self::HASH_ALGO, $ciphertext, $auth_key, TRUE);

    if ($encode) {
      return self::base64UrlEncode($mac . $ciphertext);
    }
    // Prepend MAC to the ciphertext and return to caller.
    return $mac . $ciphertext;
  }

  /**
   * Decrypts a message (after verifying integrity).
   *
   * @param string $message
   *   Ciphertext message.
   * @param string $key
   *   Encryption key (raw binary expected).
   * @param bool $encoded
   *   Are we expecting an encoded string? True or false.
   *
   * @return string
   *   Return decrypted text (raw binary).
   */
  public static function decrypt($message, $key, $encoded = FALSE) {

    list($enc_key, $auth_key) = self::splitKeys($key);
    if ($encoded) {
      $message = self::base64UrlDecode($message, TRUE);
      if ($message === FALSE) {
        // Throw new Exception('Encryption failure');.
        die('Encryption failure');
      }
    }

    // Hash Size -- in case HASH_ALGO is changed.
    $hs = mb_strlen(hash(self::HASH_ALGO, '', TRUE), '8bit');
    $mac = mb_substr($message, 0, $hs, '8bit');

    $ciphertext = mb_substr($message, $hs, NULL, '8bit');

    $calculated = hash_hmac(
          self::HASH_ALGO,
          $ciphertext,
          $auth_key,
          TRUE
      );

    if (!self::hashEquals($mac, $calculated)) {
      // Throw new Exception('Encryption failure');
      // uncaught exceptions reveal data.
      die('Encryption failure');
    }

    $nonce_size = openssl_cipher_iv_length(self::METHOD);
    $nonce = mb_substr($ciphertext, 0, $nonce_size, '8bit');
    $ciphertext = mb_substr($ciphertext, $nonce_size, NULL, '8bit');

    $plaintext = openssl_decrypt(
          $ciphertext,
          self::METHOD,
          $enc_key,
          OPENSSL_RAW_DATA,
          $nonce
      );
    return $plaintext;
  }

  /**
   * Splits a key into two separate keys.
   *
   * Splits keys to two separate keys, which are used for two operations.
   * One is used for encryption and the other for authenticaiton.
   *
   * @param string $master_key
   *   Text of key (raw binary).
   *
   * @return array
   *   Two raw binary strings.
   */
  protected static function splitKeys($master_key) {

    // Maybe get HKDF here one day!
    return [
      hash_hmac(self::HASH_ALGO, 'ENCRYPTION', $master_key, TRUE),
      hash_hmac(self::HASH_ALGO, 'AUTHENTICATION', $master_key, TRUE),
    ];
  }

  /**
   * Compare two strings without leaking timing information.
   *
   * @param string $a
   *   String first.
   * @param string $b
   *   Second string.
   *
   * @return bool
   *   True if the same, else false.
   */
  protected static function hashEquals($a, $b) {

    if (function_exists('hash_equals')) {
      return hash_equals($a, $b);
    }
    $nonce = openssl_random_pseudo_bytes(32);
    return hash_hmac(self::HASH_ALGO, $a, $nonce) === hash_hmac(self::HASH_ALGO, $b, $nonce);
  }

  /**
   * Wrapper to do UUEncode, replacing the the characters GET cannot handle.
   *
   * @param string $input
   *   Input string.
   *
   * @return string
   *   Trimmed string of uuencode.
   */
  protected static function base64UrlEncode($input) {

    return strtr(base64_encode($input), '+/=', '-_,');
  }

  /**
   * Wrapper to do UUDecode, replacing the the characters GET cannot handle.
   *
   * @param string $input
   *   The input to do UUencode to.
   *
   * @return string
   *   Trimmed string of uudecode
   */
  protected static function base64UrlDecode($input) {

    return base64_decode(strtr($input, '-_,', '+/='));
  }

}
