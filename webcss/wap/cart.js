//购物车
var jscart = new function() {
	this.t = null;
	this.t_go = false;
	this.is_save = false;
	this.add = function(o , num , isinit) {
		if(!kj.obj('#id_cart_list')) return;
		num = kj.toint(num);
		num = (!num) ? 1 : num;
		var total = 0;
		if(kj.obj("#id_cart_"+o.id)) {
			this.change_num(o.id , num);
		} else {
			total = num * o.price;
			var html = '<li id="id_cart_'+o.id+'" class="xli"><input type="hidden" name="cartid[]" value="'+o.id+'"><input type="hidden" name="price[]" id="id_cart_price_'+o.id+'" value="'+o.price+'">';
				html += '<input type="text" name="num'+o.id+'[]" value="'+num+'" class="TcartNum Tcart_num_'+o.id+'">';
				html += '<span class="xprice Ttotal Tgtotal'+o.id+'">￥'+total+'</span>';
				html += '</li>';

				var obj_box = kj.obj("#id_cart_list");
				if(obj_box) obj_box.innerHTML =  obj_box.innerHTML + html;
		}
		//同步数量显示
		this.refresh_price(o.id);
	}
	this.tongbu_num = function(id) {
		var arr = kj.obj(".Tnumval"+id);
		var arrbtn = kj.obj(".Tnumbtn"+id);
		var num = kj.obj(".Tcart_num_"+id);
		num = (!num || num.length<1) ? 0 : kj.toint(num[0].value);
		for(var i = 0 ; i < arr.length ; i++) {
			if('value' in arr[i]) {
				arr[i].value = num;
			} else if('innerHTML' in arr[i]) {
				arr[i].innerHTML = num;
			}
		}
		if(num<1) {
			kj.hide(".Tghide"+id);
		} else {
			kj.show(".Tghide"+id);
		}
	}
	this.del = function(id) {
		if(id) {
			var obj = kj.obj("#id_cart_"+id);
			if(!obj) return;
			kj.remove(obj);
			kj.remove(".Tgoods_cart"+id);
		} else {
			kj.set("#id_cart_list","innerHTML","");
		}
		this.refresh_price(id);
	}
	//改变数量
	this.change_num = function(id , num , flag) {
		var obj_cart_num = kj.obj(".Tcart_num_" + id);
		if(!obj_cart_num || !('length' in obj_cart_num) || obj_cart_num.length<1 ) return;
		val = kj.toint(obj_cart_num[0].value);
		var i;
		if(num) {
			val = flag ? num : num+val;
			if(val<1) {
				this.del(id);
				return;
			}
			for(i = 0 ; i < obj_cart_num.length ; i++) {
				obj_cart_num[i].value = val;
			}
		}
		if(obj_cart_num) {
			var obj_price = kj.obj("#id_cart_price_"+id);
			val = val * kj.toint(obj_price.value);
			var arr = kj.obj(".Tgtotal"+id);
			for(i = 0 ; i < arr.length ; i++) {
				arr[i].innerHTML = kj.cfg("coinsign") + val.toFixed(2);
			}
			this.refresh_price(id);
		}
	}
	//刷新价格
	this.refresh_price = function(id) {
		if(id) this.tongbu_num(id);
		var obj = kj.obj("#id_cart_list .Ttotal");
		var price = 0;
		for(var i = obj.length-1 ; i >=0 ; i--) {
			price += kj.toint(obj[i].innerHTML);
		}
		kj.set(".TcartTotal" , 'innerHTML' , kj.cfg("coinsign")+price);
		//算数量
		arr_obj = kj.obj(".TcartAllNum");
		if(arr_obj && 'length' in arr_obj && arr_obj.length>0 ) {
			arr = kj.obj(".TcartNum");
			var num = 0;
			for(i = 0 ; i < arr.length; i++) {
				num += kj.toint(arr[i].value);
			}
			for(i = 0 ; i < arr_obj.length ; i++) {
				arr_obj[i].innerHTML = num;
			}
			if(num<1) {
				kj.hide("#id_toll_cartnum");
			} else {
				kj.show("#id_toll_cartnum");
			}
		}
		//如果是提交页面
		var obj_t = kj.obj(".Ttotalprice");
		if(obj_t && obj_t.length>0) {
			kj.set(".Ttotalprice" , "innerHTML" ,  kj.cfg("coinsign")+price);
		}
		if("undefined" != typeof(pageCart) && pageCart) pageCart.refresh_price(price);
		this.save_cookie();
		return price;
	}
	this.save_cookie = function() {
		if(this.is_save) return;
		var i,val,j,arr_1=[],arr = [];
		obj = kj.obj("#id_cart_list :cartid[]");
		for(i = 0 ; i < obj.length ; i++ ) {
			arr = kj.obj(".Tcart_num_"+obj[i].value);
			if(!arr || arr.length<1) continue;
			val = kj.toint(arr[0].value);
			arr_1[arr_1.length] = obj[i].value + "," + val;
		}
		var str_ids = arr_1.join("|");
		kj.cookie_set("mcart_ids" , str_ids , 24);
	}
	this.load = function() {
		if(!kj.obj('#id_cart_list')) return;
		kj.ajax.get(kj.url_view(kj.basename() + '?app=ajax&app_act=cart.list') , function(data) {
			var obj = kj.json(data);
			if(!obj) return;
			for(var i = 0 ; i < obj['list'].length ; i++) {
				jscart.add(obj['list'][i] , obj['list'][i]['num'] , true);
			}
		});
	}
}
jscart.load();