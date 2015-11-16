<?php
require_once(__DIR__."/incs-funcs.inc.php");
$cms = getCMS();
if (!isset($_GET['eq']))
{
	die();
}

$c = new SedaCrypt();
$key= $cms->get_option("seda_keys");
$crypted = $c->c2a($_GET['eq'],"",$key['symmetric']);
if (!empty($crypted))
{
	$c->str2img($crypted);
}
else
{
	$c->str2img("Error!");
}

?>
