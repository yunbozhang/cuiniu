<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_excel {
	static function save_excel($name,$arr) {
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$name);  //在此设置导出数据文件名称
		header("content-Type=text/html;charset='gbk'");
		$str_cont="";
		foreach($arr as $item) {
			$str_cont .= implode("\t",$item)."\t\n";
		}
		if(cls_config::DB_CHARSET == "utf8" ) $str_cont = fun_format::utf8_gbk($str_cont);
		echo $str_cont;
		exit;
	}
	static function save_htmlexcel($name,$arr) {
		header("Content-type:application/vnd.ms-excel");
		//header("Content-Disposition:attachment;filename=".$name);  //在此设置导出数据文件名称
		header('Content-Disposition: filename='.$name);
		//header("content-Type=text/html;charset='gbk'");
		$str_charset = cls_config::DB_CHARSET;
		if(cls_config::DB_CHARSET == "utf8" ) $str_charset = 'utf-8';
		?>
		<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<meta http-equiv="Content-type" content="text/html;charset=<?php echo $str_charset;?>" />
		</head>
		<body>
		<table x:str border=1 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">
		<?php foreach($arr as $row) {?>
			<tr>
				<?php foreach($row as $col) {?>
				<td><?php echo $col;?></td>
				<?php }?>
			</tr>
		<?php }?>
		<table>
		</body></html>
		<?php
	}
}