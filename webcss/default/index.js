/*kj.handler(".listall table","mouseover",function(){
	//jsindex.over(this);
});
kj.handler(".listall table","mouseout",function(){
	//jsindex.out(this);
});*/
var jsindex = new function() {
		this.timer = null;
		this.obj = null;
		this.shop_id = 0;
		this.is_shophistory = 0;
		this.ajax_shop_id = 0;
		this.shoppage = 0;
		this.shoppages = 0;
		this.pagesize = 0;
		this.sortby = '';
		this.sortval = '';
		this.cssdir = '';
		this.over = function(o) {
			if(this.timer!=null) this.clear();
			this.obj = o;
			this.timer = setTimeout('jsindex.show();',1000);
		}
		this.out = function(o) {
			if(this.timer!=null) this.clear();
			this.timer = setTimeout('jsindex.hide();',200);
		}
		this.clear = function() {
			clearTimeout(this.timer);
			this.timer = null;
		}
		this.hide = function() {
			kj.hide("#id_shopinfo_"+this.shop_id);
		}
		this.show = function() {
			var shop_id = kj.getAttribute(this.obj,'shopid');
			this.shop_id = shop_id;
			if(kj.obj("#id_shopinfo_"+shop_id)) {
				this.show_display();
			} else {
				this.ajax_shop_id = this.shop_id;
				kj.ajax.get("?app_act=shopbeta&id="+shop_id,function(data){
					if(jsindex.ajax_shop_id == jsindex.shop_id) {
						var obj_div=document.createElement("div");
						obj_div.id="id_shopinfo_"+jsindex.ajax_shop_id;
						obj_div.className="shopinfo";
						obj_div.innerHTML = data;
						document.body.appendChild(obj_div);
						kj.handler("#"+obj_div.id,"mouseover",function(){
							jsindex.clear();
							kj.show(this);
						});
						kj.handler("#"+obj_div.id,"mouseout",function(){
							jsindex.clear();
							kj.hide(this);
						});

						jsindex.show_display();
					}
				});
			}
		}
		this.show_display = function() {
			kj.hide(".shopinfo");
			var o = this.obj;
			var offset = kj.offset(o);
			var left = offset.left+kj.w(o)-100;
			var top = offset.top;
			var body_h = document.documentElement.clientHeight;
			var shop_id = kj.getAttribute(o,'shopid');
			kj.show("#id_shopinfo_"+shop_id);
			var h = kj.h("#id_shopinfo_"+shop_id);
			var scrolltop = document.body.scrollTop || document.documentElement.scrollTop;
			if(top+h-scrolltop>body_h) top = body_h-h+scrolltop;
			kj.set("#id_shopinfo_"+shop_id,"style.left",left+'px');
			kj.set("#id_shopinfo_"+shop_id,"style.top",top+'px');
		}

		this.shopmore = function(type) {
			if(type) {
				this.shoppage = 0;
			}
			this.shoppage++;
			if(this.shoppage>this.shoppages && this.shoppage>1) return;
			if(this.shoppage==this.shoppages) kj.hide("#id_listmore");
			var url = "?app_act=shopmore&page="+this.shoppage;
			if(this.sortby) url +="&sortby="+this.sortby;
			if(this.sortval) url +="&sortval="+this.sortval;
			if(this.shoptype) url +="&shoptype="+this.shoptype;
			if(kj.obj("#id_collect_tab").className.indexOf('xsel')>=0) {
				url += "&iscollect=1";
			}
			var s_key = kj.obj("#id_s_key").value;
			if(kj.obj("#id_chk_addprice").className=='xsel') url += "&addprice=1";
			var dataval = {'s_key':s_key};
			kj.ajax.post(url,dataval , function(data){
				var arr_obj = kj.obj("#id_shopall ul");
				if(!arr_obj || arr_obj.length==0) return;
				var objul = arr_obj[0];
				if(jsindex.shoppage==1) {
					objul.innerHTML = data;
				} else {
					objul.innerHTML += data;
				}
				var obj = kj.obj("#id_pages");
				jsindex.shoppages = kj.toint(obj.value);
				kj.remove('#id_pages');
				jsindex.showstate();
				if(jsindex.shoppages == 0) {
					if(kj.obj("#id_collect_tab").className.indexOf('xsel')>=0) {
						objul.innerHTML += '<li class="xnone">没有收藏的商家</li>';
					} else {
						objul.innerHTML += '<li class="xnone">没有匹配的商家</li>';
					}
				}
			});
		}
		this.show_collect = function(o) {
			kj.delClassName(".title2 span" , 'xsel');
			kj.addClassName(o , 'xsel');
			kj.hide("#id_shophistory_list");
			kj.show("#id_collect_list");
		}
		this.show_shophistory = function(o) {
			kj.delClassName(".title2 span" , 'xsel');
			kj.addClassName(o , 'xsel');
			if(this.is_shophistory == 0 && kj.obj("#id_shophistory_list")) {
				kj.ajax.get("?app_act=shophistory",function(data){
					kj.obj("#id_shophistory_list").innerHTML = data;
					if(!kj.obj("#id_collect_list") && data=='') {
						kj.remove(".title2");
						kj.remove("#id_shophistory_list");
					} else {
						kj.show("#id_shophistory_list");
						kj.hide("#id_collect_list");
						kj.show(".title2");
					}
					jsindex.is_shophistory = 1;
				});
			} else {
				kj.show("#id_shophistory_list");
				kj.hide("#id_collect_list");
			}
		}
		//设置选中排序
		this.sort_init = function() {
			var arr = kj.obj("#id_act_sort span");
			var ii = this.sortby;
			kj.delClassName(arr,'xsel');
			kj.addClassName(arr[ii],'xsel');
			if(this.sortval == 1) {
				kj.set(kj.obj("i" , arr[ii]) , "className" , "sortup");
			} else {
				kj.set(kj.obj("i" , arr[ii]) , "className" , "sortdown");
			}
		}
		//设置排序
		this.sort = function(val) {
			var obj = kj.obj("#id_act_sort .xsel");
			var str = obj[0].innerHTML;
			kj.set("#id_act_sort i" , "className" , "");
			this.shoppage = 0;
			if(val == this.sortby) {
				var sortval = (this.sortval==1) ? 2 : 1;
				this.sortval = sortval;
				if(sortval==1) {
					kj.set(kj.obj("i" , obj[0]) , 'className' , 'sortup');
				} else {
					kj.set(kj.obj("i" , obj[0]) , 'className' , 'sortdown');
				}
			} else {
				//obj[0].innerHTML = str.substr(0,2);
				this.sortby = val;
				this.sortval = 2;
				//kj.delClassName("#id_act_sort li",'xsel');
				this.sort_init();
			}
			this.shopmore();
		}
		//仅显示营业店铺
		this.showstate = function() {
			var val = kj.obj("#id_chk_state").className;
			var x = (val=='xsel') ? 'none' : '';
			var obj = kj.obj("#id_shopall .Tclose");
			kj.set(obj,"style.display" , x);
			var arr = kj.obj("#id_shopall li");
			var ii = 0;
			for(var i = 0 ; i < arr.length ; i++) {
				if(arr[i].style.display=='') ii++;
			}
			if(jsindex.shoppages>jsindex.shoppage && ii>=jsindex.pagesize*jsindex.shoppage) {
				kj.show("#id_listmore");
			} else {
				kj.hide("#id_listmore");
			}
		}
		this.selshoptype = function(val , obj) {
			var arr = kj.obj("#id_typelist a");
			var index = kj.index(arr , obj);
			kj.delClassName(arr , 'xsel');
			kj.addClassName(arr[index] , 'xsel');
			this.shoptype = val;
			this.shoppage = 0;
			this.shopmore();
			kj.set("#id_xiala_cart span" , "innerHTML" , obj.innerHTML);
		}
		this.search = function(key) {
			var obj = kj.obj("#id_search_box");
			var offset = kj.offset("#id_form_search");
			var top = offset.top + offset.height;
			kj.set(obj,"style.top" , top + "px");
			kj.set(obj,"style.left" , offset.left + "px");
			var obj_ul = kj.obj("ul" , obj);
			kj.set(obj_ul[0] , "style.width" , offset.width + "px");
			kj.set(obj_ul[0] , "style.height" , "300px");
			if(key == '') {
				obj_ul[0].innerHTML = '';
				kj.hide("#id_search_box");
				return;
			}
			kj.show("#id_search_box");
			kj.ajax.get(kj.cfg('baseurl') + '/' + kj.basename() + '?app_act=area_search&skey='+key,function(data) {
				var obj_arr = kj.json(data);
				var arr = obj_arr.list;
				var html_shop = '',html_menu = '' , html_area = '';
				var html = '',arr1,area;
				for(var i = 0 ; i < arr.length ; i++ ) {
					if(arr[i].type=='shop') {
						arr1 = arr[i].shop_area.split(" ");
						area = '';
						if(arr1.length>0 && arr1[arr1.length-1] != '') area = "<font style='color:#888'>(" + arr1[arr1.length-1] + ")</font>";
						html_shop += "<a href='" + kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=shop&id=" + arr[i].shop_id + "'>" + arr[i].shop_name + area + "</a>";
					} else if(arr[i].type=='menu') {
						html_menu += '<a href="' + kj.cfg('baseurl') + '/' + kj.basename() + '?app_act=shop&id=' + arr[i].shop_id + '"><span class="x1">' + arr[i].menu_title + "</span><span class='x2'>" + arr[i].shop_name + "</span><span class='x3'>￥" +  arr[i].menu_price + "</span></a>";
					} else {
						html_area += '<a href="javascript:jsarea.position(' + arr[i].area_id + ',\'' + arr[i].area_name + '\')">' + arr[i].area_name + "</a>";
					}
				}
				if(html_area!='') html = "<li class='xtit'>&nbsp;&nbsp;区域</li><li>" + html_area + "</li>";
				if(html_shop!='') html += "<li class='xtit'>&nbsp;&nbsp;店铺</li><li>" + html_shop + "</li>";
				if(html_menu!='') html += "<li class='xtit'>&nbsp;&nbsp;菜品</li><li class='xmenu'>" + html_menu + "</li>";
				var arr_obj = kj.obj("#id_search_box ul");
				arr_obj[0].innerHTML = html;
			});
		}
		this.changelist = function(val , o ) {
			var obj = kj.obj("span" , kj.parent(o , 'li'));
			kj.delClassName(obj , "xsel");
			kj.addClassName(o , "xsel");
			this.shopmore(1);
		}
	this.onscroll = function(){
		var scrollTop = 0, bodyScrollTop = 0, documentScrollTop = 0;
		if(document.body){
			bodyScrollTop = document.body.scrollTop;
		}
		if(document.documentElement){
			documentScrollTop = document.documentElement.scrollTop;
		}
		scrollTop = (bodyScrollTop - documentScrollTop > 0) ? bodyScrollTop : documentScrollTop;

		var scrollHeight = 0, bodyScrollHeight = 0, documentScrollHeight = 0;
		if(document.body){
			bodyScrollHeight = document.body.scrollHeight;
		}
		if(document.documentElement){
			documentScrollHeight = document.documentElement.scrollHeight;
		}
		scrollHeight = (bodyScrollHeight - documentScrollHeight > 0) ? bodyScrollHeight : documentScrollHeight;
		var windowHeight = 0;
	　　if(document.compatMode == "CSS1Compat"){
			windowHeight = document.documentElement.clientHeight;
	　　}else{
			windowHeight = document.body.clientHeight;
	　　}
		var a= scrollTop + windowHeight;
		b = scrollHeight-100;
		var offset = kj.offset("#id_shopall");
		if(scrollTop>offset.top) {
			kj.addClassName("#id_toolbar",'div2x');
		} else {
			kj.delClassName("#id_toolbar",'div2x');
		}
		if(scrollTop + windowHeight >= scrollHeight-100){
			this.shopmore();
	　　}
	}


}

