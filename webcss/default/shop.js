var shop = new function() {
	this.rule = Array();
	this.ajax_fun = "";
	this.default_app_act = "";
	this.frm_ajax = function(msg,func) {
		if( msg == "del" || (msg != 'delete' && msg.indexOf("del")>=0 ) ){
			if(!confirm("确定要删除吗？")) {
				return;
			}
		}
		if( msg=="delete" || msg.indexOf("delete") >= 0 ){
			if(!confirm("删除将不可恢复，确定要继续吗？")) {
				return;
			}
		}
		if(msg == '') {
			alert("没有指定操作");
			return;
		}
		if(kj.rule.form(document.frm_main) == false) {
			return false;
		}
		if(!func) func="";
		this.ajax_fun=func;
		this.default_app_act = document.frm_main.app_act.value;
		document.frm_main.app_act.value=msg;
		document.frm_main.app_ajax.value=1;
		kj.ajax.post(document.frm_main,function(data){
			var obj_data=kj.json(data);
			document.frm_main.app_act.value = shop.default_app_act;
			document.frm_main.app_ajax.value = '';
			if(obj_data.isnull) {
				kj.alert("操作失败" , shop.ajax_fun , obj_data);
			} else {
				if('code' in obj_data && obj_data.code=='0') {
					if("msg" in obj_data && obj_data.msg!=''){
						kj.alert.show(obj_data.msg , shop.ajax_back , obj_data);
					} else {
						kj.alert.show("操作成功" , shop.ajax_back , obj_data);
					}
				} else {
					if("msg" in obj_data && obj_data.msg!='') {
						kj.alert(obj_data.msg , shop.ajax_fun , obj_data);
					} else {
						kj.alert("操作失败" , shop.ajax_fun , obj_data);
					}
				}
			}
		});
	}
	this.ajax_back = function(data) {
		//回调函数
		if(shop.ajax_fun != '') {
			shop.ajax_fun(data);
			shop.ajax_fun = '';
		}
		var frm = window.parent.kj.obj('#winshopadmin_iframe');	
		if( frm && 'contentWindow' in frm) {
			frm.contentWindow.shop.refresh();
		} else {
			shop.refresh();
		}
	}
	this.refresh = function(msg) {
		var url = window.location.href;
		url = kj.urlencode(url , msg);
		window.location = url;
	}
	this.search = function() {
		var obj_s = kj.obj("input<<name,/^s_/i");
		var url = [];
		for(i = 0 ; i < obj_s.length ; i++){
			if(obj_s[i].type == "radio" || obj_s[i].type == "checkbox" ) {
				if( obj_s[i].checked ) url[url.length] = obj_s[i].name + "=" + obj_s[i].value;
			} else {
				url[url.length] = obj_s[i].name + "=" + obj_s[i].value;
			}
		}
		obj_s = kj.obj("select<<name,/^s_/i");
		for(i = 0 ; i < obj_s.length ; i++){
			if(obj_s[i].type == "radio" || obj_s[i].type == "checkbox" ) {
				if( obj_s[i].checked ) url[url.length] = obj_s[i].name + "=" + obj_s[i].value;
			} else {
				url[url.length] = obj_s[i].name + "=" + obj_s[i].value;
			}
		}
		this.refresh(url);
	}
	//清空查询
	this.clear_search = function() {
		kj.set("input<<name,/^s_/i",'value','');
		kj.set("select<<name,/^s_/i",'options[0].selected',true);
		this.search();
	}
	this.edittabel = function(id) {
		kj.hide('.pEditTable<<index,/^[^'+id+']/i');
		kj.set('#id_pMenu li','className','');
		kj.show('.pEditTable<<index,'+id);
		kj.addClassName('#id_pMenu li<<index,'+id,'sel');
	}
	this.selact = function(func) {
		var act = document.frm_main.selact.value;
		shop.frm_ajax(act , func);
	}
	this.ajax_delete = function(msgid , norefresh , func) {
		this.ajax_url({id:msgid , app_act:'delete' , refresh : !norefresh} , func);
	}

	this.ajax_url = function( o , func) {
		this.ajax_fun=func;
		var url;
		if( !('refresh' in o) ) o.refresh = true;
		this.ajax_url.refresh = o.refresh;
		if('url' in o) {
			url = o.url;
		} else {
			var app = document.frm_main.app.value;
			var app_module = document.frm_main.app_module.value;
			var app_act = '';
			var id = '';
			if('app' in o) app = o.app;
			if('app_module' in o) app_module = o.app_module;
			if('app_act' in o) app_act = o.app_act;
			if('id' in o) id = o.id;
			url=kj.cfg('baseurl') + "/" + kj.basename() + "?app=" + app + "&app_module=" + app_module + "&app_act=" + app_act + "&id=" + id;
			if( app_act == "del" || (app_act != 'delete' && app_act.indexOf("del")>=0 ) ){
				if(!confirm("确定要删除吗？")) {
					return;
				}
			} else if( app_act=="delete" || app_act.indexOf("delete") >= 0 ){
				if(!confirm("删除将不可恢复，确定要继续吗？")) {
					return;
				}
			}
		}
		kj.ajax.get(url , function(data) {
			var obj_data=kj.json(data);
			if(obj_data.isnull) {
				kj.alert("操作失败");
			} else {
				if(obj_data.code=='0' ) {
					if('msg' in obj_data && obj_data.msg!='') {
						kj.alert.show(obj_data.msg , function(){
							if(shop.ajax_fun) {
								shop.ajax_fun(data);
								shop.ajax_fun = '';
							}
							if(shop.ajax_url.refresh) location.reload();});
					} else {
						if(shop.ajax_fun) {
							shop.ajax_fun(data);
							shop.ajax_fun = '';
						}
						if(shop.ajax_url.refresh) location.reload();
					}
				} else {
					if("msg" in obj_data) kj.alert(obj_data.msg);
				}
			}
		});
	}
	this.menu_display = function(index) {
		var arr = kj.obj('.btnMenuDiv');
		var arr2 = kj.obj('.btnMenu');
		if(arr[index].style.display == 'none') {
			kj.set(arr , 'style.display' , 'none');
			kj.delClassName('.btnMenu' , 'btnMenuSel');
			kj.addClassName(arr2[index] , 'btnMenuSel');
			arr[index].style.display = '';
		} else {
			kj.delClassName(arr2[index] , 'btnMenuSel');
			arr[index].style.display = 'none';
		}
	}
}