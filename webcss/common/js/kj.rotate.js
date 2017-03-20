kj.rotate = new function() {
	//格式：this.data = [{'name':'一等奖','rate':30,'id':1,'pic':''},{'name':'二等奖','rate':70,'id':1,'pic':''}];
	this.data = []
	this.color=[];
	this.step = 2*Math.PI/100;
	this.outerR = 0; //轮盘的大小
	this.interR = 50;//内存空白圆的大小
	this.beginAngle=50;//旋转起来时默认开始旋转的度数，度数愈大旋转的初始速度愈大
	this.radio = 0.95;//旋转速度衰减系数，影响旋转时间
	this.t = null;
	this.ii = 0;
	this.i = 0;
	this.load = function(obj_canvas,obj_pic) {
		this.obj_canvas = kj.obj(obj_canvas);
		var w = kj.w(this.obj_canvas)/2;
		this.outerR = w;
		this.obj_pic = kj.obj(obj_pic);
		this.setpic();
		for ( var i = 0; i < this.data.length; i++) {
			 this.color.push(this.getColor());
		}
		var context = this.obj_canvas.getContext("2d");
		context.translate(w,w);
		for(i = 0 ; i < this.data.length ; i++) {
			this.data[i].rate = kj.toint(this.data[i].rate);
		}
		//createArrow(context);
		this.init(context);
	};
	this.setpic = function() {
		var offset = kj.offset(this.obj_canvas);
		var top = offset.top+offset.height/2-kj.h(this.obj_pic)/2;
		var left = offset.left+offset.width/2-kj.w(this.obj_pic)/2;
		kj.set(this.obj_pic,'style.left',left+'px');
		kj.set(this.obj_pic,'style.top',top+'px');
	}
	this.init = function (context) {
		  var x = 0;
		  for ( var i = 0; i < this.data.length; i++) {
				 context.save();
				 context.beginPath();
				 context.moveTo(0,0);
				 context.fillStyle=this.color[i];
				 if( i > 0 ) x += this.data[i-1].rate*this.step;
				 context.arc(0,0,this.outerR,x,x + this.data[i].rate*this.step);
				 context.fill();
				 context.restore();
		  }
		 
		  context.save();
		  context.beginPath();
		  context.fillStyle="#fff";
		  context.arc(0,0,this.interR,0,2*Math.PI);
		  context.fill();
		  context.restore();
		  x = 0;
		  for ( var i = 0; i < this.data.length; i++) {
				 context.save();
				 context.beginPath();
				 context.fillStyle="#000";
				 context.font="15px 微软雅黑";
				 context.textAlign="center";
				 context.fillStyle="#fff";
				 context.textBaseline="middle";
				 if( i > 0 ) x += this.data[i-1].rate*this.step;
				 context.rotate( x + this.data[i].rate*this.step/2 );
				 context.fillText(this.data[i].name,(this.outerR + this.interR)/2,0);
				 context.restore();
		  }
	}
	this.getColor = function() {
		  var random = function(){
				 return Math.floor(Math.random()*255);
		  }
		  return "rgb("+random()+","+random()+","+random()+")";
	}
	this.start = function(id) {
		this.ii = 0;
		this.i = 0;
		var val = -1;
		for(var i = 0 ; i < this.data.length ; i++) {
			if(this.data[i].id == id) val = i;
		}
		if(val < 0) {
			alert("抽奖数据不存在");
			return;
		}
		this.award_rotate = this.get_award(val);
		this.go();
	}
	this.go = function() {
		this.obj_pic.style.webkitTransform = "rotate(" + this.ii + "deg)";
		if(this.i<7 || this.ii != this.award_rotate) {
			if(this.i<3) {
				this.ii+=15;
			} else if(this.i<6) {
				this.ii+=10;
			} else if(this.i<7) {
				this.ii+=5;
			} else {
				this.ii+=1;
			}
			if(this.ii>360) {
				this.ii = 0;
				this.i++;
			}
			var t = 1;
			setTimeout("kj.rotate.go();" , t);
		} else {
			
		}
	}
	this.get_award = function(val) {
		this.award = val;
		var rate = 0;
		for(i = 0 ; i < val ; i++) {
			rate += this.data[i].rate
		}
		rate = rate/100 * 360 + 269;
		var x = this.data[val].rate/100 * 360;
		var val2 = this.rand(1,x);
		rate = rate+val2;
		if(rate>360) rate = rate - 360;
		if(rate>360) rate = rate - 360;
		return parseInt(rate);
	}
	this.rand = function (under, over){ 
		switch(arguments.length){ 
			case 1: return parseInt(Math.random()*under+1); 
			case 2: return parseInt(Math.random()*(over-under+1) + under); 
			default: return 0; 
		}
	}
}
kj.onresize(function() {
	kj.rotate.setpic();
});
