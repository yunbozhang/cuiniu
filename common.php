<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
$query_string = $_SERVER['QUERY_STRING'];
$arrsplit = explode("klkkdjurlsplit" , $query_string);
if(count($arrsplit)>1) {
	$_GET['klkkdj_oldgeturl'] = $_GET;
	$query = urldecode($arrsplit[0]);
	$arr = explode("&" , $query);
	foreach($arr as $item) {
		$arrx = explode("=" , $item);
		if(count($arrx)<2) continue;
		if(count($arrx)>2) {
			$a1 = $arrx[0];
			unset($arrx[0]);
			$a2 = implode("=" , $arrx);
		} else {
			$a1 = $arrx[0];
			$a2 = $arrx[1];
		}
		$_GET[$a1] = $a2;
	}
}
cls_app::on_load("common");