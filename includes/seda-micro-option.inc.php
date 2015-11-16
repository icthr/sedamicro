<?php

function seda_micro_menu() {
	add_options_page( 'SedaMicro Options', 'SedaMicro', 'manage_options', 'seda-micro-id', 'seda_micro_options' );
}
function seda_micro_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$keys= get_option("seda_keys");
	if (isset($_GET['gen_key']))
	{
		$seda_keys = new SedaCrypt();
		update_option("seda_keys", $seda_keys->generate(),0);
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p><pre>';
	echo $keys['public'];
	echo '</pre>
<form type=post >
<input type=submit value="Generate new Key" />
<input type=hidden name=gen_key value=1 />
<input type=hidden name=page value=seda-micro-id />
</form>
</div>';
}
?>

