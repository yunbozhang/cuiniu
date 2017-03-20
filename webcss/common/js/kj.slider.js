kj.slider = new function() {
	this.objs = [];
	this.indexs = [];
	this.timer = 10;
	this.objtime = [];
	this.init = function() {
		var arr = kj.obj(".kj_slider .xli");
		var i = 0,w,arrx,obj_li;
		var arr_btn = kj.obj(".Tslider_btn");
		this.objs = arr;
		for( i = 0 ; i < arr.length ; i++ ) {
			w = kj.w(arr[i]);
			arrx = kj.obj("li" , arr[i]);
		    obj_li=document.createElement("li");
			obj_li.innerHTML = arrx[0].innerHTML;
			arr[i].appendChild(obj_li);
		    obj_li=document.createElement("li");
			obj_li.innerHTML = arrx[arrx.length-2].innerHTML;
			kj.insert_before(arr[i],arrx[0],obj_li);
			kj.set(kj.obj("li",arr[i]),'style.width',w+'px');
			kj.set(arr[i],'style.width',(w*arrx.length+w+w)+'px');
			kj.set(arr[i],'style.marginLeft',-w+'px');

			this.indexs[i] = 1;
			this.objs[i] = arr[i];
			this.on_timer(i);
			if(arr_btn && arr_btn.length>i) {
				kj.show(arr_btn[i]);
				w = (w-kj.w(arr_btn[i]))/2;
				kj.set(arr_btn[i] , 'style.marginLeft' , w + 'px');
				arrx = kj.obj("a" , arr_btn[i]);
				if(arrx && arrx.length>0) kj.addClassName(arrx[0],'xsel');
			}
		}
		kj.handler(".kj_slider" , "mouseover" , function() {
			var arr = kj.obj(".kj_slider");
			var ii = kj.index(arr , this);
			if(kj.slider.objtime[ii]) clearTimeout(kj.slider.objtime[ii]);
			kj.show(kj.obj(".xbtn1",this));
		});
		kj.handler(".kj_slider" , "mouseout" , function() {
			var arr = kj.obj(".kj_slider");
			var ii = kj.index(arr , this);
			kj.slider.on_timer(ii);
			kj.hide(kj.obj(".xbtn1",this));
		});
		kj.handler(".Tslider_btn a" , "mouseover" , function() {
			var arr = kj.obj(".Tslider_btn");
			var objp = kj.parent(this,'.Tslider_btn');
			var ii = kj.index(arr , objp);
			var arrx = kj.obj("a" , objp);
			var jj = kj.index(arrx , this)+1;
			kj.slider.go(ii,jj);
		});
	}
	this.go = function(i , j) {
		if(i>this.objs.length) return;
		var obj = this.objs[i];
		var objp = kj.parent(obj , '.kj_slider');
		var w = kj.w(objp);
		var ii = this.indexs[i];
		var mleft1 = ii * w * -1;
		var mleft2 = j * w * -1;
		var step = w/this.timer;
		var x = w/this.timer;
		this.indexs[i]=j;
		this.move(i, mleft1 , mleft2 , x);
	}
	this.pagenext = function(obj) {
		var arr = kj.obj(".kj_slider");
		var objp = kj.parent(obj , '.kj_slider');
		var index = kj.index(arr , objp);
		this.go_pagenext(index);
	}
	this.pagepre = function(obj) {
		var arr = kj.obj(".kj_slider");
		var objp = kj.parent(obj , '.kj_slider');
		var index = kj.index(arr , objp);
		this.go_pagepre(index);
	}
	this.go_pagenext = function(i) {
		if(i>this.objs.length) return;
		var obj = this.objs[i];
		var objp = kj.parent(obj , '.kj_slider');
		var w = kj.w(objp);
		var arr = kj.obj("li" , obj);
		var ii = this.indexs[i];
		var mleft1 = kj.toint(obj.style.marginLeft);//ii * w * -1;
		var mleft2 = (ii+1) * w * -1;
		var step = w/this.timer;
		var x = w/this.timer;
		this.indexs[i]=ii+1;
		this.move(i, mleft1 , mleft2 , x);
	}
	this.go_pagepre = function(i) {
		if(i>this.objs.length) return;
		var obj = this.objs[i];
		var objp = kj.parent(obj , '.kj_slider');
		var w = kj.w(objp);
		var arr = kj.obj("li" , obj);
		var ii = this.indexs[i];
		var mleft1 = kj.toint(obj.style.marginLeft);//ii * w * -1;
		var mleft2 = (ii-1) * w * -1;
		var step = w/this.timer;
		var x = w/this.timer;
		this.indexs[i]=ii-1;
		this.move(i, mleft1 , mleft2 , x);
	}

	this.move = function(i , mleft1 , mleft2 , len) {
		if(i>this.objs.length) return;
		var istimer = true;
		if(mleft1>mleft2) {
			mleft1 = mleft1-len;
			if(mleft1<=mleft2) {
				mleft1 = mleft2;
				istimer = false;
			}
		} else {
			mleft1 = mleft1+len;
			if(mleft1>=mleft2) {
				mleft1 = mleft2;
				istimer = false;
			}
		}
		kj.set(this.objs[i],'style.marginLeft',mleft1+'px');
		if(istimer) {
			setTimeout("kj.slider.move("+i+","+mleft1+","+mleft2+","+len+")",20);
		} else {
			var obj = this.objs[i];
			var arr = kj.obj("li" , obj);
			var objp = kj.parent(obj , '.kj_slider');
			var w = kj.w(objp);
			if(this.indexs[i]==0) {
				mleft1 = (arr.length-2)*w*-1;
				kj.set(this.objs[i],'style.marginLeft',mleft1+'px');
				this.indexs[i] = arr.length-2;
			} else if(this.indexs[i]==arr.length-1) {
				mleft1 = w*-1;
				kj.set(this.objs[i],'style.marginLeft',mleft1+'px');
				this.indexs[i] = 1;
			}
			var arrbtn = kj.obj(".Tslider_btn");
			if(arrbtn && arrbtn.length>0) {
				arr = kj.obj("a" , arrbtn[i]);
				var ii = this.indexs[i]-1;
				kj.delClassName(arr,'xsel');
				if(arr[ii]) kj.addClassName(arr[ii],'xsel');
			}
			this.on_timer(i);
		}
	}
	this.on_timer = function(i,t) {
		if(!t) t = 3000;
		if(this.objtime[i]) clearTimeout(this.objtime[i]);
		this.objtime[i] = setTimeout("kj.slider.go_pagenext("+i+")",t);
	}
}
kj.onload(function(){
	kj.slider.init();

	if(kj.cfg('iswap')=='1') {
		kj.handler(".kj_slider .xli" , "touchstart",function(e){
			var touch = event.targetTouches[0];
			kj.slider.mstartX = touch.pageX;
		});
		kj.handler(".kj_slider .xli" , "touchmove",function(e){
			 // 如果这个元素的位置内只有一个手指的话
			if (event.targetTouches.length == 1) {
		　　　　event.preventDefault();// 阻止浏览器默认事件，重要 
				var touch = event.targetTouches[0];
				// 把元素放在手指所在的位置
				var mheight = (kj.slider.movex) ? kj.slider.movex : 0;
				kj.slider.movex = touch.pageX;
				if(mheight == 0) return;
				var len = touch.pageX-mheight;
				var top = kj.toint(kj.obj(this).style.marginLeft);
				top = top+len;
				kj.slider.mlen = touch.pageX-kj.slider.mstartX;
				kj.set(this , "style.marginLeft" , top+'px');
			}
		});

		kj.handler(".kj_slider .xli" , 'touchend' , function(event) {
				if(kj.slider.mlen>0 && kj.slider.mlen>50) {
					kj.slider.pagepre(this);
				} else if(kj.slider.mlen<0 && kj.slider.mlen<50) {
					kj.slider.pagenext(this);
				} else {
					return;
				}
				kj.slider.mstartX = 0;
				kj.slider.mlen = 0;
		});
	}

});