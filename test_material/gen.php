<?php
include("includes/crypt.inc.php");
$crypt = new SedaCrypt();
if(isset($_POST['key']))
{
$key=hex2bin('000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f');
//echo "\nx=".$x."\n";
//$b = $crypt->c2a($a,0,$key);
//echo $b;
//	echo $crypt->c2a($crypt->a2c($_POST['data'],0,123 ),0,123 );
//	echo '</textarea>';
/*
$message = 'Ready your ammunition; we attack at dawn.';
$encrypted = SymmetricCrypto::encrypt($message, $key);
$decrypted = SymmetricCrypto::decrypt($encrypted, $key);
var_dump($encrypted, $decrypted);
*/
/*
	echo '<textarea cols=50>';
	echo base64_encode( $crypt->encrypt($_POST['data']."|".$_POST['time']."|".$_POST['pass'],$_POST['key']) );
	echo '</textarea>';
	echo '<textarea cols=50>';
	echo base64_encode( $crypt->a2c($_POST['data'],0,123 ));
	echo '</textarea>';
	echo '<textarea cols=50>';
*/
$x = rand(1,99);
$y = rand(1,99);
$op = rand(0,3);
$ops = array("+", "*","-", "/");

$eq = $x .$ops[$op].$y;
//since input is formed by us not user, it will be safe to use eval()
$answer = 0;
eval('$answer='.$eq.";");
echo $eq;
echo "=".$answer;
$a = $crypt->a2c($eq."=?","",$key,$x );
$ans = $crypt->a2c($answer,"",$key, $x);
echo "\n<a href=\"http://ec2-54-71-227-112.us-west-2.compute.amazonaws.com/sedatest/wp-content/plugins/seda-micro/img.php?answer=".$a."&a=".$ans."\">link</a>\n";
}
?>

<html>
<form method=post>
<textarea cols=80 rows=20	name=key >
<?php if (isset($_POST['key'])) { echo $_POST['key']; }?>
</textarea>
<br />
Data: <input type=text name=data value="asas"/> <br />
Time: <input type=text name=time value="<?php echo date("U"); ?>"/><br />
Pass: <input type=text name=pass value="testpass" /><br />
<input type=submit />
</form>
</html>

