var jsarea = new function() {
	this.arealist = {};
	this.areainfo = {};
	this.selid = 0;
	this.onload = function() {
		kj.ajax.get(kj.cfg('dirpath') + "/" + kj.basename() + "?app_act=get.area",function(data) {
			var obj = kj.json(data);
			if(obj.isnull) return;
			jsarea.arealist = obj.list.list;
			jsarea.areainfo = obj.list.info;
			jsarea.selid = kj.toint(obj.selid);
			jsarea.init();
		});
	}

	this.init = function() {
		var obj = kj.obj("#id_selarealist ul");
		var i = 0,str = '',obj1,obj2;
		if(this.selid == 0) {
			for(i = 0 ; i < this.arealist['id_0'].length ; i++) {
				str += '<li><a href="#none">' + this.arealist['id_0'][i]['name'] + '</a></li>';
			}
			obj1 = kj.obj(".List_area" , obj[0]);
			obj1[0].innerHTML = str;
		}
		this.fill(this.areainfo['id_'+this.selid].pid , this.selid);
	}

	this.fill = function(pid , id) {
		var ii = 0,str='',name='';
		if(('id_' + pid) in this.arealist == false) return;
		if(('id_' + pid) in this.areainfo) {
			ii = this.fill(this.areainfo['id_'+pid].pid , pid);
		}
		var obj = kj.obj("#id_selarea li");
		for(i = 0 ; i < this.arealist['id_'+pid].length ; i++) {
			name = this.areainfo['id_'+this.arealist['id_'+pid][i]]['name'];
			if(this.arealist['id_'+pid][i] == id) {
				kj.set(obj[ii],"innerHTML" , name);
			}
			str += '<li><a href="javascript:void(0);" onclick="jsarea.sel(this);" aid="'+this.arealist['id_'+pid][i]+'" aname="'+name+'">' + name + '</a></li>';
		}
		var obj1 = kj.obj("#id_selarealist ul");
		kj.show(obj[ii]);
		obj1[ii].innerHTML = str;
		ii++;
		return ii;
	}
	this.click = function(obj , i) {
		var offset = kj.offset(obj);
		kj.show("#id_selarealist");
		var obj = kj.obj("#id_selarealist ul");
		kj.hide(obj);
		kj.show(obj[i]);
		var top = offset.top + offset.height;
		var left = offset.left-(kj.w("#id_selarealist")-offset.width)/2;
		kj.set("#id_selarealist" , "style.top" , top + "px");
		kj.set("#id_selarealist" , "style.left" , left + "px");

	}
	this.sel = function( o ) {
		var id = kj.getAttribute(o , 'aid');
		var name = kj.getAttribute(o , 'aname');
		var arr_list = kj.obj("#id_selarealist ul");
		var arr_tit = kj.obj("#id_selarea li");
		var obj_ul = kj.parent(o , "ul");
		var index = kj.index(arr_list , obj_ul);
		kj.set(arr_tit[index],"innerHTML" , name);
		kj.hide(arr_list[index]);
		index++;
		var str_id = "id_" + id;
		if(index >= arr_list.length || !(str_id in this.arealist) ) {
			this.position(id,name);return;
		}
		for(var i = index ; i < arr_list.length ; i++) {
			kj.set(arr_list[i] , "innerHTML","");
			kj.set(arr_tit[i] , "innerHTML","请选择");
			kj.hide(arr_list[i]);
			kj.hide(arr_tit[i]);
		}
		var str = '';
		for(i = 0 ; i < this.arealist['id_'+id].length ; i++) {
			name = this.areainfo['id_'+this.arealist['id_'+id][i]]['name'];
			str += '<li><a href="javascript:void(0);" onclick="jsarea.sel(this);" aid="'+this.arealist['id_'+id][i]+'" aname="'+name+'">' + name + '</a></li>';
		}
		kj.show(arr_tit[index]);
		arr_list[index].innerHTML = str;
		this.click(arr_tit[index],index);
	}

	this.position = function(id , name) {
		kj.cookie_set('area_id' , id , 20000 , '');
		kj.cookie_set('area_name' , name , 20000 , '');
		var url = kj.urlencode(location.href , 'app_area_id='+id);
		window.open(url, "_self");
	}
}

kj.onload(function(){
	jsarea.onload();
});
//body点击事件
kj.handler(document.documentElement,"mousedown",function(e){
	var target = kj.event_target(e);
	var obj = kj.parent(target , "#id_selarealist");
	if(obj) {
	} else {
		kj.hide("#id_selarealist");
	}
});