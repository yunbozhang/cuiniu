<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_weixin {
	static $perms;
	static $config = array();
	static function get_perms($key , $site_id = null) {
		if( empty(self::$perms) || ($site_id !== null && $site_id != self::$perms['site_id']) ) {
			self::$perms = fun_kj::get_site( $site_id );
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function get_access_token() {
		if(!defined('cls_klkkdj::KJ_VERISION')) {
			fun_base::url_jump("http://www.kjcms.com/api.php?app=copy&app_act=verify.none");
			exit;
		}
		$site_id = self::get_perms('site_id');
		$token = cls_cache::get('access_token' , 'weixin/' . $site_id , 3);
		if(empty($token)) {
			$appid = self::get_perms('appid');
			$appsecret = self::get_perms('appsecret');
			if(empty($appid) || empty($appsecret)) return '';
			$cont = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret);
			if(empty($cont)) return '';
			$arr = fun_format::toarray($cont);
			if(isset($arr['access_token'])) {
				$token = $arr['access_token'];
				cls_cache::set($token ,'access_token' , 'weixin/' . $site_id);
			}

		}
		return $token;
	}
	//上传媒体文件
	static function on_media_upload($path , $type , $site_id = 0) {
		$arr_type = self::get_perms('mediatype');
		if(!isset($arr_type[$type])) return array('code' => 500 , 'msg' => '上传类型不存在');
		$real_path = fun_get::real_path($path);
		$ext = strtolower(end(explode("." , $real_path)));
		$exts = implode(',' , $arr_type[$type]);
		if(!in_array($ext , $arr_type[$type])) return  array('code' => 500 , 'msg' => '只允许上传扩展名为' . $exts . '的文件');
		if(!is_file($real_path)) return array("code" => 500 , "msg" => "上传失败");

		$access_token = self::get_access_token();
		if(empty($access_token)) return array('code' => 500 , 'msg' => '微信权限不够');
		$url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $access_token . '&type=' . $type;
		$arr_return = fun_base::post($url , array() , array(basename($real_path) => $real_path) );
		if($arr_return['code'] == 0) {
			$arr_media = fun_format::toarray($arr_return['cont']);
			if(isset($arr_media['media_id'])) {
				$size = filesize($real_path);
				//保存进数据库媒体表
				$arr_fields = array(
					'media_id' => $arr_media['media_id'],
					'media_type' => $arr_media['type'] , 
					'media_time' => $arr_media['created_at'],
					'media_uid' => cls_obj::get('cls_user')->uid,
					'media_file' => $path,
					'media_size' => $size,
					'media_site_id' => $site_id,
				);
				$arr_save = cls_obj::db_w()->on_insert(cls_config::DB_PRE."weixin_media",$arr_fields);
				$arr_media['code'] = 0;
			} else {
				$arr_media['msg'] = (isset($arr_media['errmsg'])) ? $arr_media['errmsg'] : '';
				$arr_media['code'] = 500;
			}
		} else {
			$arr_media = $arr_return;
		}
		return $arr_media;
	}
	//下载媒体文件
	static function get_media($mid) {
		$access_token = self::get_access_token();
		if(empty($access_token)) return '';
		$cont = file_get_contents('http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=' . $access_token . '&media_id=' . $mid);
		return $cont;
	}
	//接收消息
	static function get_msg() {
		$arr_return = array();
		$cont = file_get_contents("php://input");
		//$cont = file_get_contents(KJ_DIR_ROOT . "/test.txt");
		if(empty($cont)) return $arr_return;
		$request = simplexml_load_string($cont , 'SimpleXmlElement' , LIBXML_NOCDATA);
		$arr_return = fun_format::toarray($request);
		return $arr_return;
	}
	//验证
	static function check_signature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];	
				
		$token = self::get_access_token();
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return fun_get::get("echostr");
		} else {
			return '';
		}
	}
	//处理消息
	static function on_exe() {
		$arr_msg = self::get_msg();
		//$arr_msg = array('ToUserName'=>'dkd','FromUserName'=>'bbb','MsgType'=>'text','Content'=>'d');
		if(empty($arr_msg['ToUserName'])) return;
		self::$config['wx_id'] = $arr_msg['FromUserName'];
		$arr_message = $arr_cont = array();
		if($arr_msg['MsgType'] == 'text') {
			$arr = explode(",",str_replace("，" , "," , $arr_msg['Content']));
			if(is_numeric($arr[0])) {
				//是店铺用户
				$obj_shop = cls_obj::db()->get_one("select shop_id from " . cls_config::DB_PRE . "meal_shop where shop_weixin_id='" . $arr_msg['FromUserName'] . "'");
				if(!empty($obj_shop)) {
					$oid = $arr[0];
					if(count($arr)>1) {
						unset($arr[0]);
						$beta = implode("," , $arr);
					} else {
						$beta = '';
					}
					$state = (empty($beta)) ? 1 : -1;
					$where = " order_shop_id='" . $obj_shop['shop_id'] . "' and right(order_id," . strlen($oid) . ")='" . $oid . "'";
					//查看订单是否存在
					$obj_order = cls_obj::db()->get_one("select order_id,order_state from " . cls_config::DB_PRE . "meal_order where" . $where);
					if(empty($obj_order)) {
							$arr_cont['cont'] = '订单不存在';
					} else {
						if($obj_order['order_state']!=0) {
							$arr_cont['cont'] = '无效订单或已处理过';
						} else {
							$where .= " and order_state=0";
							$arr_re = tab_meal_order::on_state('' , $state , $beta , $where);
							$arr_cont = array();
							if($arr_re['code'] == 0) {
								$arr_cont['cont'] = ($state==-1) ? '取消订单成功' : '成功接收订单，请尽快配送';
							} else {
								$arr_cont['cont'] = '处理失败，原因：' . $arr_msg['msg'];
							}
						}
					}
				}
			}
			if(empty($arr_cont)) {
				$msgmode = self::get_perms('msgmode');
				$site_id = self::get_perms('site_id');
				$shop_id = self::get_perms('shop_id');
				//取自动回复消息或关键词消息
				$arr_1 = tab_weixin_message::get_rekeywords($arr_msg['Content'] , $site_id);
				$count = count($arr_1)-1;
				if($count>0) {
					$ii = rand(0,$count);
					$arr_message = $arr_1[$ii];
				} else if(!empty($arr_1)) {
					$arr_message = $arr_1[0];
				} else if(!empty($msgmode)){
					//主站才搜索店铺
					$arr_message = array('message_type' => 'news' , 'news' => array());
					if(empty($site_id)) {
						$obj_result = cls_obj::db()->select("select shop_id,shop_name,shop_desc,shop_area,shop_pic,shop_pic_small from " . cls_config::DB_PRE . "meal_shop where shop_name like '%" . $arr_msg['Content'] . "%' or shop_jian like '" . strtolower($arr_msg['Content']) . "%' limit 0,10");
						while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
							if(empty($obj_rs['shop_pic_small'])) $obj_rs['shop_pic_small'] = $obj_rs['shop_pic'];
							if(empty($arr_message['news'])) {
								if(empty($obj_rs['shop_pic'])) $obj_rs['shop_pic'] = $obj_rs['shop_pic_small'];
								$pic = fun_get::html_url($obj_rs['shop_pic'] , 1);
							} else {
								$pic = fun_get::html_url($obj_rs['shop_pic_small'] , 1);
							}
							$url = cls_config::get('url') . '/index.php?app_weixin=1&app_act=shop&id=' . $obj_rs['shop_id'];
							$shop_id = $obj_rs['shop_id'];
							$arr_message['news'][] = array(
								'title' => $obj_rs['shop_name'],
								"desc" => strip_tags(fun_get::filter($item['shop_desc'],true)),
								"pic" => $pic,
								"url" => $url,
							);
						}
						if(count($arr_message['news']) == 1) {
							//搜索该店菜品
							$obj_result = cls_obj::db()->select("select menu_title,menu_price,menu_pic,menu_pic_small,menu_intro from " . cls_config::DB_PRE . "meal_menu where menu_shop_id='" . $shop_id . "' and menu_tj=1 and menu_state>0 and menu_isdel=0 order by menu_price limit 0,4");
							while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
								if(empty($obj_rs['menu_pic_small'])) $obj_rs['menu_pic_small'] = $obj_rs['menu_pic'];
								if(!empty($obj_rs['menu_pic_small'])) $pic = fun_get::html_url($obj_rs['menu_pic_small'] , 1);
								$arr_message['news'][] = array(
									'title' => $obj_rs['menu_title'] . "(" . cls_config::get("coinsign","sys") . $obj_rs['menu_price'] . ")",
									"desc" => strip_tags(fun_get::filter($item['menu_intro'],true)),
									"pic" => $pic,
									"url" => $url,
								);
							}
						}
					}
					//搜索菜品
					if(empty($arr_message['news'])) { 
						//搜索该店菜品
						$where = " where (menu_title like '%" . $arr_msg['Content'] . "%' or menu_jian like '" . strtolower($arr_msg['Content']) . "%') and menu_state>0 and menu_isdel=0";
						if(!empty($shop_id)) $where .= " and menu_shop_id='" . $shop_id . "'";
						$obj_result = cls_obj::db()->select("select menu_title,menu_price,menu_pic,menu_pic_small,menu_intro,shop_id,shop_name,shop_pic,shop_pic_small from " . cls_config::DB_PRE . "meal_menu a left join " . cls_config::DB_PRE . "meal_shop b on a.menu_shop_id=b.shop_id" . $where . " order by menu_tj desc,menu_sold desc limit 0,5");
						while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
							if(empty($arr_message['news'])) {
								if(empty($obj_rs['menu_pic'])) $obj_rs['menu_pic'] = $obj_rs['menu_pic_small'];
								if(empty($obj_rs['shop_pic'])) $obj_rs['shop_pic'] = $obj_rs['shop_pic_small'];
								$pic = empty($obj_rs['menu_pic']) ? $obj_rs['shop_pic'] : $obj_rs['menu_pic'];
								$pic = fun_get::html_url($pic , 1);
							} else {
								if(empty($obj_rs['menu_pic_small'])) $obj_rs['menu_pic_small'] = $obj_rs['menu_pic'];
								if(empty($obj_rs['shop_pic_small'])) $obj_rs['shop_pic_small'] = $obj_rs['shop_pic'];
								$pic = empty($obj_rs['menu_pic_small']) ? $obj_rs['shop_pic_small'] : $obj_rs['menu_pic_small'];
								$pic = fun_get::html_url($pic , 1);
							}
							$url = cls_config::get('url') . '/index.php?app_weixin=1&app_act=shop&id=' . $obj_rs['shop_id'];
							$arr_message['news'][] = array(
								'title' => $obj_rs['menu_title'] . '【' . $obj_rs['shop_name'] . '】' . "(" . cls_config::get("coinsign","sys") . $obj_rs['menu_price'] . ")",
								"desc" => strip_tags(fun_get::filter($item['menu_intro'],true)),
								"pic" => $pic,
								"url" => $url,
							);
						}
					}
					if(empty($arr_message['news'])) $arr_message = array();
				}
			}
			if(empty($arr_cont)) {
				if(empty($arr_message)) {
					$customer_open = self::get_perms('customer_open');
					if($customer_open) {
						$arr_message = array('message_type' => 'transfer_customer_service');
					} else {
						$arr_message = tab_weixin_message::get_remsg($site_id);
					}
				}
				if(isset($arr_message['message_text'])) $arr_message['message_text'] = fun_get::filter($arr_message['message_text'],true);
				$arr_cont = self::format_echo($arr_message);
			}
		} else if($arr_msg['MsgType'] == 'event') {
			if($arr_msg['Event'] == 'subscribe') {//订阅
				$arr_message = tab_weixin_message::get_guanzhu($site_id);
				//关注事件
				self::on_guanzhu($arr_msg['FromUserName']);
				if(isset($arr_message['message_text'])) $arr_message['message_text'] = fun_get::filter($arr_message['message_text'],true);
				$arr_cont = self::format_echo($arr_message);
			} else if(strtolower($arr_msg['Event']) == 'click') {//自定义菜单click
				$key = str_replace('V1001_MENU_' , '' , $arr_msg['EventKey']);
				if(empty($arr_message)) $arr_message = tab_weixin_message::get_one(0,$key,$site_id);
				if(isset($arr_message['message_text'])) {
					$arr_message['message_text'] = fun_get::filter($arr_message['message_text'],true);
					$txt = trim($arr_message['message_text']);
					if(substr($txt,0,11) == 'cmd:redpack') {
						$arrcmd = explode(":" , $txt);
						$redpack_id = (int)$arrcmd[2];
						$arrx = tab_weixin_redpack::on_open($redpack_id,'',$arr_msg['FromUserName']);
						if($arrx['code'] == 0) {
							return;
						} else if($arrx['code'] == 1) {
							$arr_message['message_text'] = isset($arrcmd[3]) ? $arrcmd[3] : "没有抢到红包下次再试试~";
						} else {
							$arr_message['message_text'] = $arrx['msg'];
						}
					}
				}
				$arr_cont = self::format_echo($arr_message);
			} else if($arr_msg['Event'] == 'unsubscribe') {
				self::on_guanzhu_cancel($arr_msg['FromUserName']);
				return;
			} else if( strtolower($arr_msg['Event']) == 'location') {
				cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "weixin_user set user_position_lng='" . $arr_msg['Longitude'] . "',user_position_lat='" . $arr_msg['Latitude'] . "' where user_openid='" . $arr_msg['FromUserName'] . "'");
				return;
			}
		} else if($arr_msg['MsgType'] == 'location') {
			$arr_message = self::get_distance_shop($arr_msg['Location_Y'] , $arr_msg['Location_X']);
			if(empty($arr_message)) $arr_message = tab_weixin_message::get_remsg($site_id);
			if(isset($arr_message['message_text'])) $arr_message['message_text'] = fun_get::filter($arr_message['message_text'],true);
			$arr_cont = self::format_echo($arr_message);
		}
		$type = '';
		if(!empty($arr_message)) {
			$type = $arr_message['message_type'];
		}
		if(empty($type)) $type = 'text';
		if(empty($arr_cont) && $arr_message['message_type']!='transfer_customer_service') return;
		$xml = self::get_echo($arr_msg['FromUserName'] , $arr_cont , $type);
		echo $xml;
	}
	static function get_distance_shop($lng , $lat) {
			$arr_message = array('message_type' => 'news' , 'news' => array());
			$arr = cls_map::get_range($lng , $lat , 30);
			$arr_shop = array();
			$obj_result = cls_obj::db()->select("select shop_id,shop_name,shop_desc,shop_area,shop_pic,shop_pic_small,shop_position_lng,shop_position_lat from " . cls_config::DB_PRE . "meal_shop where (shop_state>0 or shop_state=-1) and shop_isdel=0 and shop_position_lat<=" . $arr['max_lat'] . " and shop_position_lat>=" . $arr['min_lat'] . " and shop_position_lng<=" . $arr['max_lng'] . " and shop_position_lng>=" . $arr['min_lng'] . " limit 0,10");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$len = cls_map::get_distance($lng , $lat, $obj_rs['shop_position_lng'] , $obj_rs['shop_position_lat'] , 2);
				$obj_rs['len'] = number_format($len/1000,2);
				$len = intval($len*100);
				$arr_shop[$len][] = $obj_rs;
			}
			ksort($arr_shop);
			foreach($arr_shop as $key => $item) {
				foreach($item as $obj_rs) {
					if(empty($obj_rs['shop_pic_small'])) $obj_rs['shop_pic_small'] = $obj_rs['shop_pic'];
					if(empty($arr_message['news'])) {
						if(empty($obj_rs['shop_pic'])) $obj_rs['shop_pic'] = $obj_rs['shop_pic_small'];
						$pic = fun_get::html_url($obj_rs['shop_pic'] , 1);
					} else {
						$pic = fun_get::html_url($obj_rs['shop_pic_small'] , 1);
					}
					$url = cls_config::get('url') . '/index.php?app_weixin=1&app_act=shop&id=' . $obj_rs['shop_id'];
					$shop_id = $obj_rs['shop_id'];
					$arr_message['news'][] = array(
						'title' => $obj_rs['shop_name'] . "(" . $obj_rs['len'] . "km)",
						"desc" => strip_tags(fun_get::filter($item['shop_desc'],true)),
						"pic" => $pic,
						"url" => $url,
					);
				}
			}
			if(empty($arr_message['news'])) $arr_message = array();
			return $arr_message;
	}
	//关注事件
	static function on_guanzhu($openid) {
		$userinfo = self::get_userinfo($openid);
		if(empty($userinfo)) return;
		$site_id = self::get_perms('site_id');
		$obj_rs = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE."weixin_user set user_state=1 where user_openid='" . $openid . "' and user_site_id='" . $site_id . "'");
		if($obj_rs['code'] == 0 && cls_obj::db_w()->affected_rows()) return array('code' => 0);
		$sex = '未知';
		if($userinfo['sex'] == 1) $sex = '男';
		if($userinfo['sex'] == 2) $sex = '女';
		$arr_user = array(
			'user_openid' => $openid,
			'user_pic' => $userinfo['headimgurl'],
			'user_addtime' => $userinfo['subscribe_time'],
			'user_area' => $userinfo['country'] . " " . $userinfo['province'] . " " . $userinfo['city'],
			'user_sex' => $sex,
			'user_name' => $userinfo['nickname'],
			'user_state' => 1,
			'user_site_id' => $site_id ,
		);
		$arr = cls_obj::db_w()->on_insert(cls_config::DB_PRE."weixin_user",$arr_user);
		$arrx = tab_weixin_redpack::on_open(0,'subscribe',$openid);
		return $arr;
	}
	//取消关注
	static function on_guanzhu_cancel($openid) {
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "weixin_user set user_state=0,user_canceltime='" . TIME . "'  where user_openid='" . $openid . "' and user_site_id='" . $site_id . "'");
		return $arr;
	}
	//给指定用户发送消息
	static function on_send($site_id , $toopenid , $type , $arr_cont) {
		$site_id = self::get_perms("site_id" , $site_id);
		$arr_message = array(
			'touser' => $toopenid ,
			'msgtype' => $type
		);
		switch($type) {
			case 'text':
				if(empty($arr_cont['message_text']) || !isset($arr_cont['message_text']) ) return array("code" => 500 , "msg" => "发送内容不能为空");
				$arr_message['text']['content'] = strip_tags($arr_cont['message_text']);
				break;
			case 'image':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的图片");
				$arr_message['image']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'voice':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的音频文件");
				$arr_message['voice']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'video':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的视频文件");
				$arr_message['video']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'news':
				if(empty($arr_cont['news']) || !isset($arr_cont['news']) ) return array("code" => 500 , "msg" => "发送内容不能为空");
				$arr_return = array();
				foreach($arr_cont['news'] as $item) {
					if(!isset($item['title'])) continue;
					$arr_return['articles'][] = array(
						"title" => strip_tags($item['title']),
						"description" => strip_tags($item['desc']),
						"picurl" => $item['pic'],
						"url" => $item['url'],
					);
				}
				$arr_message['news'] = $arr_return;
				break;
			default:
				return array("code" => 500 , "msg" => "类型不存在");
		}
		$cont = fun_format::json($arr_message);
		$access_token = self::get_access_token();
		if(empty($access_token)) return array('code' => 500 , 'msg' => '微信权限不够');

		$arr_return = fun_base::post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token , $cont);
		if($arr_return['code'] == 0) {
			if(!empty($arr_return['cont'])) {
				$arr = fun_format::toarray($arr_return['cont']);
				if($arr['errcode'] != '0') {
					$arr_return['code'] = 500 ; 
					$arr_return['msg'] = $arr['errmsg'];
				}
			} else {
				$arr_return['code'] = 500 ; 
				$arr_return['msg'] = '访问微信接口失败';
			}
		}
		return $arr_return;
	}
	//给指定用户发送消息
	static function on_sendmsg_openid($site_id , $toopenid = array() , $type , $arr_cont) {
		$site_id = self::get_perms("site_id" , $site_id);
		$arr_message = array(
			'touser' => $toopenid ,
			'msgtype' => $type
		);
		switch($type) {
			case 'text':
				if(empty($arr_cont['message_text']) || !isset($arr_cont['message_text']) ) return array("code" => 500 , "msg" => "发送内容不能为空");
				$arr_message['text']['content'] = strip_tags($arr_cont['message_text']);
				break;
			case 'image':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的图片");
				$arr_message['image']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'voice':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的音频文件");
				$arr_message['voice']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'video':
				if(empty($arr_cont['message_media_id']) || !isset($arr_cont['message_media_id']) ) return array("code" => 500 , "msg" => "请选择发送的视频文件");
				$arr_message['video']['media_id'] = $arr_cont['message_media_id'];
				break;
			case 'news':
				if(empty($arr_cont['news']) || !isset($arr_cont['news']) ) return array("code" => 500 , "msg" => "发送内容不能为空");
				$arr_return = array();
				foreach($arr_cont['news'] as $item) {
					if(!isset($item['title'])) continue;
					$arr_return['articles'][] = array(
						"title" => strip_tags($item['title']),
						"description" => strip_tags($item['desc']),
						"picurl" => $item['pic'],
						"url" => $item['url'],
					);
				}
				$arr_message['news'] = $arr_return;
				break;
			default:
				return array("code" => 500 , "msg" => "类型不存在");
		}
		$cont = fun_format::json($arr_message);
		$access_token = self::get_access_token();
		if(empty($access_token)) return array('code' => 500 , 'msg' => '微信权限不够');

		$arr_return = fun_base::post("https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $access_token , $cont);
		if($arr_return['code'] == 0) {
			if(!empty($arr_return['cont'])) {
				$arr = fun_format::toarray($arr_return['cont']);
				if($arr['errcode'] != '0') {
					$arr_return['code'] = 500 ; 
					$arr_return['msg'] = $arr['errmsg'];
				}
			} else {
				$arr_return['code'] = 500 ; 
				$arr_return['msg'] = '访问微信接口失败';
			}
		}
		return $arr_return;
	}
	static function format_echo($arr_cont = array()) {
		if(empty($arr_cont)) return array();
		$arr_return = array();
		if(empty($arr_cont['message_type'])) $arr_cont['message_type'] = 'text';
		switch($arr_cont['message_type']) {
			case 'text':
				//$arr_return['cont'] = strip_tags(str_replace('</div>','\n',$arr_cont['message_text']));
				$arr_return['cont'] = strip_tags(str_replace('</div>',chr(10),$arr_cont['message_text']));
				break;
			case 'image':
				$arr_return['oid'] = $arr_cont['message_media_id'];
				break;
			case 'voice':
				$arr_return['oid'] = $arr_cont['message_media_id'];
				break;
			case 'video':
				$arr_return['oid'] = $arr_cont['message_media_id'];
				$arr_return['title'] = $arr_cont['media_name'];
				$arr_return['description'] = $arr_cont['media_desc'];
				break;
			case 'music':
				$arr_return['title'] = strip_tags($arr_cont['title']);
				$arr_return['desc'] = strip_tags($arr_cont['desc']);
				$arr_return['url'] = self::get_url($arr_cont['url']);
				$arr_return['pic_id'] = $arr_cont['pic_id'];
				break;
			case 'news':
				foreach($arr_cont['news'] as $item) {
					if(!isset($item['title'])) continue;
					$arr_return[] = array(
						"title" => strip_tags($item['title']),
						"desc" => strip_tags($item['desc']),
						"picurl" => $item['pic'],
						"url" => self::get_url($item['url']),
					);
				}
				break;
		}
		return $arr_return;
	}
	//回复消息
	static function get_echo($from_user,$arr_cont,$type = 'text') {
		if(empty($from_user) ) return '';
		$uname = self::get_perms('uname');
		$str = '<xml>';
		$str .= '<ToUserName><![CDATA[' . $from_user . ']]></ToUserName>';
		$str .= '<FromUserName><![CDATA[' . $uname . ']]></FromUserName>';
		$str .= '<CreateTime>' . TIME . '</CreateTime>';
		$str .= '<MsgType><![CDATA[' . $type . ']]></MsgType>';
		switch($type) {
			case 'text':
				$str .= '<Content><![CDATA[' . $arr_cont['cont'] . ']]></Content>';
				break;
			case 'image':
				$str .= '<Image>';
				$str .= '<MediaId><![CDATA[' . $arr_cont['oid'] . ']]></MediaId>';
				$str .= '</Image>';
				break;
			case 'voice':
				$str .= '<Voice>';
				$str .= '<MediaId><![CDATA[' . $arr_cont['oid'] . ']]></MediaId>';
				$str .= '</Voice>';
				break;
			case 'video':
				$str .= '<Video>';
				$str .= '<MediaId><![CDATA[' . $arr_cont['oid'] . ']]></MediaId>';
				$str .= '<Title><![CDATA[' . $arr_cont['title'] . ']]></Title>';
				$str .= '<Description><![CDATA[' . $arr_cont['description'] . ']]></Description>';
				$str .= '</Video>';
				break;
			case 'music':
				$str .= '<Music>';
				$str .= '<Title><![CDATA[' . $arr_cont['title'] . ']]></Title>';
				$str .= '<Description><![CDATA[' . $arr_cont['desc'] . ']]></Description>';
				$str .= '<MusicUrl><![CDATA[' . fun_get::html_url($arr_cont['url'],1) . ']]></MusicUrl>';
				$str .= '<HQMusicUrl><![CDATA[' . fun_get::html_url($arr_cont['url'],1) . ']]></HQMusicUrl>';
				$str .= '<ThumbMediaId><![CDATA[' . $arr_cont['pic_id'] . ']]></ThumbMediaId>';
				$str .= '</Music>';
				break;
			case 'news':
				$str .= '<ArticleCount>' . count($arr_cont) . '</ArticleCount>';
				$str .= '<Articles>';
				foreach($arr_cont as $item) {
					$str .= '<item>';
					$str .= '<Title><![CDATA[' . $item['title'] . ']]></Title>';
					$str .= '<Description><![CDATA[' . $item['desc'] . ']]></Description>';
					$str .= '<PicUrl><![CDATA[' . fun_get::html_url($item['picurl'],1) . ']]></PicUrl>';
					$str .= '<Url><![CDATA[' . fun_get::html_url($item['url'],1) . ']]></Url>';
					$str .= '</item>';
				}
				$str .= '</Articles>';
				break;
		}
		$str .= '</xml>';
		return $str;
	}

	static function menu_create($site_id) {
		$obj_db = cls_obj::db();
		$arr_menu = array();
		$site_id = self::get_perms('site_id' , $site_id);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "weixin_menu where menu_site_id='" . $site_id . "' order by menu_pid,menu_sort,menu_id");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$type = empty($obj_rs['menu_message_id']) ? 'view' : 'click';
			$arr = array("type"=>$type , "name"=>$obj_rs['menu_name']);
			if(empty($obj_rs['menu_message_id'])) {
				$arr['url'] = fun_get::filter($obj_rs['menu_linkurl'],true);
			} else {
				$arr['key'] = "V1001_MENU_" . $obj_rs['menu_message_id'];
			}
			if(empty($obj_rs['menu_pid'])) {
				$arr_menu['id_' . $obj_rs['menu_id']] = $arr;
			} else {
				if(isset($arr_menu['id_' . $obj_rs['menu_pid']])) {
					unset($arr_menu['id_' . $obj_rs['menu_pid']]['type']);
					unset($arr_menu['id_' . $obj_rs['menu_pid']]['key']);
					unset($arr_menu['id_' . $obj_rs['menu_pid']]['url']);
					if(!isset($arr_menu['id_' . $obj_rs['menu_pid']]['sub_button'])) $arr_menu['id_' . $obj_rs['menu_pid']]['sub_button'] = array();
					$arr_menu['id_' . $obj_rs['menu_pid']]['sub_button'][] = $arr;

				}
			}
		}
		//没有菜单，则删除
		$token = self::get_access_token();
		if(empty($arr_menu)) {
			$url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $token;
			$cont = file_get_contents($url);
			if(!empty($cont)) {
				$arr = fun_format::toarray($cont);
				if(isset($arr['errcode'])) {
					if( $arr['errcode'] != '0') {
						return array('code' => 500 , 'msg' => $arr['errmsg']);
					} else {
						return array('code' => 0);
					}
				} else {
					return array('code' => 500 , 'msg' => '删除微信菜单失败');
				}
			} else {
				return array('code' => 500 , 'msg' => '删除微信菜单失败');
			}
		}
		$arr_weixin = array();
		foreach($arr_menu as $item=>$key) {
			$arr_weixin['button'][] = $key;
		}
		$cont = fun_format::json($arr_weixin);
		if(empty($token)) return array('code' => 500 , 'msg' => '微信权限不够');

		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $token;
		$arr_msg = fun_base::post($url , $cont);
		if($arr_msg['code'] == 0) {
			if(!empty($arr_msg['cont'])) {
				$arr = fun_format::toarray($arr_msg['cont']);
				if(isset($arr['errcode']) && $arr['errcode'] != '0') {
					$arr_msg['code'] = 500;
					$arr_msg['msg'] = $arr['errmsg'];
				}
			}
		}
		return $arr_msg;
	}

	static function get_user($page = 1 , $pagesize = 20) {
		$arr_return = array();
		$access_token = self::get_access_token();
		if(empty($access_token)) return array();
		$next_openid = '';
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=' . $next_openid;
		$arr = fun_base::post($url , array() , array() , 'GET');
		if($arr['code'] != 0 || empty($arr['cont']) ) return $arr_return;
		$cont = $arr['cont'];
		$arr_list = fun_format::toarray($cont);
		return $arr_list;
	}

	static function get_userinfo($openid) {
		$arr_return = array();
		$access_token = self::get_access_token();
		if(empty($access_token)) return $arr_return;
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid ."&lang=zh_CN";
		$arr = fun_base::post($url , array() , array() , 'GET');
		if($arr['code'] != 0 || empty($arr['cont']) ) return $arr_return;
		$cont = $arr['cont'];
		$arr_list = fun_format::toarray($cont);
		if(isset($arr_list['nickname'])) $arr_list['nickname'] = fun_format::emoji($arr_list['nickname']);
		return $arr_list;
	}
	static function get_url($url) {
		$wx_id = self::$config['wx_id'];
		if(empty($wx_id)) return $url;
		$basename = basename($url);
		$arr = explode("?" , $basename);
		if(count($arr)>1) {
			$url = $url . "&app_wx_id=" . $wx_id;
		} else {
			$url = $url . "?app_wx_id=" . $wx_id;
		}
		return $url;
	}
	static function get_userlist($next_openid = '') {
		$arr_return = array();
		$access_token = self::get_access_token();
		$arr_list = array('total' => 0,'count' =>0 , 'data' => array() , 'next_openid' => '');
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $access_token . "&next_openid=" . $next_openid;
		$arr = fun_base::post($url , array() , array() , 'GET');
		if($arr['code'] != 0 || empty($arr['cont']) ) return $arr_return;
		$cont = $arr['cont'];
		if(stristr($cont , 'total') && stristr($cont , 'openid')) {
			$arr_list = fun_format::toarray($cont);
			if($arr_list['count']!=10000) $arr_list['next_openid'] = '';
		}
		return $arr_list;
	}
	static function sign_pay($arr) {
		ksort($arr);
		$arr_x = array();
		foreach($arr as $item=>$key) {
			if(empty($key)) continue;
			$arr_x[] = $item . "=" . $key;
		}
		$appsecret = self::get_perms('mch_key');
		$str = implode("&" , $arr_x) . "&key=" . $appsecret;
		$str = strtoupper(md5($str));
		return $str;
	}
	static function get_jsapi_ticket($site_id = 0) {
		$jsapi_ticket = cls_cache::get('jsapi_ticket' , 'weixin/' . $site_id , 100);
		if(empty($jsapi_ticket)) {
			$access_token = self::get_access_token();
			if(empty($access_token)) return array('code' => 500 , 'msg' => '微信权限不够');
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
			$arr = fun_base::post($url , array() , array() , 'GET');
			if($arr['code'] != 0 || empty($arr['cont']) ) return array('code' => 500 , 'msg' => '微信权限不够');
			$cont = $arr['cont'];
			$arr_json = fun_format::toarray($cont);
			if(empty($arr_json) || !isset($arr_json['ticket'])) return array("code" => 500 , "msg" => "jsapi_ticket出错");
			cls_cache::set('jsapi_ticket' , 'weixin/' . $site_id , $arr_json['ticket']);
			$jsapi_ticket = $arr_json['ticket'];
		}
		return array("code" => 0 , "ticket" => $jsapi_ticket);
	}
	static function sign_js($url) {
		$arr_ticket = self::get_jsapi_ticket();
		if($arr_ticket['code'] != 0) return $arr_ticket;
		$arr = array(
			'jsapi_ticket' => $arr_ticket['ticket'],
			'timestamp' => TIME,
			'noncestr' => TIME . rand(10000,99999),
			'url' => $url,
		);
		ksort($arr);
		$arr_x = array();
		foreach($arr as $item=>$key) {
			if(empty($key)) continue;
			$arr_x[] = $item . "=" . $key;
		}
		$str = implode("&" , $arr_x);
		$arr['sign'] = sha1($str);
		$arr["appid"] = cls_weixin::get_perms("appid");
		$arr['code'] = 0;
		return $arr;
	}
}