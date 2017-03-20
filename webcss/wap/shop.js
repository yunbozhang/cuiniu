var jsshop = new function() {
	this.cart = [];
	this.index = -1;
	this.total = 0;
	this.obj_guige = {};
	this.guige_obj = null;
	this.guige_show = function(id) {
		//是否为多规格
		if(!('id_'+id in this.obj_guige)) {
			return;
		}
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
		if(!kj.obj("#id_bglayer")) kj.bglayer("","#000",0.6,'{"click":"hide"}');
		kj.show("#id_guige");
	}
	this.guige_close = function() {
		kj.hide("#id_guige");
		kj.remove("#id_bglayer");
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
	this.cart_add = function(o,oclick) {
		var obj = kj.obj("#id_cart_list");
		var num = ('num' in o) ? o.num : 1;
		if(kj.obj("#id_cart_" + o.id)) {
			var obj_num = kj.obj("#id_cart_" + o.id + " .Tnum");
			num = kj.toint(obj_num[0].value) + num;
			obj_num[0].value = num;
			var price = kj.toint(num * o.price);
			kj.set("#id_cart_" + o.id + " .Tprice","innerHTML",price);
		} else {
			var obj_li=document.createElement("li");
			obj_li.id = "id_cart_" + o.id;
			var html = '<div>';
			html += '	<ul class="switchable-trigger row" onclick="jsshop.show_edit('+o.id+');"><input type="hidden" name="menuid[]" value="'+ o.id + '"><input type="hidden" name="price" value="'+ o.price + '">';
			html += '		<li class="w_12"><p><b>'+o.name+'</b></p></li>';
			html += '		<li class="w_6"><p><input type="hidden" value="'+num+'" class="Tnum">份</p></li>';
			html += '		<li class="w_6 red"><p>￥<font class="Tprice">'+o.price+'</font></p></li>';
			html += '		</ul>';
			html += '		<ul class="switchable-panel" style="display:none">';
			html += '		<div class="C_ I_cart_editA">';
			html += '			<ul class="row list_auto">';
			html += '			<li class="w_10"><a href="javascript:jsshop.del('+o.id+');"><i class="ico_remove red"></i></a></li>';
			html += '			<li class="w_4 center"><a href="javascript:jsshop.change_num('+o.id+',-1);"><i class="ico_minus2 green"></i></a></li>';
			html += '			<li class="w_6"><p class="input"><input class="center Tnumval" value="1" type="text" ></p></li>';
			html += '			<li class="w_4 center"><a href="javascript:jsshop.change_num('+o.id+',1);"><i class="ico_plus2 green"></i></a></li>';
			html += '			</ul>';
			html += '		</div>';
			html += '		</ul>';
			html += '	</div>';
			obj_li.innerHTML = html;
			obj.appendChild(obj_li);
		}
		kj.set(".Tmenu" + o.id + " .Tbtnjian" , 'style.visibility' , '');
		kj.set(".Tmenu" + o.id + " .Tnum" , 'style.visibility' , '');
		kj.set(".Tmenu" + o.id + " .Tnum","value" , num);
		this.refresh_catenum(oclick);
		this.cart_refresh(oclick);
	}
	this.refresh_catenum = function(oclick) {
		if(!kj.obj("#id_right_list")) return;
		var arrnum,catnum,i,j,arrx,catid;
		if(oclick) {
			var objp = kj.parent(oclick,'.Tcat');
			catid = kj.getAttribute(objp , 'datacateid');
			if(catid=='0') oclick=false;
		}
		if(oclick) {
			var objx = kj.parent(oclick,".Tcat");
			catid = kj.getAttribute(objx , 'datacateid');
			arrnum = kj.obj(".Tnum" , objx);
			catnum = 0;
			for(i = 0 ;i < arrnum.length; i++) {
				catnum += kj.toint(arrnum[i].value);
			}
			if(catnum>0) {
				kj.set("#id_cateli"+catid+" span","innerHTML" , catnum);
				kj.show("#id_cateli"+catid+" span");
			} else {
				kj.hide("#id_cateli"+catid+" span");
			}
		} else {
			arrx = kj.obj("#id_categorybox li");
			for(i = 0 ; i < arrx.length ; i++) {
				catid = kj.getAttribute(arrx[i] , 'datacateid');
				if(catid=='0') continue;
				arrnum = kj.obj("#id_right_list .Tcat"+catid + " .Tnum");
				catnum = 0;
				for(j = 0 ; j < arrnum.length ; j++) {
					catnum+=kj.toint(arrnum[j].value);
				}
				if(catnum>0) {
					kj.show(kj.obj("span" , arrx[i]));
					kj.set(kj.obj("span" , arrx[i]),'innerHTML' , catnum);
				} else {
					kj.hide(kj.obj("span" , arrx[i]));
				}
			}
		}
		this.cart_refresh(oclick);
	}
	this.cart_refresh = function() {
		var arr = kj.obj("#id_cart_box .Tnum");
		var num = 0;
		for(var i = 0 ; i < arr.length ; i++) {
			num += kj.toint(arr[i].value);
		}
		arr = kj.obj("#id_cart_box .Tprice");
		var total = 0;
		for(var i = 0 ; i < arr.length ; i++) {
			total += kj.toint(arr[i].innerHTML);
		}
		kj.set("#id_total_cart .Tnum","innerHTML" , num);
		kj.set("#id_total_cart .Tprice","innerHTML",kj.toint(total));
		this.total = total;
		if(kj.obj("#id_cart_submit")) {
			if(this.mintotal>total) {
				kj.obj("#id_cart_submit").value = "满"+kj.cfg('coinsign')+this.mintotal+'起送';
				kj.obj("#id_cart_submit").disabled = true;
				kj.obj("#id_cart_submit").className = 'xnone';
			} else {
				if(this.menutype == '2' && total==0) {
					kj.obj("#id_cart_submit").value = "返回订单";
				} else if(this.menutype == '2') {
					kj.obj("#id_cart_submit").value = "确认上菜";
				} else {
					kj.obj("#id_cart_submit").value = "去结算";
				}
				kj.obj("#id_cart_submit").disabled = false;
				kj.obj("#id_cart_submit").className = 'xbtn';
			}
		}
		this.save_cookie();
	}
	this.cart_click = function() {
		var obj = kj.obj("#id_cart_box");
		if(obj.style.display=='none') {
			kj.show(obj);
			var top = kj.toint(document.documentElement.scrollTop);
			if(top==0) top = kj.toint(document.body.scrollTop);
			kj.set('#id_popup_cart','style.top',top+'px');
		} else {
			kj.hide(obj);
			kj.hide("#id_cart_box .switchable-panel");
		}
	}
	this.clear = function() {
		var arr = kj.obj("#id_cart_list :menuid[]");
		for(var i = 0 ; i < arr.length ; i++) {
			kj.set(".Tmenu" + arr[i].value + " .Tnum","style.visibility","hidden");
			kj.set(".Tmenu" + arr[i].value + " .Tnum","value" , 0);
		}
		kj.set("#id_cart_list","innerHTML","");
		this.cart_refresh();
	}
	this.show_edit = function(id) {
		kj.hide("#id_cart_box .switchable-panel");
		var objx = kj.obj("#id_cart_" + id + " .switchable-panel");
		var x =objx[0].style.display;
		if(x == 'none') {
			kj.show("#id_cart_" + id + " .switchable-panel");
		}
		var arr = kj.obj("#id_cart_" + id + " .Tnum");
		kj.set("#id_cart_" + id + " .Tnumval","value",arr[0].value);
	}
	this.del = function(id) {
		kj.set(".Tmenu" + id + " .Tnum","style.visibility","hidden");
		kj.set(".Tmenu" + id + " .Tnum","value" , 0);
		kj.remove("#id_cart_" + id);
		this.cart_refresh();
	}
	this.change_num = function(id,num,oclick) {
		var arr = kj.obj("#id_cart_" + id + " .Tnum");
		if(!arr) return;
		num = num + kj.toint(arr[0].value);
		if(num<1) {
			this.del(id);
			kj.set(".Tmenu" + id + " .Tbtnjian" , 'style.visibility' , 'hidden');
			kj.set(".Tmenu" + id + " .Tnum" , 'style.visibility' , 'hidden');
			kj.set(".Tmenu" + id + " .Tnum","value" , '0');
			this.refresh_catenum(oclick);
			return;
		}
		arr[0].value = num;
		arr = kj.obj("#id_cart_" + id + " :price");
		var price = kj.toint(num * kj.toint(arr[0].value));
		kj.set("#id_cart_" + id + " .Tprice" , "innerHTML" , price);
		kj.set(".Tmenu" + id + " .Tnum","value" , num);
		kj.set("#id_cart_"+id + " .Tnumval" , "value" , num);
		this.refresh_catenum(oclick);
	}
	//提交，保存到cookie
	this.cart_submit = function() {
		var obj = kj.obj("#id_cart_list div");
		//检查是否已点餐
		if(obj.length<1) {
			kj.alert("温馨提示：您的购物车是空的，请先点餐！");
			return false;
		}
		//点餐价格是否达到起送价
		if(this.mintotal>0 && this.total < this.mintotal) {
			kj.alert("温馨提示：由于人力成本等问题，外卖定餐需起送不得低于"+this.mintotal+"元，不便之处还请您多多包涵！");
			return false;
		}
		var url = kj.urlencode(location.href , "app_act=cart&shop_id="+this.shopid);
		this.save_cookie();
		window.location.href = url;

	}
	this.save_cookie = function() {
		var i,val,arr,j,arr_1=[];
		obj = kj.obj("#id_cart_list :menuid[]");
		for(i = 0 ; i < obj.length ; i++ ) {
			arr = kj.obj("#id_cart_"+obj[i].value + " .Tnum");
			val = kj.toint(arr[0].value);
			for(j=0;j<val;j++) {
				arr_1[arr_1.length] = obj[i].value;
			}
		}
		var str = kj.cookie_get("cart_ids");
		var arr = [];
		if(str) arr = str.split("||");
		for(i = 0 ; i < arr.length ; i++) {
			arr_2 = arr[i].split(":");
			if(arr_2[0] == this.shopid) {
				arr.removeat(i);break;
			}
		}
		arr[arr.length] = this.shopid + ":" + arr_1.join("|");
		var str_ids = arr.join("||");
		kj.cookie_set("cart_ids" , str_ids , 24);
	}
	this.cate_showitem = function(o , id) {
		if(!kj.obj("#id_right_list")) return;
		kj.delClassName('#id_categorybox li' , 'xsel');
		kj.addClassName(o , 'xsel');
		if(id == '-1') {
			kj.show(".Tcat");
		} else {
			kj.hide(".Tcat");
			kj.show(".Tcat"+id);
		}
		kj.set("#id_right_list" , "style.height" , 'auto');
		var offset = kj.offset("#id_right_list");
		if(document.documentElement.scrollTop) {
			document.documentElement.scrollTop = offset.top;
		} else {
			var h2 = kj.h("#id_categorybox");
			var h3 = kj.h("#id_right_list");
			if(h3<h2) kj.set("#id_right_list" , "style.height" , h2+'px');
			//document.body.scrollTop = offset.top;
		}
	}
	this.cate_position = function() {
		if(!kj.obj("#id_right_list")) return;
		var offset = kj.offset("#id_right_list");
		var t = document.documentElement.scrollTop || document.body.scrollTop;
		var top = offset.top-t;
		if(top<0) top = 0;
		var h2 = kj.h("#id_categorybox");
		var h = document.documentElement.clientHeight-top+60;
		kj.set("#id_categorybox" , "style.height" , h+'px');
		h2 = h;
		if(h2<h || top==0) {
		}
		var h3 = kj.h("#id_right_list");
		if(h3<h2) kj.set("#id_right_list" , "style.height" , h2+'px');
		if(top!=0) {
			if(kj.obj("#id_categorybox").className != 'catebox') {
				kj.delClassName("#id_categorybox" , "catebox2");
				kj.addClassName("#id_categorybox" , "catebox");
			}
			return;
		}
		kj.delClassName("#id_categorybox" , "catebox");
		kj.addClassName("#id_categorybox" , "catebox2");
		if(top==0) top = 40;
		kj.set("#id_categorybox" , "style.top" , top+'px');
	}
	this.cartin = function(obj) {
		var obj_img,arr,offset;
		obj_img=document.createElement("div");
		offset = kj.offset(obj,true);
		obj_img.innerHTML = '<font style="float:left;width:10px;height:10px;background:#ff0000">&nbsp;</font>';
		obj_img.style.cssText="position:fixed;width:10px;height:10px;border-radius:5px";
		obj_img.style.top = offset.top+'px';
		obj_img.style.left = offset.left+'px';
		var strid = "id_div_"+Math.random();
		obj_img.id = strid;
		document.body.appendChild(obj_img);
		var offset2 = kj.offset("#id_cartnum");
		this.cartin_move("#"+strid , (offset2.top-offset.top)/10 , (offset2.left-30-offset.left)/10,1);
	}
	this.cartin_move = function(obj , top , left , ii) {
		var offset = kj.offset(obj);
		var width,height;
		var scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
		var offset2 = kj.offset("#id_cartnum");
		l = offset.left+left;
		var w = 30,h=10;
		if(left > 0 && l+w > offset2.left ) l = offset2.left-w;
		if(left < 0 && l+w < offset2.left ) l = offset2.left-w;
		t = offset.top+top;
		if(ii == 1) {
			t = offset.top-10;
			l = offset.left-15;
		} else if(ii < 4) {
			l = offset.left-10;
			t = offset.top-10;
		} else if(ii < 6) {
			l = offset.left-15;
			t = offset.top-5;
		} else if(ii < 8) {
			l = offset.left-10;
			t = offset.top+2;
		} else if(ii < 10) {
			l = offset.left-8;
			t = offset.top+10;
			top = (offset2.top-t)/10;
			left = (offset2.left-l-30)/10;
		}
		if(top > 0 && t > offset2.top ) t = offset2.top;
		if(top < 0 && t < offset2.top ) t = offset2.top;
		kj.set(obj , "style.top" , t+"px");
		kj.set(obj , "style.left" , l+"px");
		if(t == offset2.top) {
			setTimeout("kj.remove('"+obj+"');",100);
			return;
		}
		ii++;
		setTimeout("jsshop.cartin_move('"+obj+"',"+top+","+left+","+ii+");",20);

	}
}
window.onbeforeunload = function() {
	jsshop.save_cookie();
}
kj.onload(function(){
	if(!kj.obj("#id_right_list")) return;
	jsshop.cate_position();
	jsshop.refresh_catenum();
	
});
kj.handler("#id_categoryboxli" , "touchmove",function(e){
     // 如果这个元素的位置内只有一个手指的话
    if (event.targetTouches.length == 1) {
　　　　event.preventDefault();// 阻止浏览器默认事件，重要 
        var touch = event.targetTouches[0];
        // 把元素放在手指所在的位置
		var mheight = (jsshop.mheight) ? jsshop.mheight : 0;
		jsshop.mheight = touch.pageY;
		if(mheight == 0) return;
		var len = touch.pageY-mheight;
		var top = kj.toint(kj.obj("#id_categoryboxli").style.marginTop);
		top = top+len;
		kj.set("#id_categoryboxli" , "style.marginTop" , top+'px');
    }
});

kj.handler("#id_categoryboxli" , 'touchend' , function(event) {
	jsshop.mheight = 0;
	var h1 = kj.h("#id_categorybox");
	var h2 = kj.h("#id_categoryboxli")+70;
	if(h2<=h1) {
		kj.set("#id_categoryboxli" , "style.marginTop" , '0px');
		return;
	}
	var mtop = kj.toint(kj.obj("#id_categoryboxli").style.marginTop);
	if(mtop > 0) {
		kj.set("#id_categoryboxli" , "style.marginTop" , '0px');
		return;
	}
	h = h2-h1;
	if(mtop+h<0) {
		kj.set("#id_categoryboxli" , "style.marginTop" , (h*-1-30)+'px');
		return;
	}
});


window.onscroll = function(){ 
	jsshop.cate_position();
}