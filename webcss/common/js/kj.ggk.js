kj.ggk = new function() {
	this.init = function(canva,box) {
		this.canvas = kj.obj(canva);
		this.box = kj.obj(box);
		this.ctx = this.canvas.getContext('2d');
		this.ctx.fillStyle='#A9AAA7';	//设置覆盖层的颜色
		this.ctx.fillRect(0,0,204,104);	//设置覆盖的区域
		//给整个页面绑定鼠标事件
		var agent = kj.cfg('agent');
		if(agent == '') {
			//给整个页面绑定鼠标事件
			kj.handler("body" , "mousedown",function(){
				//点点击鼠标时即调用一次鼠标移动事件
				kj.ggk.mouseMove();
				//当鼠标按下时激活鼠标移动监听
				document.onmousemove = kj.ggk.mouseMove; 
			});
			kj.handler("body" , "mouseup",function(){
				//当鼠标按键弹起时取消鼠标移动监听
				document.onmousemove = null; 
			});

		} else {
			kj.handler("body" , "touchmove",function(){
				if (event.targetTouches.length == 1) {
					var touch = event.targetTouches[0];
					var offset = kj.offset(kj.ggk.box);
					var x = touch.pageX-offset.left;
					var y = touch.pageY-offset.top;
					if(x>0 && x<offset.width && y>0 && y<offset.height) {
						event.preventDefault();// 阻止浏览器默认事件，重要 
						kj.ggk.touchmove(x,y);
					}
			   }
			});
		}
	}
	this.mouseMove = function(ev)  { 
		ev= ev || window.event; 
		var mouse = kj.ggk.mouseCoords(ev); //获取全局坐标
		var offset = kj.offset(kj.ggk.box);
		var x = mouse.x-offset.left;
		var y = mouse.y-offset.top;
		var r=10;	//半径
		for(var i=0;i<=r;i++){
			//mouse.x++;
			for(var j=0;j<=r;j++){
				var result1 = r*r - (i*i+j*j);
				//document.getElementById("show2").value =result1; 
				if(result1>=0){
					kj.ggk.ctx.clearRect(x+i-9,y+j,1,1);
					kj.ggk.ctx.clearRect(x-i-9,y+j,1,1);
					kj.ggk.ctx.clearRect(x-i-9,y-j,1,1);
					kj.ggk.ctx.clearRect(x+i-9,y-j,1,1);
				}
			}
		}
	} 

	this.touchmove = function (x,y)  { 
		var r=10;	//半径
		for(var i=0;i<=r;i++){
			//mouse.x++;
			for(var j=0;j<=r;j++){
				var result1 = r*r - (i*i+j*j);
				if(result1>=0){
					this.ctx.clearRect(x+i-9,y+j,1,1);
					this.ctx.clearRect(x-i-9,y+j,1,1);
					this.ctx.clearRect(x-i-9,y-j,1,1);
					this.ctx.clearRect(x+i-9,y-j,1,1);
				}
			}
		}
	} 
	/*清除canvas上指定坐标的像素*/
	this.clear = function (x,y){
		this.ctx.clearRect(x,y,1,1);
	}

	/*获取鼠标当前坐标 --相对于页面*/
	this.mouseCoords = function(ev) { 
		if(ev.pageX || ev.pageY){ 
			return {x:ev.pageX, y:ev.pageY}; 
		} 
		return { 
			x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, 
			y:ev.clientY + document.body.scrollTop - document.body.clientTop 
		};
	} 
}