var cls_select = new function() {
	this.data = [];
	this.init = function() {
		var arr_obj = kj.obj(".Select");
		var option,obj;
		kj.handler(arr_obj , "click" , function(){
			var option = kj.getAttribute(this,"option");
			var obj = kj.obj("#" + option);
			var offset = kj.offset(this);
			var cls = kj.getAttribute(this, "selcls");
			var top = offset.top + offset.height;
			kj.set(obj , "style.top" , top + "px");
			kj.set(obj , "style.left" , offset.left + "px");
			kj.addClassName(this , cls);
			kj.show(obj);
			cls_select.data['clicker'] = obj;
		});
		for(var i = 0; i < arr_obj.length ; i++) {
			option = kj.getAttribute(arr_obj[i],"option");
			obj = kj.obj("#" + option);
			kj.set(obj , "style.position" , "absolute");
			kj.hide(obj);
		}
		var obj_option = kj.obj(".Option");
		for(i = 0 ; i < obj_option.length ; i++) {
			kj.handler(kj.childs(obj_option[i]) , "mouseover" , function(){
				var cls = kj.getAttribute(this.parentNode, "overcls");
				kj.addClassName(this , cls);
			});
			kj.handler(kj.childs(obj_option[i]) , "mouseout" , function(){
				var cls = kj.getAttribute(this.parentNode, "overcls");
				kj.delClassName(this , cls);
			});
			kj.handler(kj.childs(obj_option[i]) , "click" , function(){
				var obj_option = kj.obj(".Option");
				var obj_select = kj.obj(".Select");
				var index = kj.index(obj_option , this.parentNode);
				obj_select[index].innerHTML = this.innerHTML;
				cls_select.hide(obj_option[index].parentNode);
				var click = kj.getAttribute(this.parentNode, "click");
				var val = kj.getAttribute(this , "val");
				if(val == null) val = this.innerHTML;
				click = click + "('" + val + "');";
				eval(click);
			});
		}
		//body点击事件
		kj.handler(document.documentElement,"mousedown",function(e){
			if( !('clicker' in cls_select.data) || !cls_select.data['clicker'] ) return;
			var target = kj.event_target(e);
			var obj = kj.parent(target , ".Options");
			if(obj) {
				if('length' in cls_select.data['clicker']) {
					for(var i  = 0  ; i < cls_select.data['clicker'].length ; i++) {
						if(cls_select.data['clicker'][i] == obj) return;
					}
				} else if(cls_select.data['clicker'] == obj) {
					return;
				}
			}
			cls_select.hide( cls_select.data['clicker'] );
			cls_select.data['clicker'] = null;
		});
	}
	this.hide = function(obj) {
		var obj_options = kj.obj(".Options");
		var obj_select = kj.obj(".Select");
		var index = kj.index(obj_options , obj);
		kj.hide(obj_options[index]);
		var cls = kj.getAttribute(obj_select[index], "selcls");
		kj.delClassName(obj_select[index] , cls);
	}
}
//body点击事件
kj.handler(document.documentElement,"mousedown",function(e){
	var target = kj.event_target(e);
	var obj = kj.parent(target , "#id_search_box");
	var obj2 = kj.parent(target , ".msearch");
	if(obj || obj2) {
	} else {
		kj.hide("#id_search_box");
	}
});
window.onscroll = function(){ 
	jsindex.onscroll();
}