<?php

/*
* This class serves as an intreface between SedaMicro and the CMS it is loaded into.
* By making it into a polymorhic structure, the future updates can add new CMS support 
* by inheriting this class and implementing it for the target CMS.
* The abstraaction hopefully helps lowering maintenance efforts.
*/
interface CMSInterface 
{
	/*
	* A method to get variables stored throught the CMS 
	* in the database.
	* @param $name name of the "variable" to be retrieved
	* @retrun value of said variable according to CMS DB interface
	*/
	function get_option($name);	
	/*
	* A method to set variables stored throught the CMS 
	* in the database.
	* @param $name name of the "variable" to be set
	* @param $value value of the variable to be set. It may be an object
	* @retrun success or failure
	*/
	function set_option($name,$value);
	/*
	* Handle a file upload through CMS
	* if hooked correctly, CMS can send to S3 or store locally
	* @param
	* @return
	*/
	function handle_upload($file,$overrides);
	/*
	* If hooked correctly, CMS can send to S3 or store locally
	* and add it to local media library
	* @param
	* @return
	*/
	function media_sideload($file_array, $opst_id, $desc);


	/*
	* See if the given param is an error
	* @param $thing input value 
	* @return true if error, false if not
	*/
	function is_error($thing);

	/*
	* Translate an error number to a message
	* @param $id error ID
	* @return error message 
	*/
	function error_message($id);

	/*
	* @param $id ID of attached file
	* @return URL to that file (on S3 if correctly connected)
	*/
	function get_attachment_url($id);
}




?>
