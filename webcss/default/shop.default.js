var jsshop = new function() {
	this.mintotal = 0;//最低起送价
	this.total = 0;//合计金额
	this.shopid = 0;
	this.cart_show = 0;
	this.cart_lock = false;
	this.is_save = false;
	this.sortby = '';
	this.sortval = '';
	this.obj_guige = {};
	this.objcate = [];
	this.guige_obj = null;
	this.guige_show = function(id,obj,type) {
		//是否为多规格
		if(!('id_'+id in this.obj_guige)) {
			return;
		}
		this.guige_obj = obj;
		this.guige_type = type;
		var html = '';
		var i;
		var idx = 'id_'+id;
		for(i = 0 ; i < this.obj_guige[idx].length ; i++) {
			html += '<font';
			if(i==0) {
				html += ' class="xsel"';
				kj.set("#id_guige .Tprice","innerHTML",this.obj_guige[idx][i].menu_price);
			}
			html += ' kjdata="{\'id\':'+this.obj_guige[idx][i].menu_id+',\'name\':\''+this.obj_guige[idx][i].menu_title+'\',\'price\':\''+this.obj_guige[idx][i].menu_price+'\'}"';
			html += ' onclick="jsshop.guige_sel(this);"';
			html += '>'+this.obj_guige[idx][i].menu_about_name+'</font>';
		}
		kj.obj("#id_guige_list").innerHTML = html;
		kj.show("#id_guige");
		var offset = kj.offset(obj);
		var left = offset.left-kj.w("#id_guige")+offset.width;
		var top = offset.top;
		kj.set("#id_guige","style.left",left+'px');
		kj.set("#id_guige","style.top",top+'px');
	}
	this.guige_close = function() {
		kj.hide("#id_guige");
		kj.set("#id_guige_num","innerHTML",1);
	}
	this.guige_num = function(val) {
		var num = kj.toint(kj.obj("#id_guige_num").innerHTML);
		num=num+val;
		if(num<1) num = 1;
		kj.set("#id_guige_num","innerHTML",num);
	}
	this.guige_add = function() {
		var arr = kj.obj("#id_guige .xsel");
		var obj = kj.getAttribute(arr[0],'kjdata');
		obj = kj.json(obj);
		this.cartin(this.guige_obj,this.guige_type);
		var num = kj.toint(kj.obj("#id_guige_num").innerHTML);
		this.cart_add({'id':obj.id,'name':obj.name,'pic':'','price':obj.price,'type':'','num':num});
		this.guige_close();
		this.save_cookie();
	}
	this.guige_sel = function(o) {
		var obj = kj.getAttribute(o,'kjdata');
		obj = kj.json(obj);
		kj.set("#id_guige .Tprice","innerHTML",obj.price);
		kj.delClassName("#id_guige_list font","xsel");
		kj.addClassName(o,"xsel");
	}
	this.cart_add = function(o) {
		var obj = kj.obj("#id_cart_box");
		var obj_cart_num = kj.obj("#id_cart_num_" + o.id);
		var num = 0;
		if(obj_cart_num) {
			var obj_cart_price = kj.obj("#id_cart_price_" + o.id);
			num = ('num' in o) ? o.num : 1;
			obj_cart_num.value = kj.toint(obj_cart_num.value) + num;
			obj_cart_price.innerHTML = kj.cfg("coinsign")+kj.format_price(kj.toint(obj_cart_num.value) * o.price);
			kj.set(".Tnum"+o.id,'value',obj_cart_num.value);
		} else {
			var arr = kj.obj(".Tnum" + o.id);
			if(arr && arr.length>0) num = kj.toint(arr[0].value);
			num = ('num' in o) ? num+o.num : num+1;
			var obj_li=document.createElement("li");
			obj_li.id = "id_cart_" + o.id;
			obj_li.innerHTML = '<input type="hidden" name="cartid[]" value="'+o.id+'"><input type="hidden" name="price[]" id="id_price_'+o.id+'" value="'+o.price+'"><span class="col1">'+o.name+'</span><span class="col4" id="id_cart_price_'+o.id+'">'+kj.cfg("coinsign")+kj.format_price(o.price)+'</span><span class="col3"><input type="button" name="btn_jian" value="" class="btn_jian" onclick="jsshop.change_num('+o.id+',-1);"> <input type="text" name="num'+o.id+'[]" value="'+num+'" id="id_cart_num_'+o.id+'" class="x_num" onkeyup="jsshop.change_num('+o.id+')"> <input type="button" name="btn_jian" value="" class="btn_jia" onclick="jsshop.change_num('+o.id+',1);"></span>';
			obj.appendChild(obj_li);
			kj.set(".Tnum"+o.id,'value',num);
			kj.show(".Tjian"+o.id);
			kj.show(".Tnum"+o.id);
			this.showcart(1);
		}
		this.refresh_price();
	}
	//删除
	this.del = function(id) {
		kj.remove("#id_cart_"+id);
		kj.hide(".Tjian"+id);
		kj.hide(".Tnumv"+id);
		this.refresh_price();
		this.showcart(1);
	}
	//改变数量
	this.change_num = function(id , num , noadd) {
		var obj_cart_num = kj.obj("#id_cart_num_" + id);
		val = kj.toint(obj_cart_num.value);
		if(num) {
			num = kj.toint(num);
			val= (noadd) ? num : val+num;
		}
		var arr = kj.obj(".Tnum" + id);
		if(val<1) {
			this.del(id);
			kj.set(".Tnum"+id,'value','');
			kj.hide(".Tjian"+id);
			kj.hide(".Tnumv"+id);
		} else {
			obj_cart_num.value = val;
			kj.set(".Tnum"+id,'value',val);
			kj.show(".Tjian"+id);
			kj.show(".Tnumv"+id);
		}
		if(obj_cart_num) {
			var obj_cart_price = kj.obj("#id_cart_price_" + id);
			var obj_price = kj.obj("#id_price_"+id);
			if(obj_cart_price && val>0) obj_cart_price.innerHTML = kj.cfg("coinsign")+ kj.format_price(kj.toint(val * kj.toint(obj_price.value)));
			this.refresh_price();
		}
	}
	//刷新价格
	this.refresh_price = function() {
		var obj = kj.obj("#id_cart_box .col4");
		var objnum = kj.obj("#id_cart_box .x_num");
		var price = 0;
		var num = 0;
		if(obj) num = obj.length;
		var ii = 0;
		for(var i = num-1 ; i >=0 ; i--) {
			price += kj.toint(obj[i].innerHTML);
			ii += kj.toint(objnum[i].value);
		}
		var html = (ii>0) ?  '(<font style="font-size:14px;color:#FF821E">'+ii+'</font>份)' : "";
		kj.set("#id_cart_menutit .x_2" , 'innerHTML' ,html);
		kj.set("#id_cart_menutit .x_3" , 'innerHTML' , kj.cfg("coinsign")+kj.format_price(price));
		this.total = price;
		if(price == 0) {
			this.showcart(0);
		} else {
			this.showcart(1);
		}
		this.save_cookie();
		return price;
	}
	//清空
	this.clear = function() {
		var obj = kj.obj("#id_cart_box");
		obj.innerHTML = '';
		this.refresh_price();
	}
	//提交，保存到cookie
	this.cart_submit = function() {
		var obj = kj.obj("#id_cart_box li");
		//检查是否已点餐
		if(obj.length<1) {
			alert("温馨提示：您的购物车是空的，请先点餐！");
			return false;
		}
		//点餐价格是否达到起送价
		if(this.mintotal>0 && this.total < this.mintotal) {
			alert("温馨提示：由于人力成本等问题，外卖订餐需起送不得低于"+this.mintotal+"元，不便之处还请您多多包涵！");
			return false;
		}
		this.save_cookie();
		var xid = this.shopid;
		if(this.menutype!=1) xid = xid + '-' + this.menutype;
		window.location.href = kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=cart&shop_id="+xid;
	}
	this.save_cookie = function() {
		var i,val,j,arr_1=[];
		obj = kj.obj("#id_cart_box :cartid[]");
		for(i = 0 ; i < obj.length ; i++ ) {
			val = kj.toint(kj.obj("#id_cart_num_"+obj[i].value).value);
			for(j=0;j<val;j++) {
				arr_1[arr_1.length] = obj[i].value;
			}
		}
		var xid = this.shopid;
		if(this.menutype!=1) xid = xid + '-' + this.menutype;
		var str = kj.cookie_get("cart_ids");
		var arr = [];
		if(str) arr = str.split("||");
		for(i = 0 ; i < arr.length ; i++) {
			arr_2 = arr[i].split(":");
			if(arr_2[0] == xid) {
				arr.removeat(i);break;
			}
		}
		if(arr_1.length>0) {
			arr[arr.length] = xid + ":" + arr_1.join("|");
		}
		var str_ids = arr.join("||");
		kj.cookie_set("cart_ids" , str_ids , 24);
	}
	this.showcart_fixed = function(obj) {
		if(obj.className == 'x_6') {
			this.showcart(1);
		} else {
			this.showcart(0);
		}
	}
	this.showcart = function(show) {
		if(this.cart_lock) return;
		this.cart_show = show;
		var h = document.documentElement.clientHeight - 32;
		var obj = kj.obj("#id_cart_menu");
		var offset = kj.offset(obj);
		var top = offset.top;
		this.cart_lock = true;
		if(show) {
			if(kj.obj("#id_cart_menu").style.display!='none') kj.set("#id_cart_menutit .x_top .x_6" , 'className' , 'x_4');
			var arr = kj.obj("#id_cart_box li");
			var h1 = arr.length*33;
			h1 = h-h1;
			if(h1<100) {
				h1 = 100;
				kj.addClassName("#id_cart_box","xon");
				kj.set("#id_cart_box","style.height",(h-h1)+'px');
			} else {
				kj.delClassName("#id_cart_box","xon");
				kj.set("#id_cart_box","style.height",'auto');
			}
			this.showcart_time('#id_cart_menu' , top , h1, -10);
		} else {
			kj.set("#id_cart_menutit .x_top .x_4" , 'className' , 'x_6');
			this.showcart_time('#id_cart_menu' , top , h , 50);
		}
	}
	this.showcart_time = function(id , top , top_target , val) {
		var obj = kj.obj(id);
		var x = top -  top_target;
		if(val > 0) x = x * -1;
		if( x > 0 ) {
			kj.set(obj, 'style.top' , top+'px');
			top+=val;
			window.setTimeout("jsshop.showcart_time('" + id + "'," + top + "," + top_target + "," + val + ")", 10);   
		} else {
			kj.set(obj, 'style.top' , top_target+'px');
			this.cart_lock = false;
		}
	}
	this.resize = function(){
		var h = document.documentElement.clientHeight;
		var offset = kj.offset("#id_menu_list");
		kj.set("#id_cart_menu" , 'style.top' , (h-32) + "px");
		kj.set("#id_cart_menutit" , 'style.top' , (h-32) + "px");
		var left = document.documentElement.scrollLeft || document.body.scrollLeft;
		kj.set("#id_cart_menu" , 'style.left' , (offset.left-left+offset.width) + "px");
		kj.set("#id_cart_menutit" , 'style.left' , (offset.left-left+offset.width) + "px");
		//定位label
		kj.set("#id_cart_menutit .x_top .x_4" , 'className' , 'x_6');
		this.cart_show = 0;
	}
	this.top = function() {
		if(document.documentElement.scrollTop) {
			document.documentElement.scrollTop = '0px';
		} else {
			document.body.scrollTop = '0px';
		}
	}
	this.menugroup = function(o) {
		if(!o) return;
		kj.show('#menugroup');
		var offset = kj.offset(o);
		var left = offset.left-kj.w('#menugroup');
		var top = offset.top+kj.h(o)-kj.h('#menugroup');
		kj.set('#menugroup' , 'style.left' , left+'px');
		kj.set('#menugroup' , 'style.top' , top+'px');
	}
	this.hash = function (name) {
		var obj = kj.obj(":"+name);
		if(obj.length<1) {
			var arr = name.split("_");
			this.hashname = name;
			var id = this.shopid;
			kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=grouplist&id="+id+"&index_group="+arr[1],function(data){
				kj.obj("#id_grouplist").innerHTML = data;
				window.location.hash=jsshop.hashname;
				if(jsfooter) jsfooter.align_height();
			});
		} else {
			window.location.hash=name;
		}
	}
	this.sort = function (val,obj) {
		kj.delClassName("#id_menu_sort i" , "sortdown");
		kj.delClassName("#id_menu_sort i" , "sortup");
		kj.delClassName("#id_menu_sort span" , "xsel");
		kj.addClassName(obj , "xsel");
		if(val == '') {
			this.shop_list(1);return;
		}
		if(val == this.sortval) {
			if(this.sortby == '') {
				kj.addClassName(kj.obj("i",obj),"sortdown");
				kj.delClassName(kj.obj("i",obj),"sortup");
				this.sortby = 'desc';
			} else {
				kj.addClassName(kj.obj("i",obj),"sortup");
				kj.delClassName(kj.obj("i",obj),"sortdown");
				this.sortby = '';
			}
		} else {
			this.sortval = val;
			if(this.sortby == '') {
				kj.addClassName(kj.obj("i",obj),"sortup");
				kj.delClassName(kj.obj("i",obj),"sortdown");
			} else {
				kj.addClassName(kj.obj("i",obj),"sortdown");
				kj.delClassName(kj.obj("i",obj),"sortup");
			}
		}
		if(this.hash_id > 0) {
			this.hash(this.hash_id,this.hashname);
		} else {
			this.shop_list();
		}
	}
	this.shop_list = function(val) {
		if(val == 1) {
			kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=shop.list&id="+this.shopid,function(data){
				kj.obj("#id_alllist").innerHTML = data;
				jsshop.refresh_cart();
			});
		} else {
			kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=shop.list&id="+this.shopid+"&sort="+this.sortval+"&sortby="+this.sortby,function(data){
				kj.obj("#id_alllist").innerHTML = data;
				jsshop.refresh_cart();
			});
		}
	}
	this.refresh_cart = function() {
		var arr = kj.obj(":cartid[]");
		var obj_cart_num;
		for(var i = 0 ; i < arr.length ; i++) {
			obj_cart_num = kj.obj("#id_cart_num_" + arr[i].value);
			val = kj.toint(obj_cart_num.value);
			kj.set(".Tnum"+arr[i].value,'value',val);
			kj.show(".Tjian"+arr[i].value);
			kj.show(".Tnum"+arr[i].value);
		}
	}
	this.comment = function(menu_id) {
		kj.dialog({id:'comment' + menu_id,title:'查看评论',url:kj.cfg('baseurl') + '/' + kj.basename() + '?app_act=comment&menu_id='+menu_id,w:800,'max_h':700,showbtnhide:false,showbtnmax:false,type:'iframe'});
	}
	this.showext = function() {
		var offset = kj.offset("#id_shoptopli");
		kj.set("#id_shopext","style.left",offset.left+"px");
		kj.show("#id_shopext");
		kj.set("#id_shopext","style.top",offset.top+"px");
		kj.set("#id_shopext ul","className","xover");
		kj.set("#id_shoptopli .xtit","className","xtit2");
	}
	this.hideext = function() {
		kj.hide("#id_shopext");
		kj.set("#id_shoptopli .xtit2","className","xtit");
	}
	this.cartin = function(obj,type) {
		var obj_img,arr,offset;
		if(type == 'img') {
			arr = kj.obj('img',kj.parent(obj,'li'));
			if(!arr || arr.length<1) return;
			obj = arr[0];
		    obj_img=document.createElement("img");
			offset = kj.offset(obj,true);
			obj_img.src = obj.src;
		} else {
			arr = kj.obj('.xname font',kj.parent(obj,'li'));
			if(!arr || arr.length<1) return;
			obj = arr[0];
		    obj_img=document.createElement("div");
			offset = kj.offset(obj,true);
			obj_img.innerHTML = '<font style="float:left;width:'+offset.width+'px;height:'+offset.height+'px;overflow:hidden">'+obj.innerHTML+'</font>';
		}
		obj_img.style.cssText="position:fixed;width:"+(offset.width)+"px;height:"+offset.height+"px";
		obj_img.style.top = offset.top+'px';
		obj_img.style.left = offset.left+'px';
		var strid = "id_div_"+Math.random();
		obj_img.id = strid
		document.body.appendChild(obj_img);
		var offset2 = kj.offset("#id_cart_menutit");
		this.cartin_move("#"+strid , (offset2.top-offset.top)/10 , (offset2.left-offset.left)/10 , offset.width/10 , offset.height/10 );
	}
	this.cartin_move = function(obj , top , left , width , height) {
		var offset = kj.offset(obj);
		var scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
		var w = offset.width-width;
		if(w<0) w = 0;
		var h = offset.height-height;
		var offset2 = kj.offset("#id_cart_menutit");
		offset2.top = offset2.top-offset.height;
		offset2.left = (width>0) ? offset2.left-width : offset2.left+width;
		if(h<0) h = 0;
		l = offset.left+left;
		if(left > 0 && l+w > offset2.left ) l = offset2.left-w;
		if(left < 0 && l+w < offset2.left ) l = offset2.left-w;
		t = offset.top+top;
		if(top > 0 && t > offset2.top ) t = offset2.top;
		if(top < 0 && t < offset2.top ) t = offset2.top;
		kj.set(obj , "style.width" , w+"px");
		kj.set(obj , "style.height" , h+"px");
		kj.set(obj , "style.top" , t+"px");
		kj.set(obj , "style.left" , l+"px");
		if(l == offset2.left && t == offset2.top && w == 0 && h == 0) {
			kj.remove(obj);
			return;
		}
		setTimeout("jsshop.cartin_move('"+obj+"',"+top+","+left+","+width+","+height+");",40);

	}
	this.show_cate2 = function(obj) {
		var offset = kj.offset(obj);
		var offset2 = kj.offset("#id_menu_list");
		kj.set("#id_shopcate2","style.left",offset2.left+'px');
		kj.set("#id_shopcate2","style.top",(offset.top+offset.height)+'px');
		kj.show("#id_shopcate2");
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
		var offset = kj.offset("#id_alllist");
		if(scrollTop>offset.top) {
			kj.addClassName("#id_menu_list",'atitle2');
			kj.addClassName("#id_shopinfo","shopinfo2");
			var left = offset.left+offset.width;
			kj.set("#id_shopinfo","style.left",(left+15)+'px');
			kj.show("#id_menu_list .xcate");
		} else {
			kj.delClassName("#id_menu_list",'atitle2');
			kj.delClassName("#id_shopinfo","shopinfo2");
			kj.hide("#id_menu_list .xcate");
		}
		var arr = kj.obj(".atitle .xtit");
		var name = '';
		if(arr && 'length' in arr && arr.length>0) {
			name = kj.getAttribute(arr[0],'kjdata');
		}
		for(var i = 0 ; i < this.objcate.length ; i++) {
			if(scrollTop>this.objcate[i].top) name = this.objcate[i].name;
		}
		kj.set("#id_menu_list .xtit","innerHTML",name);
	}
	this.movecate = function(obj , id) {
		var objp = obj.parentNode;
		var arr = kj.obj("a",objp);
		kj.delClassName(arr,'xsel');
		kj.addClassName(obj,'xsel');
		var offset = kj.offset("#id_atitle_"+id);
		var scrollTop = 0, bodyScrollTop = 0, documentScrollTop = 0;
		if(document.body){
			bodyScrollTop = document.body.scrollTop;
		}
		if(document.documentElement){
			documentScrollTop = document.documentElement.scrollTop;
		}
		scrollTop = (bodyScrollTop - documentScrollTop > 0) ? bodyScrollTop : documentScrollTop;
		var top = offset.top-scrollTop;
		this.moveto(scrollTop,top/5,offset.top-50);
	}
	this.moveto = function(start,len,end) {
		start += len;
		if(len>0) {
			if(start>=end) start = end;
		} else {
			if(start<=end) start = end;
		}
		scrollTo(0,start);
		if(start!=end) setTimeout("jsshop.moveto("+start+","+len+","+end+");",100);
	}

}
kj.onresize(function() {
	jsshop.resize();
});
window.onbeforeunload = function() {
	jsshop.save_cookie();
}
window.onscroll = function(){ 
	jsshop.onscroll();
}