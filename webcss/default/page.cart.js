var pageCart = new function() {
	this.arealist = {};//json格式，指定id包函的子地区
	this.areainfo = {};//对应id地区详细信息
	this.depth = 0;//当前地区深度
	this.sendprice = 0;//配送费
	this.sendprice_default = 0;//默认配送费
	this.address_id = 0;
	this.address_oldid = 0;
	this.area_info_nums = 0;
	this.timeout = [];
	//地区下拉发生改变时触发
	this.changearea = function(val , index , defautval) {
		var obj,i,ii;
		index++;
		//当index大于深度时跳出
		if(index > this.depth) {
			this.refresh_area_val();
			return;
		}
		//发生改变后，重置之后的地区下拉
		for(i = index ; i < this.depth; i++) {
			obj = kj.obj("#id_area_" + i);
			if(!obj) break;
			obj.options.length = 0;
			if(i>index) {
				if(kj.obj("#id_area_" + i)) kj.obj("#id_area_" + i).style.display = 'none';
			}
		}
		var key = "id_" + val;
		if(val!='') {
			var key2 = key;
			this.minleast = this.shop_minleast;
			this.minaddprice = this.addprice;
			for(i = 0; i <10 ;i++) {
				if(key2 in this.areainfo) {
					if(kj.toint(this.areainfo[key2]["price"])>0 && kj.toint(this.areainfo[key2]["addprice"])>0) {
						this.minaddprice = this.areainfo[key2]["addprice"];
						this.minleast = this.areainfo[key2]["price"];
						break;
					} else if(this.areainfo[key2]["price"]>0) {
						this.minleast = this.areainfo[key2]["price"];
						break;
					} else if(this.areainfo[key2]["addprice"]>0) {
						this.minaddprice = this.areainfo[key2]["addprice"];
						break;
					}
				}

				if(key2 in this.areainfo && 'pid' in this.areainfo[key2] && this.areainfo[key2]["pid"]) {
					key2 = "id_" + this.areainfo[key2]["pid"];
				}
			}
		}
		kj.obj("#id_dispatch_0").innerHTML = this.minleast;
		kj.obj("#id_addprice_0").innerHTML = this.minaddprice;
		if(!(key in this.arealist) || !("length" in this.arealist[key]) || !kj.obj("#id_area_" + index)) {
			//跳出则刷新当前地区值
			if(kj.obj("#id_area_" + index)) kj.obj("#id_area_" + index).style.display = 'none';
			this.refresh_area_val();
			return;
		}
		var valdefault = '--请选择--';
		kj.add_option("#id_area_" + index , valdefault , '' );
		for(i = 0 ; i < this.arealist["id_"+val].length ; i++ ) {
			obj = kj.obj("#id_area_" + index);
			ii = this.arealist["id_"+val][i];
			if( !("id_" + ii in this.areainfo ) ) continue;
			kj.add_option(obj , this.areainfo["id_" + ii]["name"] , ii);
			//选中默认值
			if(obj.options[i+1].value == defautval) {
				obj.options[i+1].selected=true;
			}
		}
		if(kj.obj("#id_area_" + index)) kj.obj("#id_area_" + index).style.display = '';
		this.changearea(obj.value , index , defautval);
	}
	this.refresh_area_val = function() {
		var obj = kj.obj(":address_area_id[]");
		var arr_id = [];
		var arr_val = [];
		var val = '';
		var id = 0;
		for(var i = 0 ; i < obj.length ; i++) {
			if(obj[i].value != '') {
				if( !("id_" + obj[i].value in this.areainfo ) ) continue;
				arr_id[arr_id.length] = obj[i].value;
				val = ( 'val' in this.areainfo["id_" + obj[i].value] ) ? this.areainfo["id_" + obj[i].value]['val'] : this.areainfo["id_" + obj[i].value]['name'];
				arr_val[arr_val.length] = val;
			} else {
				break;
			}
		}
		if(arr_id.length>0) {
			kj.obj("#id_area_id").value = arr_id[arr_id.length-1];
		} else {
			kj.obj("#id_area_id").value = '';
		}
		kj.obj("#id_area_allid").value = arr_id.join(",");
		kj.obj("#id_area").value = arr_val.join(" ");
		if(this.address_id==0) {
			this.refresh_price();
		}
	}
	//提交订单
	this.submit = function() {
		//检测配送地区
		var arr_info = kj.obj("#id_cainfo .xsel");
		if(!arr_info || arr_info.length<1) {
			alert("请填写配送地址");
			return;
		}
		var obj_info = kj.json(kj.getAttribute(arr_info[0],'kjdata'));
		this.save_cookie();
		//收货信息
		document.frmMain.address_id.value = obj_info.id;
		//支付方式
		var arr = kj.obj("#id_paymethod .xsel");
		if(arr && arr.length>0) {
		} else {
			alert("请选择支付方式");
			return;
		}
		if(document.frmMain.cart_ids.value=='') {
			alert("当前购物车为空");
			return;
		}
		var total = kj.toint(kj.obj("#id_shop_total").innerHTML);
		var minleast = pageCart.minleast;
		if(minleast == 0) minleast = pageCart.shop_minleast;
		if(total<minleast){
			alert("金额必须达到"+kj.cfg("coinsign")+minleast+"，才起送");
			return;
		}

		if(document.frmMain.arrive.value=="") {
			alert("请选择配送时间");
			document.frm_main.arrive.focus();
			return;
		}
		var is_actaddprice = kj.obj(":act_addprice[]");
		is_actaddprice = (is_actaddprice && 'length' in is_actaddprice && is_actaddprice.length>0) ? 1 : 0;
		var addprice = this.minaddprice;
		if(is_actaddprice) addprice = 0;
		document.frmMain.btnsubmit.value="正在提交";
		document.frmMain.btnsubmit.disabled = true;
		document.frmMain.addprice.value = addprice;
		//是否需要短信验证
		if(this.is_orderverify(obj_info.tel)) return;
		kj.ajax.post(document.frmMain , function(data) {
			var obj_data = kj.json(data);
			if(obj_data.isnull) {
				alert("下单失败，原因：系统繁忙");
				document.frmMain.btnsubmit.value="去结算»";
				document.frmMain.btnsubmit.disabled = false;
				return;
			}
			if('code' in obj_data && obj_data.code=='0') {
				kj.remove(":menuid[]");
				pageCart.save_cookie();
				if(kj.cfg("isremote") == '1' && 'state' in obj_data && obj_data.state=='0') {
					kj.ajax.get(kj.cfg('baseurl') + "/common.php?app_module=meal&app=call&app_act=print&isremote=1&order_id=" + obj_data.id, function(){
						kj.alert.show("下单成功" , function() {
							window.open(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=cart.ok&id="+obj_data.id,"_self");
						});
					});
				} else {
					kj.alert.show("下单成功" , function(){
						window.open(kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=cart.ok&id="+obj_data.id,"_self");
					});
				}
			}else{
				if("msg" in obj_data){
					alert(obj_data.msg);
				}else{
					alert("下单失败");
				}
				document.frmMain.btnsubmit.value="再试一次";
				document.frmMain.btnsubmit.disabled=false;
			}

		});
	}
	//选择收货地址时
	this.infosel = function(o){
		kj.delClassName("#id_cainfo ul" , "xsel");
		kj.addClassName(o , "xsel");
		var kjdata = kj.json(kj.getAttribute(o,'kjdata'));
		var minleast = 0;
		var minaddprice = 0;
		if(kjdata && 'addprice' in kjdata) {
			minleast = kj.toint(kjdata.dispatch);
			minaddprice = kj.toint(kjdata.addprice);
		}
		this.minleast = minleast;
		this.minaddprice = minaddprice;
		this.refresh_price();
	}

	//刷新价格
	this.refresh_price = function(price) {
		if(!price) price = kj.toint(kj.obj("#id_shop_total").innerHTML);
		//先刷新活动计价
		this.act_where_money(price);

		kj.set("#id_shop_addprice",'innerHTML',kj.cfg("coinsign")+this.minaddprice);
		var is_actaddprice = kj.obj(":act_addprice[]");
		is_actaddprice = (is_actaddprice && 'length' in is_actaddprice && is_actaddprice.length>0) ? 1 : 0;
		var repayment = 0 ,i,j,x;
		var obj_repayment = kj.obj("#id_repayment_input");
		if(obj_repayment) repayment = kj.toint(obj_repayment.value);
		kj.set("#id_shop_repayment",'innerHTML',kj.cfg("coinsign")+repayment);
		var act_price = 0;
		if(kj.obj('#id_shop_act')) act_price = kj.toint(kj.obj('#id_shop_act').innerHTML);
		var total = price - act_price - repayment;
		if(this.minaddprice > 0 && !is_actaddprice) total = total + kj.toint(this.minaddprice);
		total = kj.toint(total);
		kj.set(".Talltotalprice" , "innerHTML" ,  kj.cfg("coinsign")+total);
		( this.minaddprice == 0  || is_actaddprice) ? kj.hide( kj.parent("#id_shop_addprice",'span') ) : kj.show(kj.parent("#id_shop_addprice",'span'));
		( repayment == 0 ) ? kj.hide( kj.parent("#id_shop_repayment",'span') ) : kj.show(kj.parent("#id_shop_repayment",'span'));
		( act_price == 0 ) ? kj.hide( kj.parent("#id_shop_act",'span') ) : kj.show(kj.parent("#id_shop_act",'span'));
		var arr = kj.obj("#id_act li");
		if(arr && arr.length>0) {
			kj.show("#id_act");
		} else {
			kj.hide("#id_act");
		}
		this.save_cookie();
		return total;
	}
	this.address_edit = function(id) {
		this.address_id = id;
		if(kj.obj("#id_edit_info")) {
			this.editaddress_html = kj.obj("#id_edit_info").innerHTML;
			kj.remove("#id_edit_info");
		}
		kj.dialog({'html':this.editaddress_html,'id':'editinfo','type':'html','title':'收货信息','w':600,'showbtnmax':false});
		var obj = kj.obj("#id_address_"+id) ? kj.json(kj.getAttribute(kj.obj("#id_address_"+id),'kjdata')) : {'addprice':0,'dispatch':0,'id':0,'name':'','tel':'','allid':'','address':''};
		document.frmEditinfo.address.value=obj.address;
		document.frmEditinfo.name.value=obj.name;
		document.frmEditinfo.tel.value=obj.tel;
		pageCart.verifytel = obj.tel;
		if(obj.allid!='') {
			obj.allid += '';
			var arr = obj.allid.split(",");
			//加载默认值选中
			pageCart.select_sel(kj.obj("#id_area_0") , arr[0]);
			for(var i=0 ; i < pageCart.depth ; i++) {
				if(arr.length<=i) break;
				pageCart.changearea(arr[i],i,arr[i+1]);
			}

		}
	}
	this.address_del = function(id) {
		if(!confirm("确定要删除吗？")) {
			return;
		}
		kj.ajax.get(kj.cfg('baseurl') + "/" + kj.basename() + "?app=ajax&app_act=address.del&id="+id , function(data) {
			var obj_data=kj.json(data);
			pageCart.submiting = false;
			if(obj_data.isnull) {
				alert("操作失败");
			} else {
				if('code' in obj_data && obj_data.code=='0') {
					kj.remove("#id_address_"+id);
					var arr = kj.obj("#id_cainfo ul");
					if(arr.length>0) {
						kj.event(arr[0],'click');
					}
					if(arr.length<=pageCart.area_info_nums || pageCart.area_info_nums==0) kj.show("#id_cainfo .xnew");
				}else{
					if("msg" in obj_data){
						alert(obj_data.msg);
					}else{
						alert("操作失败");
					}
				}
			}
		});
	}
	this.address_cancel = function() {
		kj.dialog.close("#wineditinfo");
	}
	this.get_allnum = function() {
		var obj_num = kj.obj("#id_menu_list input<<name,/^menunum/i");
		var num = 0;
		for(var i = 0 ; i <obj_num.length ; i++ ) {
			num += kj.toint(obj_num[i].value);
		}
		return num;
	}
	this.address_seldefault = function(allid) {
		if(!allid || allid=='') return;
		var arr = allid.split(",");
		//加载默认值选中
		this.select_sel(kj.obj("#id_area_0") , arr[0]);
		for(var i=0 ; i < this.depth ; i++) {
			//if(arr.length-1<=i) break;
			this.changearea(arr[i],i,arr[i+1]);
		}
		if(arr.length<2) this.refresh_area_val();
	}
	this.select_sel = function(obj,val) {
		var is_sel = false;
		for(var i = 0 ; i < obj.length; i++) {
			if(obj[i].value == val) {
				obj[i].selected = true;
				is_sel = true;
				break;
			}
		}
		if(!is_sel && obj.length>0 ) obj[0].selected = true;
	}
	//检查用户信息合法性
	this.chk_address = function() {
			for(var i = 0 ; i < this.depth ; i++) {
				x = kj.obj("#id_area_" + i);
				if(x && x.value=="" && x.style.display=='') {
					alert("请选择您所在地区范围");
					x.focus();
					return false;
				}
			}
			if(document.frmEditinfo.address.value=="") {
				alert("请填写详细收货地址");
				document.frmEditinfo.address.focus();
				return false;
			}
			if(document.frmEditinfo.name.value=="") {
				alert("请填写收货人姓名");
				document.frmEditinfo.name.focus();
				return false;
			}

			if(document.frmEditinfo.tel.value=='') {
				alert("请填写收货人联系电话");
				document.frmEditinfo.tel.focus();
				return false;
			}
			if(!kj.rule.types.tel(document.frmEditinfo.tel.value)) {
				alert("联系电话格式不正确");
				document.frmEditinfo.tel.focus();
				return false;
			}
			return true;
	}
	this.address_save = function() {
		var area = kj.obj("#id_area").value;
		var area_allid = kj.obj("#id_area_allid").value;
		var area_id = kj.obj("#id_area_id").value;
		if(this.chk_address() == false) return;
		//手机短信验证
		if(this.is_orderverify()) return;
		var data = {"id":this.address_id,"name":document.frmEditinfo.name.value,"area":area,"area_id":area_id,"area_allid":area_allid,"address":document.frmEditinfo.address.value,"tel":document.frmEditinfo.tel.value};
		kj.ajax.post(kj.cfg('baseurl') + "/" + kj.basename() + "?app=ajax&app_act=address.save" , data ,function(data){
			var obj_data=kj.json(data);
			pageCart.saveinfo = false;
			if(obj_data.isnull) {
				alert("保存失败");
			} else {
				if('code' in obj_data && obj_data.code=='0') {
					var id = "#id_address_" + pageCart.address_id;
					if(pageCart.address_id>0) {
						kj.set(id + " .xname" , "innerHTML" ,document.frmEditinfo.name.value );
						kj.set(id + " .xtel" , "innerHTML" ,document.frmEditinfo.tel.value );
						kj.set(id + " .xaddress" , "innerHTML" ,document.frmEditinfo.address.value );
						kj.set(id + " .xarea" , "innerHTML" ,kj.obj("#id_area").value );
						kj.set(id + " .Tdispatch" , "innerHTML" ,kj.obj("#id_dispatch_0").innerHTML);
						kj.set(id + " .Taddprice" , "innerHTML" ,kj.obj("#id_addprice_0").innerHTML);
					} else {
						pageCart.address_id = obj_data.id;
						kj.delClassName("#id_cainfo ul" , 'xsel');
						var html = '';
						html += '<li><font class="xname">' + document.frmEditinfo.name.value + '</font>&nbsp;&nbsp;&nbsp;<b>Tel：</b><font class="xtel">' + document.frmEditinfo.tel.value + '</font><span><a href="javascript:pageCart.address_edit('+obj_data.id+');"><img src="'+pageCart.jspath+'/images/edit.png"></a>&nbsp;&nbsp;<a href="javascript:pageCart.address_del('+obj_data.id+');"><img src="'+pageCart.jspath+'/images/del.png"></a></span></li>';
						html += '<li><font class="xarea">' + kj.obj("#id_area").value + '</font></li><li class="xaddress">' + document.frmEditinfo.address.value + '</li>';
						html += '<li>起送价：<font class="Tdispatch">' + kj.obj("#id_dispatch_0").innerHTML + '</font>，配送费：<font class="Taddprice">' + kj.obj("#id_addprice_0").innerHTML + '</font></li>';
						html += '<li class="xtag"></li>';
						var obj = document.createElement("ul");
						obj.id = 'id_address_'+obj_data.id;
						obj.className = 'xsel';
						obj.innerHTML = html;
						obj.onclick = function() {
							pageCart.infosel(this);
						}
						var arrul = kj.obj("#id_cainfo ul");
						kj.insert_before(kj.obj("#id_cainfo") , arrul[arrul.length-1] , obj);
						var arr = kj.obj("#id_cainfo ul");
						if(arr.length>pageCart.area_info_nums && pageCart.area_info_nums>0) kj.hide("#id_cainfo .xnew");
					}
					var kjdata = "{'id':"+pageCart.address_id+",'name':'"+document.frmEditinfo.name.value+"','tel':'"+document.frmEditinfo.tel.value+"','address':'"+document.frmEditinfo.address.value+"','allid':'"+document.frmEditinfo.area_allid.value+"','dispatch':'" + kj.obj("#id_dispatch_0").innerHTML + "','addprice':'" + kj.obj("#id_addprice_0").innerHTML + "'}"
					kj.setAttribute("#id_address_"+pageCart.address_id , "kjdata" , kjdata);
					kj.dialog.close("#wineditinfo");
				}else{
					if("msg" in obj_data){
						kj.alert(obj_data.msg);
					}else{
						kj.alert("操作失败");
					}
				}
			}
		});
	}
	this.delgoods = function(id) {
		var row = kj.obj("#id_goods_row"+id);
		var index = row.rowIndex;
		var table = kj.parent(row , "table");
		if(index || index == 0) table.deleteRow(index);
		if(jscart) jscart.del(id);
	}
	this.sendsms = function() {
		var tel = kj.obj("#id_orderverify_tel").innerHTML;
		kj.ajax.get(kj.cfg('baseurl') + "/common.php?app=sys&app_act=send.sms&tel="+tel+"&type=4" , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code != 0) {
				kj.alert(obj.msg);
				return;
			}
			kj.hide("#id_order_info1");
			kj.show("#id_order_info2");
			pageCart.timer = 120;
			pageCart.starttime(true);
		});
	}
	this.starttime = function(isstart) {
		var obj = kj.obj("#id_orderverify_timer");
		if(!obj) return;
		var timer = kj.toint(obj.innerHTML);
		//if(timer <= 0) timer = this.timer;
		timer--;
		if(isstart) timer = this.timer;
		if(timer<0) {
			kj.hide("#id_orderverify_timers");
			kj.show("#id_orderverify_resend");
		} else {
			obj.innerHTML = timer;
			setTimeout('pageCart.starttime()' , 1000);
		}
	}
	this.resendsms = function() {
		kj.show('#id_order_info1');
		kj.hide('#id_order_info2');
		kj.show("#id_orderverify_timers");
		kj.hide("#id_orderverify_resend");
	}
	this.verfiysms = function() {
		var val = kj.obj("#id_orderverify_code").value;
		var tel = kj.obj("#id_orderverify_tel").innerHTML;
		kj.ajax.get(kj.cfg('baseurl') + "/common.php?app=sys&app_act=verify.sms&tel="+tel+"&type=4&key=" + val , function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			if(obj.code != 0) {
				kj.alert(obj.msg);
			} else {
				pageCart.verifytel = kj.obj("#id_orderverify_tel").innerHTML;
				pageCart.verifytelpost = pageCart.verifytel;
				kj.dialog.close("#winverifytel");
				if(pageCart.verfiysmspost) {
					pageCart.submit();
				} else {
					pageCart.address_save();
				}
			}
		});
	}
	this.is_orderverify = function(tel) {
		if(this.order_verify==0) return false;
		if(!tel) {
			var tel = document.frmEditinfo.tel.value;
			if(tel == '') {
				alert("请输入手机号");
				return true;
			}
			if(this.verifytel == tel) return false;
			//已验证则不需要再验了
			var arr = kj.obj("#id_cainfo .xtel");
			var arrtel = [];
			for(var i = 0 ; i < arr.length ; i++) {
				arrtel[arrtel.length] = arr[i].innerHTML;
			}
			if(arrtel.indexOf(tel)>=0) return false;
			pageCart.verfiysmspost = false;
		} else {
			if(this.order_verify!=2) return false;
			if(pageCart.verifytelpost == tel) return false;
			pageCart.verfiysmspost = true;
		}
		var obj = kj.obj('#id_order_verify');
		if(obj) {
			this.verifycode_html = obj.innerHTML;
			kj.remove(obj);
		}
		kj.dialog({'html':this.verifycode_html,'id':'verifytel','type':'html','title':'手机验证','w':330,'showbtnmax':false,'showbtnhide':false,'showbtnclose':false});
		kj.obj("#id_orderverify_tel").innerHTML = tel;
		if(pageCart.verfiysmspost) {
			kj.hide("#id_order_info1");
			pageCart.sendsms();
		}
		pageCart.sendsms();
		return true;
	}
	this.insert_beta = function(val) {
		if(kj.obj('#id_beta').value=='') {
			kj.textarea_insertstr('#id_beta',val);}else{kj.textarea_insertstr('#id_beta','，'+val);
		}
		this.beta_keyup();
	}
	this.beta_keyup = function() {
		var val = kj.obj("#id_beta").value;
		var len = 30-val.length;
		if(len<=0) {
			kj.obj("#id_beta").value = val.substr(0,30);
			len = 0;
		}
		kj.obj("#id_beta_num").innerHTML = len;
	}
	this.act_where_money = function(total_price) {
		var act_money = 0;
		var obj_val;
		if(this.act_list.length>0) {
			var obj_box = kj.obj('#id_act');
			var obj_act,price,val,num,arr,i;
			num = this.get_allnum();
			for(i = 0; i < this.act_list.length ; i++) {
				obj_act = kj.obj("#id_act_"+this.act_list[i]['act_id']);
				obj_val = this.get_act_money(this.act_list[i]['act_id'] , this.act_list[i]['act_method'] , this.act_list[i]['act_method_val'] , total_price);
				if(this.act_list[i]['act_method'] == 1) this.act_list[i]['act_name'] += "(" + this.act_list[i]['act_method_val'] + ")";
				if( (this.act_list[i]['act_where'] == '1' && this.act_list[i]['where_val']<=total_price) || ((this.act_list[i]['act_where'] == '3' || this.act_list[i]['act_where'] == '4') && this.act_list[i]['where_val']<=num) || this.act_list[i]['act_where'] == '2' || this.act_list[i]['act_where'] == '5' || this.act_list[i]['act_where'] == '6' || (this.act_list[i]['act_where'] == '7' && document.frmMain.paymethod.value=='onlinepayment') ) {//达到指定金额
					if(!obj_act) {
						obj_act=document.createElement("li");
						obj_act.id="id_act_"+this.act_list[i]['act_id'];
						if(this.act_list[i]['act_method']=='7') {
							obj_act.innerHTML=this.act_list[i]['act_name'] + "<input type='hidden' name='act_addprice[]' value='1' id='id_act_addprice_"+this.act_list[i]['act_id']+"' extscore='{add:" + obj_val.addscore + ",bei:"+obj_val.beiscore+"}'><input type='hidden' name='shop_act_id[]' value='"+this.act_list[i]['act_id']+"'>";
						} else {
							obj_act.innerHTML = this.act_list[i]['act_name'] + "<input type='hidden' name='act_money[]' value='"+obj_val.money+"' id='id_act_money_"+this.act_list[i]['act_id']+"' extscore='{add:" + obj_val.addscore + ",bei:"+obj_val.beiscore+"}'><input type='hidden' name='shop_act_id[]' value='"+this.act_list[i]['act_id']+"'>";
						}
						obj_box.appendChild(obj_act);
					} else {
						kj.set("#id_act_money_"+this.act_list[i]['act_id'],"value",obj_val.money);
					}
				} else {
					if(obj_act) kj.remove(obj_act);//移除
				}
				if(this.act_list[i]['act_where'] == '4' || this.act_list[i]['act_where'] == '2') {
					val = 'id_'+ this.act_list[i]['act_id'];
					if(!this.timeout[val]) this.timeout[val] = setTimeout("pageCart.cancel_time_act("+this.act_list[i]['act_id']+");",this.act_list[i]['time']);
				}
			}
		}
		var obj_price = kj.obj(":act_money[]");
		for(i = 0;i<obj_price.length;i++) {
			act_money+=kj.toint(obj_price[i].value);
		}
		if(kj.obj("#id_shop_act")) kj.obj("#id_shop_act").innerHTML = act_money;
	}
	this.cancel_time_act = function(act_id) {
		for(var i = 0; i < this.act_list.length ; i++) {
			if(this.act_list[i]['act_id'] == act_id) {
				this.act_list.removeat(i);
				kj.alert.show("超过时间取消【"+this.act_list[i]['act_name']+"】优惠");
			}
		}
		kj.remove("#id_act_"+act_id);
		this.refresh_price();
	}
	this.get_act_money = function(id , method , method_val, total_price) {
		var money = 0;
		var objact = {'money':0,'addscore':0,'beiscore':0};
		method = kj.toint(method);
		switch(method) {
			case 3://送指定积分
				objact.addscore = method_val;
				break;
			case 4://积分翻倍
				objact.beiscore = method_val;
				break;
			case 2://打折
				objact.money = total_price-kj.toint(total_price*kj.toint(method_val)/10);
				break;
			case 5://立减多少
				objact.money = kj.toint(method_val);
				break;
			case 6://每份优惠多少
				var num = this.get_allnum();
				objact.money = kj.toint(method_val) * num;
				break;
		}
		return objact;
	}
	this.repayment_refresh = function() {
		var obj_repayment_input = kj.obj("#id_repayment_input");
		var repayment = kj.toint(obj_repayment_input.value);
		if(obj_repayment_input.value != repayment + '.') obj_repayment_input.value = repayment;
		var is_ok = true;
		val = this.user_repayment - repayment;
		if(val < 0) {
			obj_repayment_input.value = 0;
			val = this.user_repayment;
			obj_repayment_input.focus();
			is_ok = false;
		} else {
			var objshop = kj.obj("#id_shop_alltotal");
			var objrepayment = kj.obj("#id_shop_repayment");
			var x = kj.toint(objshop.innerHTML)+kj.toint(objrepayment.innerHTML);
			if( (x - repayment) < 0) {
				obj_repayment_input.value = x;
				val = this.user_repayment - x;
			}
		}
		kj.obj("#id_my_repayment").innerHTML = kj.cfg("coinsign") + val;
		this.refresh_price();
		return is_ok;
	}
	this.chk_radio = function( o ) {
		var objp = kj.parent(o , ".Tradio");
		kj.delClassName(kj.obj("li" , objp) , "xsel");
		kj.addClassName(o,"xsel");
		kj.set(kj.obj("input" , o) , "checked" , true);
		this.refresh_price();
	}


	//改变数量
	this.change_num = function(id , num , o , ischange) {
		var obj_cart_num = kj.obj(id);
		var val = kj.toint(obj_cart_num.value);
		val = ischange ? kj.toint(num) : val+kj.toint(num);
		if(val<1) {
			this.cart_remove(o); 
			return;
		}
		obj_cart_num.value = val;
		num = kj.toint(obj_cart_num.value);
		var shopid = "id_" + this.shopid;
		var key = id.replace('#id_num_','');
		this.refresh_menu_price();
		this.refresh_price();
	}
	this.refresh_menu_price = function() {
		//刷新菜品价格
		var arr_price = kj.obj("#id_menu_list :menuprice[]");
		var arr_num = kj.obj("#id_menu_list :menunum[]");
		var arr_id = kj.obj("#id_menu_list :menuid[]");
		var total = 0;
		var x = 0;
		for(i = 0 ; i < arr_price.length ; i++) {
			x = kj.toint(arr_price[i].value) * kj.toint(arr_num[i].value);
			total = total + x;
			kj.set(".Tgtotal"+arr_id[i].value , "innerHTML" , kj.cfg("coinsign")+kj.toint(x));
		}
		kj.set(".Ttotalprice" , "innerHTML" , kj.cfg("coinsign") + kj.toint(total));
	}
	//移除行
	this.cart_remove = function(o) {
		var obj_p = kj.parent(o,"tr");
		kj.remove(obj_p);
		this.refresh_menu_price();
		this.refresh_price();
	}
	this.init = function(data) {
		this.refresh_price();
	}
	//更新购物车
	this.save_cookie = function(isgo) {
		//取当前购物车值
		var arr_1 = [];
		var shopid = this.shopid;
		var arr_menuid = kj.obj(":menuid[]");
		var arr_menunum = kj.obj(":menunum[]");
		var i,j,num;
		for(i = 0 ; i < arr_menuid.length ; i++) {
			num = kj.toint(arr_menunum[i].value);
			for(j = 0 ; j < num ; j++) {
				arr_1[arr_1.length] = arr_menuid[i].value;
			}
		}
		if(isgo && arr_1.length<1) {
			alert("当前购物车为空，请选好菜品再来下单");
			return false;
		}
		var cartids = (arr_1.length>0) ? this.shopid + ":" + arr_1.join("|") : '';
		document.frmMain.cart_ids.value=cartids;
		var str = kj.cookie_get("cart_ids");
		var arr = [];
		if(str) arr = str.split("||");
		for(i = 0 ; i < arr.length ; i++) {
			arr_2 = arr[i].split(":");
			if(arr_2[0] == this.shopid) {
				if(cartids == '') {
					arr.removeat(i);
				} else {
					arr[i] = cartids;
				}
				break;
			}
		}
		var str_ids = arr.join("||");
		kj.cookie_set("cart_ids" , str_ids , 24);
	}
	this.arrive_change = function(val) {
		if(val == '') {
			kj.hide(":arrive2");
			kj.show(":arrive");
		} else {
			kj.show(":arrive2");
			kj.hide(":arrive");
		}
	}

}
var jsfile = document.scripts;
jsfile =jsfile[jsfile.length-1].src.substring(0,jsfile[jsfile.length-1].src.lastIndexOf("/")+1);
pageCart.jspath = jsfile;

kj.handler(".Tradio li" , "click" , function(e) {
	pageCart.chk_radio(this);
});