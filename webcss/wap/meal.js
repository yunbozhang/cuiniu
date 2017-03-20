var jsmeal = new function() {
	this.is_verify = false;
	this.obj = null;
	this.html = [];
	this.overids = [];
	this.collect = function(shop_id , o) {
		if(kj.cfg('uid')<1) {
			alert("请登录后，再来操作");
			return;
		}
		var obj1 = o;//kj.obj("font",o);
		if(o && obj1.className.indexOf('collect2')>=0) {
			this.collect_cancel(shop_id);
			return;
		}
		kj.ajax.get(kj.cfg('baseurl') +"/" + kj.basename() + "?app=ajax&app_act=collect&shop_id=" + shop_id , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code=='0') {
				//kj.set(".id_collect_" + obj.shop_id + ' font' , "innerHTML" , "已收藏");
				kj.delClassName(".id_collect_" + obj.shop_id , "collect1");
				kj.addClassName(".id_collect_" + obj.shop_id , "collect2");
			} else {
				alert(obj.msg);
			}
		});
	}
	this.collect_cancel = function(shop_id , o) {
		if(kj.cfg('uid')<1) {
			alert("请登录后，再来操作");
			return;
		}
		var obj1 = o;//kj.obj("font",o);
		if(o && obj1.className.indexOf('collect1')>=0) {
			this.collect(shop_id);
			return;
		}
		kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app=ajax&app_act=collect.cancel&shop_id=" + shop_id , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code=='0') {
				//kj.set(".id_collect_" + obj.shop_id + ' font' , "innerHTML" , "收藏");
				kj.delClassName(".id_collect_" + obj.shop_id , "collect2");
				kj.addClassName(".id_collect_" + obj.shop_id , "collect1");
			} else {
				alert(obj.msg);
			}
		});
	}
	this.chk_radio = function( o ) {
		var objp = kj.parent(o , ".Tradio");
		kj.delClassName(kj.obj("li" , objp) , "xsel");
		kj.addClassName(o,"xsel");
		kj.set(kj.obj("input" , o) , "checked" , true);
	}
	this.cartnum = function() {
		var str = kj.cookie_get("cart_ids");
		var arr = [],num = 0;arr_2 = [] ,arr_3 = [];
		if(str) arr = str.split("||");
		for(i = 0 ; i < arr.length ; i++) {
			arr_2 = arr[i].split(":");
			if(arr_2.length>1) {
				arr_3 = arr_2[1].split("|");
				num += arr_3.length;
			}
		}
		kj.set("#id_toll_cartnum","innerHTML" , num);
		if(num<1) {
			kj.hide("#id_toll_cartnum");
		} else {
			kj.show("#id_toll_cartnum");
		}

	}
}

kj.handler(document.documentElement,"touchend",function(e){
	//隐藏购物车
	var target = kj.event_target(e);
	if( !('clicker' in kj.data) || !kj.data['clicker'] ) return;
	if(target.className.indexOf('Tmousedown')>=0) return;
	var obj = kj.parent(target , ".dropdown");
	if(obj) {
		if('length' in kj.data['clicker']) {
			var obj2;
			for(var i  = 0  ; i < kj.data['clicker'].length ; i++) {
				obj2 = kj.parent(kj.data['clicker'][i] , ".dropdown");
				if(obj2 == obj) return;
			}
		} else if(kj.parent(kj.data['clicker'], ".dropdown") == obj || kj.data['clicker'] == obj) {
			return;
		}
	}
	kj.hide(kj.data['clicker']);
	kj.data['clicker'] = null;
});
kj.handler(document.documentElement,"mousedown",function(e){
	//隐藏购物车
	var target = kj.event_target(e);
	if( !('clicker' in kj.data) || !kj.data['clicker'] ) return;
	if(target.className.indexOf('Tmousedown')>=0) return;
	var obj = kj.parent(target , ".dropdown");
	if(obj) {
		if('length' in kj.data['clicker']) {
			var obj2;
			for(var i  = 0  ; i < kj.data['clicker'].length ; i++) {
				obj2 = kj.parent(kj.data['clicker'][i] , ".dropdown");
				if(obj2 == obj || kj.data['clicker'][i] == obj) return;
			}
		} else if(kj.parent(kj.data['clicker'], ".dropdown") == obj || kj.data['clicker'] == obj) {
			return;
		}
	}
	kj.hide(kj.data['clicker']);
	kj.data['clicker'] = null;
});
kj.handler(".Actcheck","click",function(){
	var objx = kj.obj(".xon" , this);
	var objf = kj.obj("font" , this);
	var objfont = '';
	var objval = kj.obj("input" , this);
	if(objf && 'length' in objf && objf.length>0) objfont = objf[0];
	var objmsg = objfont ? kj.json(kj.getAttribute(objfont,'kjdata')) : {'on':'','off':''};
	if(objx && objx.length>0) {
		kj.delClassName(kj.obj("span",this),"xon");
		kj.set(objf , "innerHTML" , objmsg.off);
		kj.set(objval , "value" , 0);
	} else {
		kj.addClassName(kj.obj("span",this),"xon");
		kj.set(objf , "innerHTML" , objmsg.on);
		kj.set(objval , "value" , 1);
	}
	var fun = kj.getAttribute(this,'kjfun');
	if(fun && fun != '') {
		eval(fun);
	}
});
kj.onload(function(){
	jsmeal.cartnum();
});
