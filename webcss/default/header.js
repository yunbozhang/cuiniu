var jsheader = new function() {
	this.is_verify = false;
	this.obj = null;
	this.html = [];
	this.over = function(o,id) {
		var offset = kj.offset(o);
		kj.set('#id_menu'+id , 'style.left' , (offset.left-90)+'px');
		kj.show('#id_menu'+id);
		this.obj = o;
	}
	this.out = function(o,id) {
		kj.hide('#id_menu'+id);
	}
	this.sel = function(o) {
		kj.show(o);
		if(this.obj) kj.addClassName(this.obj,'xsel');
	}
	this.unsel = function(o) {
		kj.hide(o);
		if(this.obj) kj.delClassName(this.obj,'xsel');
	}
	this.showlogin = function() {
		kj.dialog.close("#winshowreg");
		var obj = kj.obj('#id_loginbox');
		if(obj) {
			this.login_html = obj.innerHTML;
			kj.remove(obj);
		}
		kj.dialog({'html':this.login_html,'id':'showlogin','type':'html','title':'会员登录','w':600,'showbtnmax':false,'showbtnhide':false});
	}
	this.show_verify = function() {
		var objpic = kj.obj("#id_verify_pic");
		if(objpic.src.indexOf("verifycode")<0) {
			objpic.src = kj.cfg('baseurl') + '/common.php?app=sys&app_act=verifycode';
			kj.handler(document.documentElement,"click",function(e){
				var arr = new Array('img' , 'input');
				var target = kj.event_target(e);
				if(target.name!='verifycode' && target.id!='id_verify_pic') {
					kj.hide('#id_verify_pic');
				}
			});
		}
		kj.show('#id_verify_pic');
	}
	this.on_login = function() {
		kj.ajax.post(document.frmlogin , function(data) {
			var obj_data=kj.json(data);
			if(obj_data.isnull) {
				alert("系统繁忙，请稍后再来试试");
			} else {
				if(obj_data.code == 0) {
					if('jump_fromurl' in document.frmlogin && document.frmlogin.jump_fromurl.value!='') {
						window.open(document.frmlogin.jump_fromurl.value , "_self");
					} else {
						location.reload(true);
					}
				} else {
					if('msg' in obj_data && obj_data.msg) {
						alert(obj_data.msg);
					} else {
						alert("系统繁忙，请稍后再来试试");
					}
					if(jsheader.is_verify) {
						jsheader.verify_refresh();
						document.frmlogin.verifycode.value='';
					}
					if('show_code' in obj_data && obj_data.show_code == '1') {
						jsheader.is_verify = true;
						kj.show("#id_verify_code");
					}
					if(obj_data.code == '4') document.frmlogin.uname.focus();
					if(obj_data.code == '3') document.frmlogin.pwd.focus();
					if(obj_data.code == '11') document.frmlogin.verifycode.focus();
				}
			}
		});
		return false;
	}
	this.verify_refresh = function() {
		kj.obj("#id_verify_pic").src = kj.cfg('baseurl') + '/common.php?app=sys&app_act=verifycode&app_contenttype=img&app_rnd='+Math.random();
	}
	this.showreg = function() {
		kj.dialog.close("#winshowlogin");
		if("reg" in this.html) {
			kj.dialog({'html':this.html['reg'],'id':'showreg','type':'html','title':'注册会员','w':600,'showbtnmax':false,'showbtnhide':false});
		} else {
			kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=reg" , function(data) {
				jsheader.html['reg'] = data;
				kj.dialog({'html':data,'id':'showreg','type':'html','title':'注册会员','w':600,'showbtnmax':false,'showbtnhide':false});
				kj.loadjs(jsheader.tempurl + "reg.js",function() {
					if(typeof(jsreg) == 'undefined') return;
					jsreg.reg_verify = jsheader.reg_verify;
					jsreg.rule_uname = jsheader.rule_uname;
				});
			});
		}
	}

	this.collect = function(shop_id , o) {
		if(kj.cfg('uid')<1) {
			jsheader.showlogin();
			return;
		}
		if(o && o.className.indexOf('collect2')>=0) {
			this.collect_cancel(shop_id);
			return;
		}
		kj.ajax.get(kj.cfg('baseurl') +"/" + kj.basename() + "?app=ajax&app_act=collect&shop_id=" + shop_id , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code=='0') {
				kj.set(".id_collect_" + obj.shop_id , "title" , "点击取消收藏");
				kj.set(".Tcollect" + obj.shop_id , "innerHTML" , "取消收藏");
				kj.delClassName(".id_collect_" + obj.shop_id , "collect1");
				kj.addClassName(".id_collect_" + obj.shop_id , "collect2");
				var arr = kj.obj(".TcollectN" + obj.shop_id);
				if(arr.length>0) {
					var i = kj.toint(arr[0].innerHTML)+1;
					kj.set(".TcollectN" + obj.shop_id,"innerHTML" , i);
				}
			} else {
				alert(obj.msg);
			}
		});
	}
	this.collect_cancel = function(shop_id , o) {
		if(kj.cfg('uid')<1) {
			jsheader.showlogin();
			return;
		}
		if(o && o.className.indexOf('collect1')>=0) {
			this.collect(shop_id);
			return;
		}
		kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app=ajax&app_act=collect.cancel&shop_id=" + shop_id , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code=='0') {
				kj.set(".id_collect_" + obj.shop_id , "title" , "点击收藏");
				kj.set(".Tcollect" + obj.shop_id , "innerHTML" , "收藏");
				kj.delClassName(".id_collect_" + obj.shop_id , "collect2");
				kj.addClassName(".id_collect_" + obj.shop_id , "collect1");
				kj.remove("#id_collect_"+obj.shop_id);
				var arr = kj.obj(".TcollectN" + obj.shop_id);
				if(arr.length>0) {
					var i = kj.toint(arr[0].innerHTML)-1;
					if(i<0) i=0;
					kj.set(".TcollectN" + obj.shop_id,"innerHTML" , i);
				}
			} else {
				alert(obj.msg);
			}
		});
	}

	this.close = function() {
		var obj = kj.dialog.objid;
		for(var i = 0 ; i < obj.length ; i++) {
			kj.dialog.close("#"+obj[i]);
			i--;
		}
	}
	this.comment_shop = function(shop_id) {
		window.open("?app_act=comment.shop&shop_id="+shop_id,"_self");
		return;
		kj.dialog({id:'comment',title:'商家评论',url:kj.cfg('baseurl') + '/' + kj.basename() + '?app_act=comment.shop&shop_id='+shop_id,w:800,h:700,showbtnhide:false,showbtnmax:false,type:'iframe'});
	}

}