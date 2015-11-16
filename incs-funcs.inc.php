<?php
	require_once(__DIR__."/includes/lang.inc.php");
	include_once(__DIR__."/includes/crypt.inc.php");	
function getCMS()
{
$expected_wp_load_file = "../../../wp-load.php";
if (file_exists($expected_wp_load_file))
{
	require_once($expected_wp_load_file);
	include_once(ABSPATH . 'wp-admin/includes/plugin.php' ); 
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	include_once(ABSPATH . 'wp-admin/includes/plugin.php' ); 
	require_once(__DIR__."/includes/wp_interface.inc.php");
	if ( ! function_exists( 'wp_handle_upload' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	if (!is_plugin_active("seda-micro/seda-micro.php"))
	{
		die("Plugin is disabled");
	}
	$cms = new WPInterface();
}
else
{
	if (!defined('DRUPAL_ROOT'))
	{
		define('DRUPAL_ROOT', dirname(dirname(dirname(dirname(__DIR__)))));
	}
	require_once(__DIR__."/includes/dl_interface.inc.php");
	$cms = new DLInterface();
	require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
	drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
	if (!module_exists("sedamicro") && !defined('NO_AUTH'))
	{
		die("Module is disabled");
	}
}
return $cms;
}
getCMS();
?>
