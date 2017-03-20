
<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_ajax extends mod_ajax {
	//保存指定id收货信息
	function act_saveinfo() {
		$arr_info = $this->on_save_info();
		return fun_format::json($arr_info);
	}
	//删除收货信息
	function act_address_del() {
		$arr = fun_format::json( $this->on_address_del() );
		return $arr;
	}
	function act_address_save() {
		$arr_info = $this->on_address_save();
		return fun_format::json($arr_info);
	}
	//提交定单
	function act_saveorder() {
		$menu_list = fun_format::json( $this->save_order() );
		return $menu_list;
	}
	function act_shop_table_reserve_save(){
		$arr_return = $this->shop_table_reserve_save();
		return fun_format::json($arr_return);
	}

	//编辑用户信息
	function act_useredit() {
		$arr = fun_format::json( $this->on_useredit() );
		return $arr;
	}
	//注册
	function act_reg() {
		$arr = fun_format::json( $this->on_reg() );
		return $arr;
	}
	//店铺注册
	function act_shop_reg() {
		$arr = fun_format::json( $this->on_shop_reg() );
		return $arr;
	}
	//找回密码第一步
	function act_findpwd_step1() {
		$arr = fun_format::json( $this->on_findpwd_step1() );
		return $arr;
	}
	//找回密码第二步
	function act_findpwd_step2() {
		$arr = fun_format::json( $this->on_findpwd_step2() );
		return $arr;
	}
	//重置新密码
	function act_findpwd_step3() {
		$arr = fun_format::json( $this->on_findpwd_step3() );
		return $arr;
	}
	//验证信息
	function act_verify_mobile() {
		$arr = fun_format::json( $this->on_verify_mobile() );
		return $arr;
	}
	//保存留言
	function act_msg_save() {
		$arr = fun_format::json( $this->on_msg_save() );
		return $arr;
	}
	//收藏店铺
	function act_collect() {
		$shop_id = (int)fun_get::get("shop_id");
		$arr =  $this->on_collect_shop($shop_id);
		$arr['shop_id'] = $shop_id;
		return fun_format::json($arr);
	}
	//取消收藏
	function act_collect_cancel() {
		if(!cls_obj::get("cls_user")->is_login()) return fun_format::json(array("code" => 500 , "msg" => "您还未登录或登录已过期，请重新登录"));
		$shop_id = (int)fun_get::get("shop_id");
		$arr =  tab_meal_collect::on_delete("" , "collect_for_id='" . $shop_id . "' and collect_user_id='" . cls_obj::get("cls_user")->uid . "' and collect_type=1");
		$arr['shop_id'] = $shop_id;
		return fun_format::json($arr);
	}
	function act_menu_comment() {
		$id = (int)fun_get::get("id");
		$arr_list = $this->get_comment_list($id);
		return fun_format::json($arr_list);
	}
	function act_menu_sold() {
		$id = (int)fun_get::get("id");
		$arr_list = $this->get_sold_list($id);
		return fun_format::json($arr_list);
	}
	function act_upmenu() {
		$arr = $this->on_upmenu();
		return fun_format::json($arr);
	}
	function act_reserve_delmenu() {
		$arr = $this->on_reserve_delmenu();
		return fun_format::json($arr);
	}
}