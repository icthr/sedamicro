<?php
require_once(__DIR__."/../incs-funcs.inc.php");
$crypt = new SedaCrypt();

$key= $cms->get_option("seda_keys");
$eq = $crypt->createEq($key["symmetric"]);
?>
<html dir=rtl>
<head>
<link rel="stylesheet"  href='basic.css' type='text/css' />
<meta charset="UTF-8">
</head>
<body>
<div class="element">
<?php echo $lang["choose_file"];?>
<br />
<div id = "loadingDiv"><img id = "myImage" src = "images/loading.gif" style="z-index:5;"></div>
<center>
<br/>
<div id="doit">
	<img src="images/rec.png" />
</div>
<form action="seda_up.php" id="fileForm" method="post" enctype="multipart/form-data">
<?php echo $lang["title"];?>:
    <input type="text" name="fileTitle" id="fileTitle" size=50 value="" /><br />
    <input type="file" name="fileToUpload" id="fileToUpload"><br />
    <?php echo $lang['please_answer']; ?>  </br >
    <input type="text" name="ga" value="" width=5 />
    <img height="100"  src="img.php?eq=<?php echo $eq['eq']; ?>" /> 
    <input type="hidden" name="hashKey" id ="hashKey" value="<?php echo $eq['answer'];?>"/><br />
    <input type="submit" value="<?php echo $lang['upload']; ?>" name="submit">
</form>
</center>
<?php
if($msg)
{
	echo "<div id='message'>\n".$msg."\n</div>";
}
?>
<a href="seda_up.php"><?php echo $lang['refresh'];?></a>
</div>
<script type="text/javascript">
doit.onclick = function(){fileToUpload.click();};
fileForm.onsubmit = function(){document.getElementById("loadingDiv").style.display="block";};
</script>
</body>
</html>

