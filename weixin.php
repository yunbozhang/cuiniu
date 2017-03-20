<?php
include("inc.php");
if(isset($_GET["echostr"])) {
	echo $_GET["echostr"];exit;
}
cls_weixin::on_exe();