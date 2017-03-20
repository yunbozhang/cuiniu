var pageArea = new function() {
	this.depth = 10;
	this.area_id = 0;
	this.area_name = '';
	this.load = function() {
		return;
		var arr = kj.obj("#id_area font");
		if(!arr || arr.length<1) return;
		var id = arr[0].id.replace('id_xiala_','');
		this.select(id);
	}
	this.select = function(id,name,childs,depth,istop) {
		var obj = kj.obj("#id_xiala_"+id);
		if(!obj) return;
		var val = obj.innerHTML;
		kj.set("#id_xiala_tit font","innerHTML" , val);
		kj.hide("#id_list");
		kj.hide("#id_area");
		kj.show("#id_xiala_tit");
		kj.hide(".neararea");
		this.get_area(id,name,childs,depth,istop);
	}
	this.xiala_open = function() {
		if(kj.obj("#id_area").style.display=='') {
			kj.hide("#id_area");return;
		}
		var offset = kj.offset("#id_xiala_tit");
		var w = offset.width;
		var top = offset.top+offset.height;
		kj.set("#id_area" , "style.width" , w+'px');
		kj.set("#id_area ul" , "style.width" , (w-2)+'px');
		kj.set("#id_area" , "style.top" , top+"px");
		kj.set("#id_area" , "style.left" , offset.left+"px");
		kj.show("#id_area");
		kj.data['clicker'] = kj.obj("#id_area");
	}
	this.get_area = function(id,name,childs,depth,istop) {
		if(kj.toint(childs) <= 0 || this.depth<=depth) {
			this.selarea(id,name);
			return;
		}
		if(istop) this.path = [];
		var ispath = false;
		for(var i = 0 ; i < this.path.length ; i++) {
			if(this.path[i].id==id) ispath = true;
		}
		if(!ispath) this.path[this.path.length] = {'id':id,'name':name,'childs':childs,'depth':depth};
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
			arr_j = arr_j.sort();
			var html = "<table class='stable'>";
			for(i = 0 ; i < arr_j.length ; i++ ) {
				html += '<tr onmouseover="kj.addClassName(this,\'rowsel\')" onmouseout="kj.delClassName(this,\'rowsel\')"><td class="jian" width="50px" valign="top">' + arr_j[i] + '</td><td>';
				for(j = 0 ; j < arr_jian[arr_j[i]].length ; j++ ) {
					html += '<a href="javascript:pageArea.get_area('+arr_jian[arr_j[i]][j].area_id+',\''+arr_jian[arr_j[i]][j].area_name+'\',\''+arr_jian[arr_j[i]][j].area_childs+'\',\''+arr_jian[arr_j[i]][j].area_depth+'\');">' + arr_jian[arr_j[i]][j].area_name + '</a>';
				}
				html += '</td></tr>';
			}
			html += '</table>';
			kj.obj("#id_area_list").innerHTML = html;
			kj.show('#id_dialogbox');
		});
	}
	this.set_path = function(id) {
		var html = '';
		for(var i = 0 ; i < this.path.length ; i++) {
			html += '<a href="javascript:pageArea.get_area('+this.path[i].id+',\''+this.path[i].name+'\',\''+this.path[i].childs+'\',\''+this.path[i].depth+'\');">' + this.path[i].name + '</a>';
			if(id == this.path[i].id && i < this.path.length-1) {
				for(var j = i+1 ; j < this.path.length ; j++ ) {
					this.path.removeat(j);
				}
				break;
			}
		}
		kj.obj('#id_path').innerHTML = "<span onclick='pageArea.back_home()'>【返回】</span>" + html;
	}
	this.selarea = function(id,name) {
		kj.cookie_set('area_id' , id , 20000);
		kj.cookie_set('area_name' , name , 20000);
		//window.location.href = kj.cfg('baseurl');
		window.open("?" , "_self");
	}
	this.back_home = function() {
		this.path = [];
		kj.hide("#id_dialogbox");
		kj.hide("#id_xiala_tit");
		kj.show("#id_list");
		kj.show(".neararea");
	}
	this.selnow = function() {
		if(this.area_id == 0) {
			alert("请选择您当前所在的位置");
			return;
		}
		this.selarea(this.area_id,this.area_name);
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
				var longitude =position.coords.longitude;
				//纬度
				var latitude = position.coords.latitude;
				kj.ajax.get("index.php?app_act=get.location&longitude=" + longitude + "&latitude=" + latitude , function(data) {
					var objdata = kj.json(data);
					if(objdata.isnull) {
						return;
					}
					kj.obj("#id_neararea").innerHTML = objdata.area_name;
					pageArea.area_id = objdata.area_id;
					pageArea.area_name = objdata.area_name;
				});

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