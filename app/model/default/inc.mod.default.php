<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class inc_mod_default extends cls_base{

	/**
	 * admin 目录 初始类，启动 : 登录检查，权限检查
	 */
	function __construct($arr_v) {
		parent::__construct($arr_v);
		$this->init();
		$this->this_login_user = cls_obj::get("cls_user");
		$obj_tips = cls_obj::db()->get_one("select sum(tips_num) as 'num' from " . cls_config::DB_PRE . "sys_user_tips where tips_user_id='" . cls_obj::get("cls_user")->uid . "'");
		if($obj_tips['num']>99) $obj_tips['num'] = '99+';
		$this->user_tips = $obj_tips['num'];
		$this->uid = cls_obj::get("cls_user")->uid;
	}
	//初始化通用变量
	function init() {
		//当前地区
		if(fun_is::set("app_area_id")) {
			$area_id = (int)fun_get::get("app_area_id");
			cls_session::set_cookie("area_id" , $area_id);
			$obj_rs = cls_obj::db()->get_one("select area_id,area_name from " .cls_config::DB_PRE . "sys_area where area_id='" . $area_id . "' limit 0,1");
			$this->area_id = $area_id;
			if(!empty($obj_rs)) {
				cls_session::set_cookie("area_name" , $obj_rs['area_name']);
				$this->area_name = $obj_rs['area_name'];
			}
		} else {
			$area_id = (int)cls_session::get_cookie("area_id");
			$area_name = cls_session::get_cookie("area_name");
			if(empty($area_id)) {
				$area_id = (int)cls_config::get("area_default","meal");
				if(!empty($area_id)) {
					$obj_rs = cls_obj::db()->get_one("select area_id,area_name from " .cls_config::DB_PRE . "sys_area where area_id='" . $area_id . "' limit 0,1");
					if(!empty($obj_rs)) {
						cls_session::set_cookie("area_name" , $obj_rs['area_name']);
						cls_session::set_cookie("area_id" , $area_id);
						$area_name = $obj_rs['area_name'];
					}
				}
			}
			$this->area_id = $area_id;
			$this->area_name = $area_name;
		}
		$this->site = fun_kj::get_site();
	}
	//取当前地区下级地区,拼按首字母分类
	function get_area_1($id = -1) {
		$arr_return = array();
		if($id<0) $id = (int)cls_config::get("area_city_id","meal");
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select area_id,area_name,area_val,area_jian,area_depth,area_pic,area_childs from " .cls_config::DB_PRE . "sys_area where area_pid='" . $id . "'  order by area_sort");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!empty($obj_rs['area_pic'])) $obj_rs['area_pic'] = fun_get::html_url($obj_rs['area_pic']);
			if(!empty($obj_rs['area_val'])) $obj_rs['area_name'] = $obj_rs['area_val'];
			unset($obj_rs['area_val']);
			$arr_return[] = $obj_rs;
		}
		return $arr_return;
	}
	//取所有地区
	function get_area_3() {
		$arr_return = array('list'=> array() , 'info' => array());
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select area_id as id,area_pid as pid,area_name as name,area_val as val from " .cls_config::DB_PRE . "sys_area order by area_pid,area_sort");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!empty($obj_rs['val'])) $obj_rs['name'] = $obj_rs['val'];
			unset($obj_rs['val']);
			$arr_return['list']['id_' . $obj_rs['pid']][] = $obj_rs['id'];
			$arr_return['info']['id_' . $obj_rs['id']] = $obj_rs;
		}
		return $arr_return;
	}
	//搜索分类
	function get_area_2($id = -1 , $skey = '') {
		$arr_return = array('pids' => array() ,'list' => array() );
		if($id<0) $id = (int)cls_config::get("area_city_id","meal");
		$depth = (int)cls_config::get('area_depth' , 'meal');
		$obj_db = cls_obj::db();
		$where = " where (area_name like '%" . $skey . "%' or area_val like '%" . $skey . "%' or area_jian like '%" . strtoupper($skey) . "%') and area_depth<=" . $depth;
		if($id>0) $where .= " and " . $obj_db->concat("," , "area_pids" , ",") . " like '%," . $id . ",%";
		$arr_pids = array();
		$obj_result = $obj_db->select("select area_id,area_name,area_val,area_jian,area_depth,area_pids,area_childs from " .cls_config::DB_PRE . "sys_area" . $where);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!empty($obj_rs['area_val'])) $obj_rs['area_name'] = $obj_rs['area_val'];
			unset($obj_rs['area_val']);
			$obj_rs['type'] = 'area';
			$arr_return['list'][] = $obj_rs;
			$arr = explode("," , $obj_rs['area_pids']);
			unset($arr[count($arr)-1]);
			if(count($arr)>0) $arr_pids[] = implode("," , $arr);
		}
		if(!empty($arr_pids)) {
			$arr_pids = array_unique($arr_pids);
			$ids = implode("," , $arr_pids);
			$obj_result = $obj_db->select("select area_id,area_name,area_val,area_jian,area_depth,area_pids,area_childs from " .cls_config::DB_PRE . "sys_area where area_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_return['pids']['id_' . $obj_rs['area_id']] = $obj_rs;
			}
		}
		//搜索店铺
		$obj_result = $obj_db->select("select shop_name,shop_id,shop_area from " .cls_config::DB_PRE . "meal_shop where shop_name like '%" . $skey . "%' or shop_jian like '%" . $skey . "%'");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['type'] = 'shop';
			$arr_return['list'][] = $obj_rs;
		}
		//搜索菜品
		$obj_result = $obj_db->select("select menu_title,menu_id,shop_id,shop_name,menu_price from " .cls_config::DB_PRE . "meal_menu a left join " . cls_config::DB_PRE . "meal_shop b on a.menu_shop_id=b.shop_id where menu_state=1 and menu_isdel=0 and menu_about_id<1 and menu_title like '%" . $skey . "%' or menu_jian like '%" . $skey . "%'");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['type'] = 'menu';
			$arr_return['list'][] = $obj_rs;
		}

		return $arr_return;
	}

	//取地区路径
	function get_path($pid) {
		$city_id = (int)cls_config::get("area_city_id","meal");
		$arr_return = array();
		$str_sql="select area_id,area_name,area_pid from " . cls_config::DB_PRE . "sys_area where area_id='".$pid."'";
		$obj_result = cls_obj::db()->query($str_sql);
		if($obj_rs = cls_obj::db()->fetch_array($obj_result))	{
			$arr_return[] = $obj_rs;
			if($obj_rs['area_id']!=$city_id) {
				$arr = $this->get_path($obj_rs['area_pid']);
				if(!empty($arr)) $arr_return = array_merge($arr,$arr_return);
			}
		}
		return $arr_return;
	}
	/* 将购物车数据转换成数组格式 shopid:1,2,3|2,3
	 *
	 */
	function format_cart($cart_ids) {
		$arr_return = array("shop_ids" => array() , "menu_ids" => array() );
		$arr = explode("||" , $cart_ids);
		foreach($arr as $item) {
			$arr_cart = $arr_cart_id = array();
			$arr_1 = explode(":" , $item);
			if(count($arr_1)<2) continue;
			$arr_2 = explode("|" , $arr_1[1]);
			foreach($arr_2 as $menu) {
				if(empty($menu)) continue;
				$arr_3 = explode("," , $menu);
				$arr_cart_id = array_merge($arr_cart_id , $arr_3);
				$arr_cart[] = $arr_3;
			}
			if(empty($arr_cart_id)) continue;
			$arr_cart_id = array_unique($arr_cart_id);
			$arr_return["shop_" . $arr_1[0]] = array("ids" => $arr_cart_id , "cart" => $arr_cart ,"shop_id" => $arr_1[0]);
			$arr_return["shop_ids"][] = $arr_1[0];
			$arr_return["menu_ids"] = array_merge($arr_cart_id , $arr_return["menu_ids"]);
		}
		return $arr_return;
	}
	/**
	 * 统一获取分页样式
	 * arr_info : 数组 , 值为 : 
	 * 返回 : 分页html字符串
	 */
	function get_pagebtns( $arr_info , $listnum = 10 ) {
		$viewdir = cls_app::$perms['viewdir'];
		if(empty($viewdir)) $viewdir = 'default';
		$viewdir = KJ_DIR_ROOT . KJ_WEBCSS_PATH . '/' . $viewdir . '/css';
		if(is_dir($viewdir)) return $this->get_pagebtns2( $arr_info , $listnum);
		if($arr_info['total'] < 1) return '';
		$prepg = $arr_info['page']-1;
		$nextpg = $arr_info['page']+1;//$page == $pages ? 0 : ($page+1);
		$str_left="";
		$str_right="";
		$agent = fun_get::agent();
		$pagenav ='<li class="info">共:<font color="#ff6600">'.$arr_info['total'].'</font> 条&nbsp;页 '.$arr_info['page'].'/'.$arr_info['pages'];
		if(in_array($agent,array('','ipad'))) $pagenav .= '&nbsp;&nbsp;页大小<input type="text" name="url_pagesize" value="' . $arr_info['pagesize'] . '" style="width:20px;color:#888888" onkeyup="kj.page.size(event,\''.$this->app_module . "." . $this->app . "." . $this->app_act . '\',\''.$this->app_dir.'\');" id="id_page_size">';
		$pagenav .= '</li>';
		$str_x="";
		if($arr_info["pages"] > 10) {
			if($arr_info['page']>5){
				$lng_pre=$arr_info['page']-5;
				$lng_next=$arr_info['page']+5;
				$str_left="<li><a href='javascript:kj.page.go(1);'>首页</a></li>";
				$str_right="<li><a href='javascript:kj.page.go(".$arr_info['pages'].");'>尾页</a></li>";
			}else{
				$lng_pre=1;
				$lng_next=10;
				$str_right="<li><a href='javascript:kj.page.go(".$arr_info['pages'].");'>尾页</a></li>";
			}
		}else{
			$lng_pre=1;
			$lng_next=$arr_info['pages'];
		}
		if($lng_next>=$arr_info['pages']){
			$lng_next=$arr_info['pages'];
			$str_right="";
		}
		for($i=$lng_pre;$i<=$lng_next;$i++){
			$str_sel="";
			if($i==$arr_info['page']) $str_sel=" class='x_sel'";
			$str_x.="<li".$str_sel."><a href='javascript:kj.page.go(".$i.");'>[".$i."]</a></li>";
		}
		$pagenav.=$str_left.$str_x.$str_right;
		if(in_array($agent,array('','ipad'))) $pagenav .= "<li class='x_go'><input type='text' name='go_page' id='id_go_page' value='' class='pTxt1 x_txt' onkeyup='kj.page.page_keyup(event);'>&nbsp;&nbsp;<a href=\"javascript:kj.page.go(kj.obj('#id_go_page').value);\">跳转</a></li>";
		return $pagenav;
	}
	function get_pagebtns2( $arr_info , $listnum = 10 ) {
		$arr_return = array("pre" => 0, "next" => 0,"list" => array() ,"premore" => 0 , "nextmore" => 0 , "start" => 1 , "end" => $arr_info['pages']);
		if($arr_info['total'] < 1) return $arr_return;
		$arr_return['pre'] = max(0,$arr_info['page']-1);
		$arr_return['next'] = $arr_info['page']+1;
		if($arr_return['next'] > $arr_info['pages']) $arr_return['next'] = 0;

		if($arr_info["pages"] > $listnum) {
			$ii = intval($listnum/2);
			if($arr_info['page'] > $ii){
				$lng_pre=$arr_info['page'] - $ii;
				$lng_next=$arr_info['page'] + $ii;
			}else{
				$lng_pre=1;
				$lng_next = $listnum;
			}
		}else{
			$lng_pre=1;
			$lng_next=$arr_info['pages'];
		}
		if($lng_pre < 1) $lng_pre = 1;
		if( $lng_next >= $arr_info['pages'] ) $lng_next = $arr_info['pages'];
		$arr_return['start'] = $lng_pre;
		$arr_return['end'] = $lng_next;

		for($i=$lng_pre;$i<=$lng_next;$i++){
			$arr_return['list'][] = $i;
		}
		return $arr_return;
	}
	//获取指定目录文章列表
	function get_folder_article($key , $limit = '') {
		$arr_return = array();
		$arr_where = array();
		$where = ' where article_state>0 and article_isdel=0';
		if(!empty($key)) $where .= " and channel_key='" . $key . "'";
		$obj_result = cls_obj::db()->select("select article_id,article_title,article_addtime from " . cls_config::DB_PRE . "article_channel a left join " . cls_config::DB_PRE . "article b on a.channel_id=b.article_channel_id" . $where . $limit);
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$obj_rs["addtime"] = date("Y/m/d" , $obj_rs["article_addtime"]);
			$arr_return[] = $obj_rs;
		}
		return $arr_return;
	}
	/* 取店铺当天经营菜品条件
	 *
	 */
	function get_menu_today_where($shop_id) {
		$obj_db = cls_obj::db();
		$where = " where menu_shop_id='" . $shop_id . "' and menu_state>0 and menu_isdel=0 and menu_about_id<1";
		//取每天自定义菜品
		$arr_opentime = tab_meal_shop::get_opentime($shop_id);
		$date_period = $arr_opentime['nowindex'];
		if($date_period>0) {
			$where_x = " and (today_date_period='".$date_period."' or today_date_period=0)";
		} else {
			$where_x = " and today_date_period='".$date_period."'";
		}
		$arr_x = array();
		$obj_today = $obj_db->select("select today_menu_id from " . cls_config::DB_PRE . "meal_menu_today where today_shop_id='" . $shop_id . "' and today_date='" . strtotime(date('Y-m-d' , TIME)) . "'" . $where_x);
		while($obj_rs = $obj_db->fetch_array($obj_today)) {
			$arr_x[] = $obj_rs["today_menu_id"];
		}
		$str_ids = implode("," , $arr_x);
		if(!empty($str_ids)) {
			$where .=" and (menu_mode!=2 or menu_id in(" . $str_ids . "))";
		} else {
			$where .=" and menu_mode!=2";
		}
		return $where;
	}
	//获取店铺营销活动
	function get_shop_act($shop_id , $price , $num , $order_pay_method) {
		$arr_return = array();
		$date = date("Y-m-d H:i:s");
		$i = 1;
		$arr_where = $arr_num_where = $arr_time_num_where = array();
		$obj_result = cls_obj::db()->select("select act_id,act_name,act_where,act_method,act_where_val,act_method_val from " . cls_config::DB_PRE . "meal_act where act_isdel=0 and act_state>0 and (act_shop_id='" . $shop_id . "' or act_shop_id=0) and act_starttime<='" . $date . "' and act_endtime>='" . $date . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$obj_rs["index"] = $i;
			$i++;
			if($obj_rs['act_where']==1 && $price>=(float)$obj_rs['act_where_val']) {//大于指定金额
				$obj_rs['where_val'] = (float)$obj_rs['act_where_val'];
				$arr_return[] = $obj_rs;
			} else if($obj_rs['act_where']==2) {//指定时间
				$arr = explode("," , $obj_rs['act_where_val']);
				$time1 = (int)$arr[0];
				$time2 = (count($arr)>1) ? (int)$arr[1] : 0;
				$time = (int)date("Hi");
				if($time>=$time1 && $time<$time2) {
					$x = (int)substr($time,0,-2);
					$x1 = (int)substr($time2,0,-2);
					if($x == $x1) {
						$x = (int)substr($time,-2);
						$x1 = (int)substr($time2,-2);
						$obj_rs['time'] = ($x1-$x)*60*1000;
					} else {
						$obj_rs['time'] = ($x1-$x)*60*60*1000;
					}
					$arr_return[] = $obj_rs;
				}
			} else if($obj_rs['act_where']==3 && $num>=(int)$obj_rs['act_where_val']) {//达到指定数量
				$obj_rs['where_val'] = (int)$obj_rs['act_where_val'];
				$arr_return[] = $obj_rs;
			} else if($obj_rs['act_where']==4) {//指定时间，达到指定数量
				$arr = explode("," , $obj_rs['act_where_val']);
				$time1 = (int)$arr[0];
				$time2 = (count($arr)>1) ? (int)$arr[1] : 0;
				$obj_rs['where_val'] = $lng_num = (count($arr)>2) ? (int)$arr[2] : 0;
				$time = (int)date("Hi");
				if($time>=$time1 && $time<$time2 && $num>=$lng_num) {
					$x = (int)substr($time,0,-2);
					$x1 = (int)substr($time2,0,-2);
					if($x == $x1) {
						$x = (int)substr($time,-2);
						$x1 = (int)substr($time2,-2);
						$obj_rs['time'] = ($x1-$x)*60*1000;
					} else {
						$obj_rs['time'] = ($x1-$x)*60*60*1000;
					}
					$arr_return[] = $obj_rs;
				}
			} else if($obj_rs['act_where']==6) {//首次下单
				if(cls_obj::get("cls_user")->uid>0) {
					if($orderone == -1) {
						$obj_shopone = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $shop_id . "' and order_user_id='" . cls_obj::get("cls_user")->uid . "' and (order_state>=0 or order_state=-2)");
						if(empty($obj_shopone)) {
							$orderone_shop = 1;
							$obj_one = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_user_id='" . cls_obj::get("cls_user")->uid . "' and (order_state>=0 or order_state=-2)");
							$orderone = empty($obj_one) ? 1 : 0;
						} else {
							$orderone_shop = 0;
							$orderone = 0;
						}
					}
					if(($obj_rs['act_shop_id']==0 && $orderone==1) || ($obj_rs['act_shop_id']==$shop_id && $orderone_shop==1)) $arr_return[] = $obj_rs;
				}
			} else if( ($obj_rs['act_where']==7 && $order_pay_method=='onlinepayment') || $obj_rs['act_where']==5) {//
				$arr_return[] = $obj_rs;
			}
		}
		return $arr_return;
	}
	//取满足指定地区送餐条件的店铺
	function get_area_shop($area_id) {
		$cache_time = (int)cls_config::get("area_shoplist","cache");
		$cache_key = "area_" . $area_id . "_shopid";
		$arr_shop = cls_cache::get($cache_key,"area_shop",$cache_time);
		if($arr_shop === null) {
			$obj_db = cls_obj::db();
			$arr_shop = array('id'=>array(),'list'=>array());
			$obj_rs = $obj_db->get_one("select area_pids from " . cls_config::DB_PRE . "sys_area where area_id='" . $area_id . "'");
			if(!empty($obj_rs)) {
				$sql = "select dispatch_shop_id,dispatch_price,dispatch_time,dispatch_addprice from " . cls_config::DB_PRE . "meal_dispatch where dispatch_area_id in(" . $obj_rs['area_pids'] . ")";
				$obj_result = $obj_db->select($sql);
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					$arr_shop['id'][] = $obj_rs['dispatch_shop_id'];
					if((int)$obj_rs['dispatch_price']==$obj_rs['dispatch_price']) $obj_rs['dispatch_price'] = (int)$obj_rs['dispatch_price'];
					if((int)$obj_rs['dispatch_addprice']==$obj_rs['dispatch_addprice']) $obj_rs['dispatch_addprice'] = (int)$obj_rs['dispatch_addprice'];
					$arr_shop['list']['id_' . $obj_rs['dispatch_shop_id']] = $obj_rs;
				}
			}
			cls_cache::set($arr_shop,$cache_key,"area_shop");
		}
		return $arr_shop;
	}
	function get_comment_list($id) {
		$lng_pagesize = 10;
		$arr_return = array("list" => array() , "pagebtns" => "");
		$obj_db = cls_obj::db();
		$lng_page = (int)fun_get::get("page");
		$str_where = " where comment_menu_id='" . $id . "'";
		//取分页信息
		$arr_return["list"] = array();
		$arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_menu_comment" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT a.comment_addtime,a.comment_val,a.comment_user_id,b.user_experience,b.user_netname,b.user_name,c.comment_beta,c.comment_recont,comment_re_time,c.comment_pic FROM ".cls_config::DB_PRE."meal_menu_comment a left join " . cls_config::DB_PRE . "sys_user b on a.comment_user_id=b.user_id left join " . cls_config::DB_PRE . "meal_order_comment c on a.comment_order_id=c.comment_order_id" . $str_where . " order by a.comment_id desc" . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['val'] = $obj_rs['comment_val']/5*100;
			$obj_rs['addtime'] = date("Y-m-d H:i" , $obj_rs['comment_addtime']);
			$arr_uid[] = $obj_rs['comment_user_id'];
			$obj_rs['pic'] = empty($obj_rs['comment_pic']) ? array() : explode("||" , $obj_rs['comment_pic']);
			$obj_rs['retime'] = empty($obj_rs['comment_re_time']) ? '' : date("Y-m-d H:i" , $obj_rs['comment_re_time']);
			$obj_rs['level'] = tab_sys_user::get_level($obj_rs['user_experience']);
			$obj_rs['user_name'] = empty($obj_rs['user_netname']) ? $obj_rs['user_name'] : $obj_rs['user_netname'];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				if(empty($arr_return["list"][$i]['user_name'])) $arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['comment_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);
		return $arr_return;
	}

}