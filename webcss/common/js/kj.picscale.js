kj.picscale = new function() {
	this.scale = 2;
	this.maxleft = 0;
	this.maxtop = 0;
	this.minleft = 0;
	this.mintop = 0;
	this.opic = {};
	this.load = function(opic) {
		var offset = kj.offset(opic);
		var obj_div=document.createElement("div");
		obj_div.id = 'id_gpicscale';
		obj_div.style.cssText = 'position:absolute;border:1px #ccc solid;width:' + offset.width + 'px;height:' + offset.height + 'px;overflow:hidden;display:none';
		obj_div.innerHTML = '<img src="" id="id_gpicsrc" style="position:relative;top:0px;left:0px">';
		document.body.appendChild(obj_div);
		obj_div=document.createElement("div");
		obj_div.id = 'id_gpicdot';
		var w = kj.toint(offset.width/this.scale);
		var h = kj.toint(offset.height/this.scale);
		obj_div.style.cssText = 'width:' + w + 'px;height:' + h + 'px;position:absolute;border:1px #ccc solid;background:url(' + kj.cfg("basecss") + '/common/images/pic-dot.png);display:none';
		document.body.appendChild(obj_div);
		kj.handler(opic,"mouseover",function(e){
			kj.picscale.setgpic(e , this.src);
		});
		kj.handler("#id_gpicdot" ,"mousemove",function(e){
			kj.picscale.picover(e);
		});
		kj.handler("#id_gpicdot" ,"mouseout",function(e){
			kj.hide("#id_gpicdot");
			kj.hide("#id_gpicscale");
		});
		this.opic = opic;

	}
	this.picover = function(event) {
		var e = this.getevt(event);
		var offset = kj.offset("#id_gpicdot");
		var top = e.y-offset.height/2;
		var left = e.x-offset.width/2;
		if(left > this.maxleft) left = this.maxleft;
		if(top > this.maxtop) top = this.maxtop;
		if(left<this.minleft) left = this.minleft;
		if(top<this.mintop) top = this.mintop;
		kj.set("#id_gpicdot" , "style.left" , left+'px');
		kj.set("#id_gpicdot" , "style.top" , top+'px');
		var top2 = -1 * this.scale * (top - this.mintop);
		var left2 =  -1 * this.scale * (left - this.minleft);
		kj.set("#id_gpicsrc" , "style.left" , left2+'px');
		kj.set("#id_gpicsrc" , "style.top" , top2+'px');
	}
	this.getevt = function(evt) {
		evt = evt || window.event;
		if(evt.pageX || evt.pageY){  
			return { x : evt.pageX,y : evt.pageY}     
		}
		var top = document.documentElement.scrollTop || document.body.scrollTop;
		var left = document.documentElement.scrollLeft || document.body.scrollLeft;
		return {  
			x : evt.clientX + left - document.body.clientLeft,  
			y : evt.clientY + top - document.body.clientTop  
		}
	}
	this.setgpic = function(event , url) {
		if(kj.obj("#id_gpicdot").style.display=='') return;
		kj.show("#id_gpicdot");
		kj.obj("#id_gpicsrc").src = url;
		var e = this.getevt(event);
		var offset = kj.offset("#id_gpicdot");
		var top = e.y-offset.height/2;
		var left = e.x-offset.width/2;
		var offset2 = kj.offset(this.opic);
		var maxtop = offset2.top+offset2.height-offset.height;
		var maxleft = offset2.left+offset2.width-offset.width;
		if(left > maxleft) left = maxleft;
		if(top > maxtop) top = maxtop;
		if(left<offset2.left) left = offset2.left;
		if(top<offset2.top) top = offset2.top;
		kj.set("#id_gpicdot" , "style.left" , left+'px');
		kj.set("#id_gpicdot" , "style.top" , top+'px');
		this.maxleft = maxleft;
		this.maxtop = maxtop;
		this.minleft = offset2.left;
		this.mintop = offset2.top;
		kj.set("#id_gpicscale" , "style.left" , offset2.left+offset2.width+10+'px');
		kj.set("#id_gpicscale" , "style.top" , offset2.top+'px');
		kj.show("#id_gpicscale");
		kj.set("#id_gpicsrc" , "style.width" , offset2.width*this.scale+'px');
		kj.set("#id_gpicsrc" , "style.height" , offset2.height*this.scale+'px');
	}
}