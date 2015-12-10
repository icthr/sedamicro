<?php
require_once(dirname(__FILE__)."/symmetric_crypto.inc.php");
class SedaCrypt
{
// Generate a public and private key
function generate()
{
    // Set the key parameters
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Create the private and public key
    $res = openssl_pkey_new($config);

    // Extract the private key from $res to $privKey
    openssl_pkey_export($res, $privKey);

    // Extract the public key from $res to $pubKey
    $pubKey = openssl_pkey_get_details($res);
    $bytes=openssl_random_pseudo_bytes(32);
    $symmetric = bin2hex($bytes);
    if ($bytes == false || empty($symmetric) )
    {
	return array (
		'error' => 1
	);
    }
    return array(
        'private' => $privKey,
        'public' => $pubKey["key"],
	'symmetric'=> $symmetric,
        'type' => $config,
	'error' => 0
    );
}

// Encrypt data using the public key
function encrypt($data, $publicKey)
{
    // Encrypt the data using the public key
    openssl_public_encrypt($data, $encryptedData, $publicKey);

    // Return encrypted data
    return $encryptedData;
}

// Decrypt data using the private key
function decrypt($data, $privateKey)
{
    // Decrypt the data using the private key
    openssl_private_decrypt($data, $decryptedData, $privateKey);

    // Return decrypted data
    return $decryptedData;
}

//parameters:
// [0] : question
// [1] : answer from user
// [2] : passphrase (optional)
// [3] : time of response

/*
* @param data the string coming from user, decrypted: (question, answer from user, passphrase (opt), time)
* @param pass public key
* @return true/false: valid upload
* @see
*/
function checkUploadKey($data, $pass)
{
	$ret = 0;;
	$sentData = explode("|",$data);
	//first check simple validations: pass and time
	if (($sentData[2]==$pass) && 
		(abs($sentData[3]-date("U")) <86400 ))	//the time is within 24 hours
	{
		print_r($sentData);
		if ($sentData[0] )
		{
		}
	}			

}

/*
* @param eq string representing expected answer to the security question
* @param password the password to use
* @return str the string generated with answer+time
* @see
*/

function a2c($eq, $pass, $symKey)
{
	$symCrypto = new SymmetricCrypto();
	if (!$symKey)
	{
		echo "Error: Empty symmetric key!";
		return 0;
	}
	$currentTime = date("U");
	$toCrypt = $eq . "|" . $pass. "|" .$currentTime;
	$crypted=$symCrypto->encrypt($toCrypt, $symKey,1); 
	return $crypted;
}


/*
* Decrypt the string, validate time (and opional pass), and return string (expected answer) if valid
* @param data the string of encypted data 
* @return the answer expected, 0 if expired input 
* @see
*/

function c2a($data, $pass, $symKey)
{
	$symCrypto = new SymmetricCrypto();
	if (!$symKey)
	{
		echo "Error: Empty symmetric key!";
		return 0;
	}
	$decrypted = $symCrypto->decrypt($data, $symKey,1);
	$ret = 0;
	$currentTime = date("U");
	$sentData = explode("|",$decrypted);
	if (count($sentData)<3)
	{
		die("String not decrypted");
	}
	//first check simple validations: pass and time
	if (($sentData[1]==$pass) && 
		(($currentTime - $sentData[2]) < 3600 ))	//the time is within 1 hour
	{
		if ($sentData[0] )
		{
			return $sentData[0];
		}
	}		
	else { 
	//either time has expired, or pass was wrong
		//print_r($sentData); 
		//echo $currentTime;
	}
	return "";
}


/*
* Create an on-the-fly image from text for spam prevention
* Note: this has to be called on a separate page so Header works
* @param str string of text to be embedded into image
* @return none
* @see
*/

function str2img($str)
{
	if(headers_sent()){
	    //throw new Exception('Headers have been send somewhere before this point!');
	    die('Headers have been send somewhere before this point!');
	}
	if( !function_exists('gd_info') ) {
        	//throw new Exception('Required GD library is missing');
        	die('Required GD library is missing');
	}
	$error_msg = ("Error"== substr($str,0,5));
	ob_clean(); 
	$width = 85;
	$height = 30;
	header("Content-Type: image/png");
	$im = @imagecreate($width, $height)
	    or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 250, 255, 255);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	// Make the text a bit harder to read by machines
	if ($error_msg)
	{
		$noise_level = 0;
		imagestring($im,3, 15, 1, $str, $text_color);
	}
	else
	{
		$noise_level = 20;
		for ($i = 0; $i < strlen($str); $i++)
		{	
			imagestring($im,3, 15+10*$i, 1+rand(0,15), $str[$i], $text_color);
		}
	}
	// Add some noise to the image.
	$ns = imagecolorallocate($im, 200, 200, 200);//noise color
	for ($i = 0; $i < $noise_level; $i++) 
	{
		for ($j = 0; $j < $noise_level; $j++) 
		{
			imagesetpixel( $im, rand(0, $width), 
				rand(0, $height),//make sure the pixels are random and don't overflow out of the image
				$ns
			);
		}
	}
	imagepng($im);
	imagedestroy($im);
	ob_end_flush();
}

/*
* Generate a random equation, so user can solve it
* @param 
* @return array of (equation, expected answer)
* @see
*/

function createEq($key)
{
	$answer = -1;
	while ($answer < 0 )
	{
		$x = rand(1,9);
		$y = rand(1,9);
		$z = rand(1,9);
		$op1 = rand(0,1);
		$op2 = rand(0,1);
		$ops = array("+", "-");

		$eq = $x .$ops[$op1].$y.$ops[$op2].$z;
		eval('$answer='.$eq.";");
	}
	$imgEq = self::a2c($eq."=?","",$key);
	$expectedAnswer = self::a2c($answer,"",$key, $x);
	return array("eq"=>$imgEq, "answer"=>$expectedAnswer);
}

/*
* @param 
* @return 
* @see
*/

function func()
{
}
}//of class
?>
