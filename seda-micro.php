<?php
include_once(__DIR__."/includes/crypt.inc.php");	
include_once(dirname(__File__) .'/includes/seda-micro-option.inc.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(__DIR__."/includes/wp_interface.inc.php");

/**
 * Plugin Name: SedaMicro Uploader 
 * Plugin URI: https://github.com/icthr/sedamicro
 * Description: This plugin will add allow file upload directly from the Android app to the Amazon S3 if correctly connected.
 * Version: 0.8.4
 * Author: ICT4HR
 * Author URI: http://www.ict4hr.net/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


class seda_micro
{
	public $seda_keys = 0;
	/*
	 *  Construct
	 *
	 *  @description: 
	 *  @since 0.6
	 *  @created 1/04/15
	 */
	function __construct()
	{
		add_action('init', array($this, 'init'));
	}


	/*
	 *  Init function.
	 *
	 *  @description: 
	 * Register the fields for admin, and register a short-code.
	 * We also associate the .MO file in 'languages' folder to this
	 * plugin. Finally, we generate the keys that will be used in later
	 * sections.
	 *
	 *  @since 0.6
	 *  @created 1/04/15
	 */

	function init()
	{
		if(function_exists('register_field'))
		{ 
			register_field('seda-micro', dirname(__File__) . '/seda-micro.php');
		}
		add_action( 'admin_menu', 'seda_micro_menu' );
		add_shortcode( 'sedamicro-form', array( $this, 'seda_create_form'));
		load_plugin_textdomain('sedamicro', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
		$seda_keys = new SedaMicroSedaCrypt();
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
	 *  register_fields.
	 *
	 *  @description: 
	 *  
	 *  @since 0.6
	 *  @created 1/04/15
	 */

	function register_fields()
	{
		include_once('basic_file.php');
	}
	/*
	 * Install would check dependencies.
	 * 
	 * @description: check Imagick is installed.
	 * @since: 0.6
	 * @created 9/1/2015
	 */
	function install()
	{
		if (!class_exists(Imagick))
		{
			$error_message = __('This plugin requires <a href="http://php.net/manual/en/imagick.setup.php">Imagick</a> to be active!', 'Imagick');
			die($error_message);	
		}

	}
	/*
	 * seda_create_form
	 * 
	 * @description
	 * This generates the form that replaces the shorthand.
	 * @created 2/29/2016
	 */
	function seda_create_form()
	{
		$crypt = new SedaMicroSedaCrypt();
		$key= get_option("seda_keys");
		$eq = $crypt->createEq($key["symmetric"]);
		wp_enqueue_style('sedamicro',plugins_url('images/sedamicro-wp.css',__FILE__));
		if(isset($_POST["submit"]) && ($_FILES["fileToUpload"]["size"]>0)) {
			$msg = $this->seda_handle_uploads();
		}
		?>
			<div class="element">
			<?php _e("Please choose file",'sedamicro');?>
			<br />
			<div id = "loadingDiv"><img id = "myImage" src = "<?php echo plugins_url( 'images/loading.gif', __FILE__ ); ?>" style="z-index:5;"></div>
			<center>
			<br/>
			<div id="doit">
			<img src="<?php echo plugins_url( 'images/rec.png', __FILE__);?>" />
			</div>
			<form action="" id="fileForm" method="post" enctype="multipart/form-data">
			<?php _e("Title",'sedamicro');?>:
			<input type="text" name="fileTitle" id="fileTitle" size=50 value="" /><br />
			<input type="file" name="fileToUpload" id="fileToUpload"><br />
			<?php echo $lang['please_answer']; ?>  </br >
			<input type="text" name="ga" value="" width=5 />
			<!--<img height="100" style="min-width:30%;" src="<?php plugins_url( 'img.php', __FILE__ ); ?>?eq=<?php echo $eq['eq']; ?>" /> -->
			<img height="100" style="min-width:30%;" 
				src="<?php  echo $this->generate_captcha($eq['eq']);?>" />
			<input type="hidden" name="hashKey" id ="hashKey" value="<?php echo $eq['answer'];?>"/><br />
			<input type="submit" value="<?php _e('Send','sedamicro');  ?>" name="submit">
			</form>
			</center>
			<?php 
			if($msg)
			{
				echo "<div id='message'>\n".$msg."\n</div>";
			}
		?>
			</div>
			<script type="text/javascript">
			doit.onclick = function(){fileToUpload.click();};
		fileForm.onsubmit = function(){document.getElementById("loadingDiv").style.display="block";};
		</script>
			<?php
	}//end seda_create_form()

	/**
	 * Add uploaded file into WP library.
	 * 
	 * We make sure (1) answer for CAPTCHA is right, (2) type of file is accepted,
	 * and (3) for images remove the geotag information.
	 *
	 */
	function seda_handle_uploads()
	{
		$crypt = new SedaMicroSedaCrypt();
		$rsaKeys = get_option("seda_keys");
		$cms = new SedaMicroWPInterface();
		$msg="";
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"]) && ($_FILES["fileToUpload"]["size"]>0)) {
			/*************/
			$expectedAnswer = $crypt->c2a($_POST['hashKey'],"",$rsaKeys['symmetric']);
			$keyValid = ($expectedAnswer == $_POST['ga']);
			if (!$keyValid && class_exists(NumberFormatter))
			{
				//what if user just used the wrong language?
				//if they have the NumberFormatter installed, translate encoding
				$fmt = numfmt_create('fa', NumberFormatter::DECIMAL);
				$givenAnswer = numfmt_parse($fmt,  $_POST['ga']);	
				$keyValid = ($expectedAnswer == $givenAnswer);
			}
			/*************/
			$target_dir = __DIR__."uploads/";
			$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
			$uploadOk = 1;
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if (!in_array($imageFileType, 
						array('png', 'gif', 'jpg','jpeg', 'ogg','mp3','mp4'))) 
			{
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if (0 == $keyValid || 0 == $uploadOk ) {
				$msg.="<span>".__("sorry file was not uploaded", 'sedamicro').".</span>";
				// if everything is ok, try to upload file
			} else {
				$uploadedfile = $_FILES['fileToUpload'];
				$upload_overrides = array( 'test_form' => false );
				//WP handles escaping characters, etc. 
				$movefile = $cms->handleUpload( $uploadedfile, $upload_overrides );
				if ( $movefile && ((!isset( $movefile['error'])||$movefile['error']==0 )) ) {
					//echo "File is valid, and was successfully uploaded.\n";
					//var_dump( $movefile);
					$file_array = array(
							'name' => $uploadedfile["name"],
							'type' =>$uploadedfile["type"], 
							'tmp_name' => $movefile["file"], 
							'error' => 0, 
							'size' => $uploadedfile["size"]
							);
					if ($uploadedfile["type"]=="image/jpeg")
					{
						$img = new Imagick($movefile["file"]);
						$img->stripImage();
						$img->writeImage($movefile["file"]);
						$img->clear();
						$img->destroy();
					}
					//double check for XSS in case CMS misses it
					//accepted filetypes are enforced by CMS, and configured in its config
					$id=  $cms->mediaSideload($file_array, 0, htmlspecialchars($_POST['fileTitle']));
					if ( $cms->isError(array($id)) ) {
						$msg=__('Error', 'sedamicro').":<br /><span dir=ltr>".$id."</span>";
					}
					else {	
						//this URL will be S3 if configured, else a local URL on this server
						$msg.=__('File was uploaded successfully', 'sedamicro').
							"&nbsp;<a href=\"".$cms->getAttachmentUrl(array($id))."\">".__('see here', 'sedamicro')."</a>.\n";
					}
				} else {
					/**
					 * Error generated by _wp_handle_upload()
					 * In Drupal emulated but CMS classes
					 * @see _wp_handle_upload() in wp-admin/includes/file.php
					 */
					$msg=__('Error', 'sedamicro').":<br /><span dir=ltr>".$movefile['error']."</span>";
				}
			}
		}
		return $msg;

	}//end seda_handle_uploads

	/**
	 * Generate images for CAPTCHA section.
	 *
	 * A random string is generated, then stored in an image file
	 * stored in 'uploades' directory of the WordPress.
	 *
	 * @since 0.8.4 
	 * @access private.
	 *
	 * @param string $eq The CAPTCHA string.
	 * @param type $var Optional. Description.
	 * @return String URL for the file that was generated.
	 */
	private function generate_captcha($eq)
	{
		$cms = new SedaMicroWPInterface();
		$c = new SedaMicroSedaCrypt();
		$eq = htmlspecialchars($eq);
		if (!isset($eq) || (strlen($eq)>255) || (strlen($eq)<16))
		{
			die();
		}

		$key= $cms->getOption("seda_keys");
		
		$crypted = $c->c2a($eq,"",$key['symmetric']);
		if ( !empty($crypted) )
		{
			$contents=$c->str2imgblob($crypted);
			return "data:image/jpg;base64,".base64_encode($contents);
		}
		else
		{
			$c->str2img("Error!");
		}
	}//end generate_captcha

}
new seda_micro();

?>
