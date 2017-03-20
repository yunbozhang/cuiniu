//处理下拉
kj.handler(".Txiala","mouseover",function(e){
	jsxiala.show(this);
	kj.set(kj.obj("em" , this) , "className" , "xdown");
});
kj.handler(".Txiala","mouseout",function(e){
	jsxiala.close(this);
	kj.set(kj.obj("em" , this) , "className" , "xup");
});

kj.handler(".Txialaval","mouseover",function(e){
	var objp = this.parentNode;
	kj.addClassName(kj.obj('.Txiala',objp,true),'Thover');
	kj.show(this);
	kj.set(kj.obj("em" , objp) , "className" , "xdown");
});
kj.handler(".Txialaval","mouseout",function(e){
	var objp = this.parentNode;
	kj.delClassName(kj.obj('.Txiala',objp,true),'Thover');
	kj.hide(this);
	kj.set(kj.obj("em" , objp) , "className" , "xup");
});


var jsxiala = new function() {
	this.show = function(msgobj) {
		msgobj = kj.obj(msgobj);
		var objp = msgobj.parentNode;
		kj.addClassName(msgobj,'Thover');
		var arr_obj = kj.obj(".Txialaval" , objp);
		if(!arr_obj || !('length' in arr_obj) || arr_obj.length==0) return;
		var obj = arr_obj[0];
		if(obj.style.display != 'none') return;
		var offset = kj.offset(msgobj);
		var objdata = kj.getAttribute(msgobj,'xialadata');
		objdata = (objdata)? objdata = kj.json(objdata) : {};
		var align = left;
		if('align' in objdata) align = objdata.align;
		kj.show(obj);
		var position = '';
		var offset2 , left , top;
		offset2 = kj.offset(obj);
		left = offset.left;
		top = offset.top;
		if(!('valign' in objdata) || objdata.valign == "down") top += offset.height
		if('parent' in objdata) {
			var obj_p = kj.obj(objdata.parent);
			if(obj_p) {
				var offset_p = kj.offset(obj_p);
				top = top - offset_p.top;
				left = left - offset_p.left;
				alert(left);
				var arr_sp = kj.obj(".xsp" , obj);
				var top2 = top + offset.height;
				if(offset2.height < top2) {
					top2 = top2 - offset2.height;
					top = top - top2;
					kj.set(arr_sp[0] , "style.top" , top + 'px');
					top = top2;
				} else {
					kj.set(arr_sp[0] , "style.top" , top + 'px');
					top = 0;
				}
			}
		}
		if('left' in objdata) left += objdata.left;
		if(align == 'right') left += offset.width-offset2.width;
		kj.set(obj,'style.left',left+'px');
		if('top' in objdata) top += objdata.top;
		kj.set(obj,'style.top',top+'px');
	}
	this.close = function(msgobj) {
		msgobj = kj.obj(msgobj);
		var objp = msgobj.parentNode;
		kj.delClassName(msgobj,'Thover');
		var arr_obj = kj.obj(".Txialaval" , objp);
		if(!arr_obj || !('length' in arr_obj) || arr_obj.length==0) return;
		kj.hide(arr_obj[0]);
	}
}