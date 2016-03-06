<?php
/**
 * @file
 * The class that provides crypto algs for use in SedaMicro.
 *
 * @category Interface
 *
 * @package SedaMicro
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link http://www.ict4hr.net
 */

require_once dirname(__FILE__) . "/symmetric_crypto.inc.php";
/**
 * This class handles the assymmetric encryption functionality.
 */
class SedaMicroSedaCrypt {
	/**
	 * Generate a public and private key.
	 *
	 * @return array
	 *   The array containing 3 elements:
	 *   private key, public key, symmetric key, type, and if there is an error.
	 */
	public function generate() {

		// Set the key parameters.
		$config = array(
				"digest_alg" => "sha512",
				"private_key_bits" => 4096,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			       );

		// Create the private and public key.
		$res = openssl_pkey_new($config);

		// Extract the private key from $res to $priv_key.
		openssl_pkey_export($res, $priv_key);

		// Extract the public key from $res to $pub_key.
		$pub_key = openssl_pkey_get_details($res);
		$bytes = openssl_random_pseudo_bytes(32);
		$symmetric = bin2hex($bytes);
		if ($bytes == FALSE || empty($symmetric)) {
			return array(
					'error' => 1,
				    );
		}
		return array(
				'private' => $priv_key,
				'public' => $pub_key["key"],
				'symmetric' => $symmetric,
				'type' => $config,
				'error' => 0,
			    );
	}

	/**
	 * Encrypt data using the public key.
	 *
	 * @param string $data
	 *   The data to be encrypted.
	 * @param string $public_key
	 *   The key to use.
	 *
	 * @return string
	 *   The encrypted string.
	 */
	public function encrypt($data, $public_key) {

		// Encrypt the data using the public key.
		openssl_public_encrypt($data, $encrypted_data, $public_key);

		// Return encrypted data.
		return $encrypted_data;
	}

	/**
	 * Decrypt data using the private key.
	 *
	 * @param string $data
	 *   The encrypted string.
	 * @param string $private_key
	 *   The private key to use.
	 *
	 * @return decrypted string
	 *   A normal string of decrypted text.
	 */
	public function decrypt($data, $private_key) {

		// Decrypt the data using the private key.
		openssl_private_decrypt($data, $decrypted_data, $private_key);

		// Return decrypted data.
		return $decrypted_data;
	}

	/**
	 * Check the key used by uploding user.
	 *
	 * @param string $data
	 *   The string coming from user, decrypted:
	 *   parameters:
	 *   [0] : question
	 *   [1] : answer from user
	 *   [2] : passphrase (optional)
	 *   [3] : time of response.
	 * @param string $pass
	 *   Public key used.
	 */
	public function checkUploadKey($data, $pass) {

		$sent_data = explode("|", $data);
		// First check simple validations: pass and time.
		if (($sent_data[2] == $pass)
				// The time is within 24 hours.
				&& (abs($sent_data[3] - date("U")) < 86400)
		   ) {
			print_r($sent_data);
			if ($sent_data[0]) {
			}
		}

	}

	/**
	 * Convert the "question" (formerly an equation) to a hashed string.
	 *
	 * @param string $eq
	 *    String representing expected answer to the security question.
	 * @param string $pass
	 *    The password to use.
	 * @param string $sym_key
	 *    The symmetric key to use.
	 *
	 * @return string $str
	 *    The string generated with answer+time.
	 */
	public function a2c($eq, $pass, $sym_key) {

		$sym_crypto = new SedaMicroSymmetricCrypto();
		if (!$sym_key) {
			echo "Error: Empty symmetric key!";
			return 0;
		}
		$current_time = date("U");
		$to_crypt = $eq . "|" . $pass . "|" . $current_time;
		$crypted = $sym_crypto->encrypt($to_crypt, $sym_key, 1);
		return $crypted;
	}


	/**
	 * Convert crypted hash to the "answer" user had given.
	 *
	 * Decrypt the string, validate time (and opional pass), and return
	 * string (expected answer) if valid.
	 *
	 * @param string $data
	 *   The string of encypted data.
	 * @param string $pass
	 *    The password to use.
	 * @param string $sym_key
	 *    The symmetric key.
	 *
	 * @return string
	 *    The answer expected, 0 if expired input.
	 */
	public function c2a($data, $pass, $sym_key) {

		$sym_crypto = new SedaMicroSymmetricCrypto();
		if (!$sym_key) {
			echo "Error: Empty symmetric key!";
			return 0;
		}
		$decrypted = $sym_crypto->decrypt($data, $sym_key, 1);
		$current_time = date("U");
		$sent_data = explode("|", $decrypted);
		if (count($sent_data) < 3) {
			die("String not decrypted");
		}
		// First check simple validations: pass and time.
		if (($sent_data[1] == $pass)
				// The time is within 1 hour.
				&& (($current_time - $sent_data[2]) < 3600)
		   ) {
			if ($sent_data[0]) {
				return $sent_data[0];
			}
		}
		else {
			// Either time has expired, or pass was wrong
			// print_r($sent_data);
			// echo $current_time;.
		}
		return "";
	}


	/**
	 * Spam prevention captcha.
	 *
	 * Create an on-the-fly image from text for spam prevention.
	 * To avoid having to deal with temporary CAPTCHA image files,
	 * iamge is created as a Base 64 encoded string.
	 *
	 * @param string $str
	 *   String of text to be embedded into image.
	 * @return string 
	 * base64 encoded image contents to show inline.
	 */

	public function str2imgblob( $str ) {
		if (!function_exists('gd_info')) {
			// Throw new Exception('Required GD library is missing');.
			die('Required GD library is missing');
		}
		/* Create a new imagick object */
		$img = new Imagick();
		$error_msg = ("Error" == substr($str, 0, 5));
		$width = 85;
		$height = 30; 
		$img->newImage($width, $height, new ImagickPixel('white'));
		$draw = new ImagickDraw();
		$draw->setFillColor('black');
		$img->drawImage($draw);
		$img->setImageFormat('png');
		$draw->setStrokeColor('black');
		if ($error_msg) {
			$img->annotateImage($draw, 5, 10, 0, $str);
		}
		else {
			// Make the text a bit harder to read by machines.
			$img->addNoiseImage(imagick::NOISE_GAUSSIAN,imagick::COLOR_MAGENTA );
			$img->addNoiseImage(imagick::NOISE_RANDOM,imagick::COLOR_RED );
			for ($i = 0; $i < strlen($str); $i++) {
				$img->annotateImage($draw, 5 + 10*$i , 10+rand(0,10), rand(0,45) , $str[$i]);
			}
		}
		return $img->getImageBlob();
	}
	/**
	 * Generate random string of given length.
	 *
	 * @param int $length
	 *   Default is 10.
	 *
	 * @return string
	 *   A random string of num/symbols of given length.
	 */
	private function generateRandomString($length = 7) {

		$characters = '0123456789abcdefhijklmnoprstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ';
		$characters_length = strlen($characters);
		$random_string = '';
		for ($i = 0; $i < $length; $i++) {
			$random_string .= $characters[rand(0, $characters_length - 1)];
		}
		return $random_string;
	}

	/**
	 * Generate a random equation, so user can solve it.
	 *
	 * @param string $key
	 *   The key to use.
	 *
	 * @return array
	 *   The arry is: (equation, expected answer).
	 */
	public function createEq($key) {

		$rand_str = self::generateRandomString();
		$img_eq = self::a2c($rand_str, "", $key);
		$expected_answer = self::a2c($rand_str, "", $key, "");
		return array("eq" => $img_eq, "answer" => $expected_answer);
	}

}
