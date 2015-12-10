<?php
include_once(dirname(__File__) .'/includes/seda-micro-option.inc.php');
include_once(dirname(__File__) .'/includes/crypt.inc.php');

/*
Plugin Name: SedaMicro Uploader 
Plugin URI: {{git_url}}
Description: This plugin will add allow file upload directly from the Android app to the Amazon S3 if correctly connected.
Version: 0.5.0
Author: ICT4HR
Author URI: http://www.domain.net/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


class seda_micro
{
	/*
	*  Construct
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/15
	*/
	public $seda_keys=0;
	function __construct()
	{
		// set text domain
		// version 3-
		add_action( 'admin_menu', 'seda_micro_menu' );
		$seda_keys = new SedaCrypt();
		if (!get_option("seda_keys"))
		{
			$key_arr = $seda_keys->generate();
			if ($key_arr['error'] == 0)
			{
				add_option("seda_keys", $key_arr, 0, 0);
			}
			else
			{
				die("error generating keys");
			}
		}
	}
	
	
	/*
	*  Init
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/15
	*/
	
	function init()
	{
		if(function_exists('register_field'))
		{ 
			register_field('seda-micro', dirname(__File__) . '/seda-micro.php');
		}
	}
	
	/*
	*  register_fields
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/15
	*/
	
	function register_fields()
	{
		include_once('basic_file.php');
	}
	/*
	* install
	* 
	* @description: check dependencies
	* @since: 3.6
	* @created: 9/1/2015
	*/
	function install()
	{
		if (!class_exists(Imagick))
		{
			$error_message = __('This plugin requires <a href="http://php.net/manual/en/imagick.setup.php">Imagick</a> to be active!', 'Imagick');
			die($error_message);	
		}
		
	}
}
new seda_micro();
		
?>
