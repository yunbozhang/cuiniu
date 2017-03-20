<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
 class ctl_weixin_media extends mod_weixin_media {
	//默认浏览页
	function act_default() {
		$type = fun_get::get("type");
		$this->type = $type;
		$this->arr_list = $this->get_media_list($type);
		$arr_type = cls_config::get('mediatype' , 'weixin' , '' , '');
		$this->allowext = (isset($arr_type[$type])) ? implode("," , $arr_type[$type]) : '';
		$arr_size = cls_config::get('mediasize' , 'weixin' , '' , '');
		$this->allowsize = (isset($arr_size[$type])) ? $arr_size[$type] : 0;
		return $this->get_view();
	}

	function act_upload() {
		$file = fun_get::get("file");
		$type = fun_get::get("type");
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$arr_return = cls_weixin::on_media_upload($file , $type , $site_id);
		return fun_format::json($arr_return);
	}
}