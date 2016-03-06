<?php
require_once(__DIR__."/includes/lang.inc.php");
include_once(__DIR__."/includes/crypt.inc.php");	
include_once(dirname(__File__) .'/includes/seda-micro-option.inc.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(__DIR__."/includes/wp_interface.inc.php");
if (!is_plugin_active("sedamicro/seda-micro.php"))
{
	die("Plugin is disabled");
}
//$cms = new WPInterface();
?>
