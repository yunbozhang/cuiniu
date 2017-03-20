<?php
$domain = $_SERVER["HTTP_HOST"];
$dirpath = "/test";
$basename = isset($_SERVER["SCRIPT_NAME"]) ? basename($_SERVER["SCRIPT_NAME"]) : "index.php";
$admin_file = defined("KJ_ADMIN_FILENAME") ? KJ_ADMIN_FILENAME : "master.php";
return array(
	"domain"          => "http://" . $domain, //网站域名
	"dirpath"         => $dirpath, //二级目录
	"url"             => "http://" . $domain . $dirpath, //网址
	"admin_uids"      => "1",//超级管理员账号，多个用 , 号分隔
	"basename"        => $basename,
	"admin_file"        => $admin_file,
  "kjcms_key"       => "",//kjcms.com官网生成
  "domainext"       => array("com.cn","net.cn","org.cn","gov.cn","com.hk","com.tw","com.au"),//kjcms.com官网生成
);