var jsshoplist = new function() {
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
	this.longitude = '';
	this.latitude = '';
	this.ispagein = false;
	this.over = function(o) {
		if(this.timer!=null) this.clear();
		this.obj = o;
		this.timer = setTimeout('jsshoplist.show();',1000);
	}
	this.out = function(o) {
		if(this.timer!=null) this.clear();
		this.timer = setTimeout('jsshoplist.hide();',200);
	}
	this.clear = function() {
		clearTimeout(this.timer);
		this.timer = null;
	}
	this.hide = function() {
		kj.hide("#id_shopinfo_"+this.shop_id);
	}
	this.shopmore = function(type) {
		if(type) {
			this.shoppage = 0;
		}
		if(this.shoppage>this.shoppages && this.shoppage>1) return;
		if(this.ispagein) return;
		this.shoppage++;
		this.ispagein = true;
		if(this.shoppage==this.shoppages) kj.hide("#id_listmore");
		var url = "?app_act=shopmore&page="+this.shoppage;
		if(this.sortby) url +="&sortby="+this.sortby;
		if(this.sortval) url +="&sortval="+this.sortval;
		if(this.shoptype) url +="&shoptype="+this.shoptype;
		if(this.longitude != '') url +="&longitude="+this.longitude;
		if(this.latitude != '') url +="&latitude="+this.latitude;
		/*if(kj.obj("#id_collect_tab").className.indexOf('xsel')>=0) {
			url += "&iscollect=1";
		}*/
		if(this.iscollect) url += "&iscollect=1";
		var s_key = kj.obj("#id_s_key") ? kj.obj("#id_s_key").value : '';
		if(kj.obj("#id_chk_addprice") && kj.obj("#id_chk_addprice").className=='xsel') url += "&addprice=1";
		var dataval = {'s_key':s_key};
		kj.ajax.post(url,dataval , function(data){
			var arr_obj = kj.obj("#id_shopall ul");
			if(!arr_obj || arr_obj.length==0) return;
			var objul = arr_obj[0];
			if(jsshoplist.shoppage==1) {
				objul.innerHTML = data;
			} else {
				kj.remove('#id_pages');
				objul.innerHTML += data;
			}
			var obj = kj.obj("#id_pages");
			jsshoplist.shoppages = kj.toint(obj.value);
			if(jsshoplist.shoppage >= jsshoplist.shoppages ) {
				kj.hide("#id_init_refresh");
			} else {
				kj.show("#id_init_refresh");
			}
			jsshoplist.showstate();
			if(jsshoplist.shoppages == 0) {
				objul.innerHTML += '<li class="xnone">没有搜索的商家</li>';
			}
			jsshoplist.ispagein = false;
		});
	}
	//设置选中排序
	this.sort_init = function() {
		var arr = kj.obj("#id_act_sort li");
		var ii = this.sortby;
		kj.delClassName(arr,'xsel');
		kj.addClassName(arr[ii],'xsel');
		kj.set("#id_act_tit font" , "innerHTML" , arr[ii].title);
		if(this.sortval == 1) {
			kj.set(kj.obj("i" , arr[ii]) , "className" , "sortup");
		} else {
			kj.set(kj.obj("i" , arr[ii]) , "className" , "sortdown");
		}
	}
	//设置排序
	this.sort = function(val , html) {
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
		kj.hide("#id_act_sort");
		kj.set("#id_act_tit font" , "innerHTML" , html);
		this.shopmore();
	}
	this.showsort = function() {
		var offset = kj.offset("#id_act_tit");
		kj.set("#id_act_sort" , "style.left" , offset.left+'px');
		kj.set("#id_act_sort" , "style.top" , (offset.top+offset.height)+'px');
		kj.set("#id_act_sort" , "style.width" , (offset.width+2)+'px');
		kj.show("#id_act_sort");
		kj.data['clicker'] = kj.obj("#id_act_sort");
	}
	this.showtype = function() {
		var offset = kj.offset("#id_type_tit");
		kj.set("#id_typelist" , "style.left" , (offset.left-1)+'px');
		kj.set("#id_typelist" , "style.top" , (offset.top+offset.height)+'px');
		kj.set("#id_typelist" , "style.width" , (offset.width+1)+'px');
		kj.show("#id_typelist");
		kj.data['clicker'] = kj.obj("#id_typelist");
	}
	this.showcondition = function() {
		var offset = kj.offset("#id_state_tit");
		kj.set("#id_condition" , "style.left" , (offset.left-1)+'px');
		kj.set("#id_condition" , "style.top" , (offset.top+offset.height)+'px');
		kj.set("#id_condition" , "style.width" , (offset.width+2)+'px');
		kj.show("#id_condition");
		kj.data['clicker'] = kj.obj("#id_condition");
	}
	this.showstate = function() {
		var x = (kj.obj("#id_chk_state") && kj.obj("#id_chk_state").className=='xsel') ? 'none' : '';
		var obj = kj.obj("#id_shopall .Tclose");
		kj.set(obj,"style.display" , x);
		var arr = kj.obj("#id_shopall li");
		var ii = 0;
		for(var i = 0 ; i < arr.length ; i++) {
			if(arr[i].style.display=='') ii++;
		}
		if(jsshoplist.shoppages>jsshoplist.shoppage && ii>=jsshoplist.pagesize*jsshoplist.shoppage) {
			kj.show("#id_listmore");
		} else {
			kj.hide("#id_listmore");
		}
	}

	this.selshoptype = function(val , obj ,html) {
		var arr = kj.obj("#id_typelist a");
		var index = kj.index(arr , obj);
		kj.delClassName(arr , 'xsel');
		kj.addClassName(arr[index] , 'xsel');
		this.shoptype = val;
		this.shoppage = 0;
		this.shopmore();
		kj.set("#id_type_tit font" , "innerHTML" , html);
		kj.hide("#id_typelist");
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
		if(scrollTop + windowHeight >= scrollHeight-100){
			this.shopmore();
	　　}
	}
   this.getLocation = function(){
	   var options={
		   enableHighAccuracy:true, 
		   maximumAge:1000
	   }
	   if(navigator.geolocation){
			//浏览器支持geolocation
			navigator.geolocation.getCurrentPosition(function(position) {
				//返回用户位置
				//经度
				jsshoplist.longitude =position.coords.longitude;
				//纬度
				jsshoplist.latitude = position.coords.latitude;
			},function(error) {
			   switch(error.code){
				   case 1:
				   //alert("位置服务被拒绝");
				   break;

				   case 2:
				   //alert("暂时获取不到位置信息");
				   break;

				   case 3:
				   //alert("获取信息超时");
				   break;

				   case 4:
				   //alert("未知错误");
				   break;
			   }
			},options);
		   
	   }else{
			//浏览器不支持geolocation
	   }
   }
}
window.onscroll = function(){ 
	jsshoplist.onscroll();
}