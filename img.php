<?php
require_once(__DIR__."/incs-funcs.inc.php");
$cms = getCMS();
$eq = htmlspecialchars($_GET['eq']);
if (!isset($eq) || (strlen($eq)>255) || (strlen($eq)<16))
{
	die();
}

$c = new SedaCrypt();
$key= $cms->get_option("seda_keys");
$crypted = $c->c2a($eq,"",$key['symmetric']);
if (!empty($crypted))
{
	$c->str2img($crypted);
}
else
{
	$c->str2img("Error!");
}

?>
