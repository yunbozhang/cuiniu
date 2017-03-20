<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_act_lottery extends mod_act_lottery{
	function act_default(){
		$id = (int)fun_get::get("id");
		$this->award_log = $this->get_award_log($id);
		$this->lottery_info = $this->get_lottery($id);
		if($this->lottery_info['lottery_type'] == 0) {
			return $this->get_view('ggk');//�ιν�
		} else {
			return $this->get_view('rotate');//���˴�ת��
		}
	}

	function act_lottery() {
		$arr_return = $this->get_award();
		return fun_format::json($arr_return);
	}
}