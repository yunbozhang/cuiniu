kj.tool = new function() {
	this.select_list = [];
	this.show_select = function(obj , id) {
		var id2 = id.substr(1);
		if(!kj.obj("#id_bglayer")) kj.bglayer(id2,"#000",0.6,'{"click":"hide"}');
		var w = kj.w() * 0.8;
		kj.show(id);
		var h = document.body.clientHeight || document.documentElement.clientHeight;
		var h1 = h * 0.8;
		var h2 = kj.h(id);
		kj.set(id , 'style.width',w+'px');
		if(h2>h1) {
			kj.addClassName(id,'scroll');
			kj.set(id , 'style.height',h1+'px');
			h2 = h1;
		} else {
			kj.delClassName(id,'scroll');
		}
		var top = (h-h2)/2;
		var left = (kj.w()-w)/2;
		kj.set(id , 'style.top',top+'px');
		kj.set(id , 'style.left',left+'px');
		this.select_obj = obj;
		this.select_id = id;
		if(!(id in this.select_list)) {
			var arr = kj.obj(id+' li');
			this.select_list[this.select_list.length] = id;
			kj.handler(arr , 'click' , function() {
				kj.tool.select_obj.innerHTML = this.innerHTML;
				var val = kj.getAttribute(this,'kjdata');
				kj.setAttribute(kj.tool.select_obj , 'kjdata' , val);
				var arr = kj.obj(kj.tool.select_id+' li');
				kj.delClassName(arr , 'xsel');
				kj.addClassName(this,'xsel');
				kj.hide(kj.tool.select_id);
				kj.remove("#id_bglayer");
			});
		}
	}
}