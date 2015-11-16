<?php
require_once(__DIR__."/cms_interface.inc.php");

/*
* This class serves as an intreface between 
* SedaMicro and the CMS it is loaded into
*/
class DLInterface implements CMSInterface 
{
	/*
	* A method to get variables stored throught the CMS 
	* in the database.
	* @param $name name of the "variable" to be retrieved
	* @retrun value of said variable according to CMS DB interface
	*/
	function get_option($name) 
	{
		$arr = array(
		        'private' 	=> variable_get("seda_private"), 
		        'public' 	=> variable_get("seda_public"),
			'symmetric'	=> variable_get("seda_symmetric"),
		        'type' 		=> '',
			);
		return $arr;
	}
	
	/*
	* A method to set variables stored throught the CMS 
	* in the database.
	* @param $name name of the "variable" to be set
	* @param $value value of the variable to be set. It may be an object
	* @retrun success or failure
	*/
	function set_option($name,$value)
	{
		$ret = 0;
		$ret &= variable_set("seda_private", $value["private"]);
		$ret &= variable_set("seda_public", $value["public"]);
		$ret &= variable_set("seda_symmetric", $value["symmetric"]);
		return $ret;
	}
	/*
	* Handle a file upload through CMS
	* if hooked correctly, CMS can send to S3 (putObject) or store locally
	* with the recent support changes for S3, file_save_data also adds the DB data
	* @param
	* @return
	*/
	function handle_upload($infile,$overrides)
	{
		$infile["file"] = $infile["tmp_name"];
		return $infile;
	}
	/*
	* If hooked correctly, CMS can send to S3 or store locally
	* and add it to local media library
	* @param $file_array the array pointing to the file uploaded
	* @param $post_id not used in Drupal
	* @param $desc description of file that is uploaded
	* @return an array (code, message, uri) showing success/failure and URI
	*/
	function media_sideload($file_array, $opst_id, $desc)
	{
		$fl = file_get_contents($file_array['tmp_name']);
		$fileuri="s3://".time()."-".$file_array["name"];
		$s3_file = file_save_data($fl, $fileuri);	//no need to use bucket name here, uses global config
		if ($s3_file->filesize > 0) 
		{ 
			$retVal = array( 
				"code" => 0,
				"message" => "File uploaded.",
				"uri" => $fileuri,
				);
		}
		else 
		{ 
			$retVal = array( 
				"code" => 1,
				"message" => "Unable to upload file.",
				"uri" => "", 
				);
		}
		//echo file_create_url($fileuri);
		return $retVal; 
	}


	/*
	* See if the given param is an error
	* @param $thing arraye containing error id, message 
	* @return true if error, false if not
	*/
	function is_error($thing)
	{
		return ($thing['code']!=0);
	}

	/*
	* Translate an error number to a message
	* @param $id array containing error id, message
	* @return error message 
	*/
	function error_message($id)
	{
		return $thing['message'];
	}
	/*
	* @param $id array containing error id, message, URI
	* @return URL to that file (on S3 if correctly connected)
	*/
	function get_attachment_url($id)
	{
		return file_create_url($id['uri']);
	}
}
?>
