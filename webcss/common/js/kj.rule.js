/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
kj.rule = {
	types : {},
	chk : function (val,rule,obj) {// rule:支持 && , || 两种条件
		if(rule == "checked" && obj && 'length' in obj ) {
			for(var i = 0 ; i < obj.length ; i++ ) {
				if (obj[i].checked) return false;
			}
			return true;
		} else {
			var fun;
			var arr_or = rule.split("||");
			var is_and = true;
			var is_or = false;
			var arr_and;
			for(var j = 0; j < arr_or.length; j++) {
				arr_and = arr_or[j].split("&&");
				for(var i = 0; i < arr_and.length; i++) {
					if(this._chk(val,arr_and[i]) == false) {
						is_and = false;
						break;
					}
				}
				if(is_and == true) return true;
			}
			return false;
		}
	},
	_chk : function (val,rule) {
		var left = rule.substring(0,1);
		switch(left) {
			case "!"://非
				rule = rule.substring(1);
				if(!(rule in this.types)) return false;
				fun = this.types[rule];
				return !fun(val);
			case "/"://正则
				eval('var rule1 = ' + rule);
				if( rule1.test(val) ) return false;
				return true;
			default://现有函数
				if(!(rule in this.types)) {
					alert("调用规则不存在");
					return false;
				}
				fun = this.types[rule];
				return fun(val);
		}
	},
	form : function(obj , msgmode) {//msgmode : 消息模式, 0 : 默认，1 : alert提示
		var arr_obj = kj.rule.form_obj(obj);
		//验证
		var i,ruletips;
		for(i = 0 ; i < arr_obj.length ; i++ ) {
			ruletips = kj.getAttribute(arr_obj[i] , 'ruletips');
			if(kj.getAttribute(arr_obj[i] , 'required') != null && kj.rule.types.empty(arr_obj[i].value)) {
				var x = kj.getAttribute(arr_obj[i] , 'placeholder');
				if(x) ruletips = x + '不能为空';
				kj.rule.showtips(arr_obj[i] , ruletips , msgmode);
				return false;
			}
			rule = kj.getAttribute(arr_obj[i] , 'rule');
			if( arr_obj[i].value!='' && rule != null && kj.rule.chk(arr_obj[i].value , rule )==false) {
				if(rule == 'pwd') ruletips = kj.cfg('rule_pwd_tips');
				if(rule == 'uname') ruletips = kj.cfg('rule_uname_tips');
				if(ruletips == '') ruletips = kj.getAttribute(arr_obj[i] , 'placeholder') + '格式不对';
				kj.rule.showtips(arr_obj[i] , ruletips , msgmode);
				return false;
			}
		}
		return true;
	},
	form_obj : function(obj) {
		if('elements' in obj) {
			return obj.elements;
		} else {
			var arr_obj1 = kj.obj("input" , obj);
			var arr_obj2 = kj.obj("textarea" , obj);
			var arr_obj3 = kj.obj("select" , obj);
			var arr_obj = [];
			var i;
			for(i = 0 ; i <arr_obj1.length ; i++ ) {
				arr_obj[arr_obj.length] = arr_obj1[i];
			}
			for(i = 0 ; i < arr_obj2.length ; i++) {
				arr_obj[arr_obj.length] = arr_obj2[i];
			}
			for(i = 0 ; i < arr_obj3.length ; i++) {
				arr_obj[arr_obj.length] = arr_obj3[i];
			}
			return arr_obj;
		}
	},
	showtips : function(obj , ruletips , msgmode) {
		if(!msgmode) msgmode = kj.getAttribute(obj , 'ruletipsmode');
		if(msgmode!=null && msgmode == '1') {
			alert(ruletips);
			obj.focus();
			return;
		}
		var id = ('id' in obj && obj.id!='') ? obj.id : obj.name;
		id = 'ruletips_' + id;
		var img = kj.cfg('basecss') + "/common/images/tips.gif";
		var imgicon = kj.cfg('basecss') + "/common/images/tipsicon.gif";
		var obj_div;
		if(kj.obj('#' + id)) {
			obj_div = kj.obj('#'+id);
			kj.show('#' + id);
		} else {
			obj_div=document.createElement("div");
			obj_div.id = id;
			obj_div.style.cssText = 'position:absolute;color:#ff0000;font-size:12px;z-index:99';
			//obj_div.style.height = h + "px";
			document.body.appendChild(obj_div);

		}
		obj_div.innerHTML = '<div style="float:left;border:1px #ccc solid;background:#fff;padding:5px 8px;;box-shadow:0 0 10px rgba(0, 0, 0, 0.2)"><img src="' + img + '" style="position:relative;top:-18px"><span style="display:block;margin-top:-12px;font-size:14px"><img src="'+imgicon+'">&nbsp;' + ruletips + '</span></div>';
		var offset = kj.offset(obj);
		var w = kj.w(obj);
		var h = kj.h(obj) + 14;
		var top = kj.toint(kj.getAttribute(obj , 'ruletipstop'));
		var left = kj.toint(kj.getAttribute(obj , 'ruletipsleft'));

		obj_div.style.top = (offset.top+h+top) + 'px';
		obj_div.style.left = (offset.left+left) + "px";
		var act = (obj.tagName.toLowerCase() == 'select') ? "click" : "blur";
		kj.handler(obj,act,function(e){
			var id = ('id' in obj && obj.id!='') ? obj.id : obj.name;
			id = 'ruletips_' + id;
			kj.hide('#'+id);
		});
		obj.focus();
	}
}
//是否为空
kj.rule.types.empty = function(val) {
	if(!val || val == '' || val == 'undefined' || val == null) return true;
	return false;
}
//用户名格式，来自配置文件
kj.rule.types.uname = function(val) {
	var str_reg = kj.cfg('rule_uname');
	if(str_reg && str_reg != '') {
		if(str_reg == 'mobile') {
			if(kj.rule.types.mobile(val)) return true;
		} else if(str_reg == 'email') {
			if(kj.rule.types.email(val)) return true;
		} else {
			eval('var reg = ' + str_reg);
			if(reg.test(val)) return true;
		}
		return false;
	}
	return true;
}
//密码格式，来自配置文件
kj.rule.types.pwd = function(val) {
	var str_reg = kj.cfg('rule_pwd');
	if(str_reg && str_reg != '') {
		eval('var reg = ' + str_reg);
		if(reg.test(val)) return true;
		return false;
	}
	return true;
}
//邮箱格式
kj.rule.types.email = function(val) {
	var reg = /^[a-z0-9_-]+\@[a-z0-9\-\.]{1,30}\.[a-z]{2,4}$/i;
	if(reg.test(val)) return true;
	return false;
}
//年龄验证
kj.rule.types.age = function(val) {
	var reg = /^[0-9]+$/i;
	var age = kj.toint(val);
	if( reg.test(val) && age>0 && age<150) return true;
	return false;
}
//日期验证
kj.rule.types.date = function(val) {
	var reg = /((^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(10|12|0?[13578])([-\/\._])(3[01]|[12][0-9]|0?[1-9])$)|(^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(11|0?[469])([-\/\._])(30|[12][0-9]|0?[1-9])$)|(^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(0?2)([-\/\._])(2[0-8]|1[0-9]|0?[1-9])$)|(^([2468][048]00)([-\/\._])(0?2)([-\/\._])(29)$)|(^([3579][26]00)([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][0][48])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][0][48])([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][2468][048])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][2468][048])([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][13579][26])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][13579][26])([-\/\._])(0?2)([-\/\._])(29)$))/ig;
	if(reg.test(val)) return true;
	return false;
}
//日期时间
kj.rule.types.datetime = function(val) {
	var reg = /^(2?[0-3]|[0-1][0-9])[\:][0-5][0-9]([\:][0-5][0-9]){0,1}$/ig;
	var arr = val.split(" ");
	var time = '';
	if(arr.length>1) time = arr[1];
	if(kj.rule.types.date(arr[0]) && (time=='' || reg.test(time))) return true;
	return false;
}
//电话验证
kj.rule.types.tel = function(val) {
	var reg = /^([0-9]{3,4}[-|\s]{0,1}){0,1}[0-9]{7,8}$/i;
	if( reg.test(val) || kj.rule.types.mobile(val) ) return true;
	return false;
}
//手机
kj.rule.types.mobile = function(val) {
	var reg = /^[1][0-9]{10}$/i;
	if( reg.test(val) ) return true;
	return false;
}