var jsareasel = new function() {
	this.w = 0;
	this.h = 0;
	this.path = [];
	this.pids = [];
	this.search = function(key) {
		if(key == '') {
			kj.set("#id_search_list .y1",'innerHTML','');
			return;
		}
		kj.show("#id_search_list");
		kj.hide("#id_area_box");
		var url = kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=area_search&skey=" + key;
		kj.ajax.get(url,function(data) {
				var obj_arr = kj.json(data);
				var arr = obj_arr.list;
				var html_shop = '',html_menu = '' , html_area = '';
				var html = '<table class="stable">',arr1,area;
				for(var i = 0 ; i < arr.length ; i++ ) {
					if(arr[i].type=='shop') {
						arr1 = arr[i].shop_area.split(" ");
						area = '';
						if(arr1.length>0 && arr1[arr1.length-1] != '') area = "<font style='color:#888'>(" + arr1[arr1.length-1] + ")</font>";
						html_shop += "<a href='" + kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=shop&id=" + arr[i].shop_id + "'>" + arr[i].shop_name + area + "</a>";
					} else if(arr[i].type=='menu') {
						html_menu += '<a href="' + kj.cfg('baseurl') + '/' + kj.basename() + '?app_act=shop&id=' + arr[i].shop_id + '">' + arr[i].menu_title + "￥" +  arr[i].menu_price + '&nbsp;<font style="color:#888">(' + arr[i].shop_name + ')</font>' + "</a>";
					} else {
						html_area += '<a href="javascript:jsareasel.set_area(' + arr[i].area_id + ',\'' + arr[i].area_name + '\',\''+arr[i].area_childs+'\',\''+arr[i].area_depth+'\',\''+arr[i].area_pids+'\')">' + arr[i].area_name + "</a>";
					}
				}
				if(html_area!='') html += '<tr onmouseover="kj.addClassName(this,\'rowsel\')" onmouseout="kj.delClassName(this,\'rowsel\')"><td class="jian" width="50px" valign="top">区域</td><td>' + html_area + '</td></tr>';
				if(html_shop!='') html += '<tr onmouseover="kj.addClassName(this,\'rowsel\')" onmouseout="kj.delClassName(this,\'rowsel\')"><td class="jian" width="50px" valign="top">店铺</td><td>' + html_shop + '</td></tr>';
				if(html_menu!='') html += '<tr onmouseover="kj.addClassName(this,\'rowsel\')" onmouseout="kj.delClassName(this,\'rowsel\')"><td class="jian" width="50px" valign="top">菜品</td><td>' + html_menu + '</td></tr>';
				html += '</table>';

				jsareasel.pids = obj_arr.pids;
				arr = kj.obj("#id_search_list .y1");
				arr[0].innerHTML = html;
				jsareasel.showdialog();

		});
		kj.obj('#id_search_path').innerHTML = "<span onclick='jsareasel.back_home()'>关闭</span>";
	}
	this.set_area = function(id,name,childs,depth,pids) {
		if(kj.toint(childs)<1 || kj.toint(depth)>=this.depth) {
			this.select(id,name);
			return;
		}
		var arr = pids.split(",");
		for(var i = 0 ; i < arr.length ; i++ ) {
			if(arr[i] == this.city_id) {i++;break;}
		}
		var key = '';
		this.path = [];
		for(var i = i ; i < arr.length ; i++ ) {
			key  = 'id_' + arr[i];
			if( key in this.pids) {
				this.path[this.path.length] = {'id':this.pids[key].area_id,'name':this.pids[key].area_name,'childs':this.pids[key].area_childs,'depth':this.pids[key].area_depth};
			}
		}
		this.get_area(id,name,childs,depth);
	}
	this.set_path = function(id) {
		var html = '';
		for(var i = 0 ; i < this.path.length ; i++) {
			html += '<a href="javascript:jsareasel.get_area('+this.path[i].id+',\''+this.path[i].name+'\',\''+this.path[i].childs+'\',\''+this.path[i].depth+'\');">' + this.path[i].name + '</a>';
			if(id == this.path[i].id && i < this.path.length-1) {
				for(var j = i+1 ; j < this.path.length ; j++ ) {
					this.path.removeat(j);
				}
				break;
			}
		}
		kj.obj('#id_path').innerHTML = html + "<span onclick='jsareasel.back_home()'>关闭</span>";
	}
	this.get_area = function(id,name,childs,depth,istop) {
		if(kj.toint(childs) <= 0 || this.depth<=depth) {
			this.select(id,name);
			return;
		}
		if(istop) this.path = [];
		this.path[this.path.length] = {'id':id,'name':name,'childs':childs,'depth':depth};
		this.set_path(id);
		kj.show("#id_area_box");
		var url = kj.cfg('baseurl') + "/" + kj.basename() + "?app_act=area_next&pid=" + id;
		kj.ajax.get(url,function(data) {
			var arr = kj.json(data);
			var arr_jian = [];
			var arr_j = [];
			var jian,j;
			for(var i = 0 ; i < arr.length ; i++) {
				jian = arr[i].area_jian.substr(0,1);
				if(!(jian in arr_jian)) arr_jian[jian] = [];
				if(arr_j.indexOf(jian)<0) arr_j[arr_j.length] = jian;
				arr_jian[jian][arr_jian[jian].length] = arr[i];
				//html+="<li>"+arr[i].area_name+"</li>";
			}
			var html = "<table class='stable'>";
			for(i = 0 ; i < arr_j.length ; i++ ) {
				html += '<tr onmouseover="kj.addClassName(this,\'rowsel\')" onmouseout="kj.delClassName(this,\'rowsel\')"><td class="jian" width="50px" valign="top">' + arr_j[i] + '</td><td width="710px">';
				for(j = 0 ; j < arr_jian[arr_j[i]].length ; j++ ) {
					html += '<a href="javascript:jsareasel.get_area('+arr_jian[arr_j[i]][j].area_id+',\''+arr_jian[arr_j[i]][j].area_name+'\',\''+arr_jian[arr_j[i]][j].area_childs+'\',\''+arr_jian[arr_j[i]][j].area_depth+'\');">' + arr_jian[arr_j[i]][j].area_name + '</a>';
				}
				html += '</td></tr>';
			}
			html += '</table>';
			kj.obj("#id_area_list").innerHTML = html;
			jsareasel.showdialog();
		});
	}
	this.showdialog = function() {
		kj.show('#id_dialogbox');
		return;
		var h3 = kj.h("#id_dialogbox");
		var top3 = (jsareasel.h-h3)/2+'px';
		if(top3<0) top3 = 0;
		kj.set("#id_dialogbox","style.top",top3);
		var w3 = kj.w("#id_dialogbox");
		var left3 = (jsareasel.w-w3)/2+'px';
		kj.set("#id_dialogbox","style.left",left3);
	}
	this.back = function() {
		if(this.path.length>1) {
			this.path.removeat(this.path.length-1);
			this.get_area(this.path[this.path.length-1].id,this.path[this.path.length-1].name,this.path[this.path.length-1].childs);
		} else {
			this.back_home();
		}
	}
	this.back_home = function() {
		kj.delClassName('.div2 ul li' , 'xs');
		kj.hide("#id_dialogbox");
	}
	this.seltop = function(o) {
	}
	this.select = function(id,name) {
		kj.cookie_set('area_id' , id , 20000);
		kj.cookie_set('area_name' , name , 20000);
		//window.location.href = kj.cfg('baseurl');
		window.open(kj.cfg('baseurl')+"/" + kj.basename() + "?app_area_id="+id , "_self");

	}
	this.resize = function() {
		var h = document.documentElement.clientHeight;
		var w = document.documentElement.clientWidth;
		this.w = w;
		this.h = h;
	}
	this.showpic = function() {
		var arr = kj.obj("#id_backmain li");
		for(var i = 0 ;i < arr.length ; i++) {
			if(arr[i].style.display!='none') break;
		}
		var index = i+1;
		var h = this.h;
		if(index>=arr.length) {
			index=0;
			kj.set(arr[i],'style.top',-1*h+'px');
		} else {
			kj.set(arr[index],'style.top',-1*h+'px');
		}
		kj.opacity(arr[index],0);
		kj.show(arr[index]);
		this.timeindex1 = i;
		this.timeindex2 = index;
		this.starttime(99);
	}
	this.starttime = function(opacity) {
		var arr = kj.obj("#id_backmain li");
		kj.opacity(arr[this.timeindex1],opacity);
		var opacity2 = 100-opacity;
		if(opacity2<0) opacity2 = 0;
		kj.opacity(arr[this.timeindex2],opacity2);
		if(opacity2 == 100 ) {
			kj.hide(arr[this.timeindex1]);
			kj.show(arr[this.timeindex2]);
			kj.set(arr[this.timeindex2],'style.top','0px');
			setTimeout('jsareasel.showpic();',3000);
		} else {
			opacity--;
			setTimeout('jsareasel.starttime('+opacity+');',10);
		}
	}
}
//浏览器改变大小时调整大小
kj.onresize(function() {
	jsareasel.resize();
});