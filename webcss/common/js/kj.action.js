/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 * 增加 dialog 相关功能
 */
kj.action = new function(){
	this.size = function(id , tow , toh , num , time , func , fx,num2) {
			var offset = kj.offset(id);
			var w = offset.width-tow;
			var h = offset.height-toh;
			var left=0,top=0;
			var istimte = false;
			if(offset.width>tow) {
				w = offset.width-num;
				if(fx) left = num;
				if(w<=tow) {
					w = tow;
					if(fx) left = offset.width-tow;
				} else {
					istimte = true;
				}
			} else {
				w = offset.width+num;
				if(fx) left = -num;
				if(w>=tow) {
					w = tow;
					if(fx) left = offset.width-tow;
				} else {
					istimte = true;
				}
			}
			var numx = (num2) ? num2 : num;
			if(offset.height>toh) {
				h = offset.height-numx;
				if(fx) top = numx;
				if(h<=toh) {
					h = toh;
					if(fx) top = offset.height-toh;
				} else {
					istimte = true;
				}
			} else {
				h = offset.height+numx;
				if(fx) top = -numx;
				if(h>=toh) {
					h = toh;
					if(fx) top = offset.height-toh;
				} else {
					istimte = true;
				}
			}
			kj.set(id , "style.width" , w+'px');
			kj.set(id , "style.height" , h+'px');
			if(left!=0) {
				kj.set(id , "style.left" , (offset.left+left)+'px');
			}
			if(top!=0) {
				kj.set(id , "style.top" , (offset.top+top)+'px');
			}
			if(istimte) {
				if(!func) func = '0';
				if(!fx) fx = '0';
				if(!num2) num2 = '0';
				setTimeout("kj.action.size('"+id+"',"+tow+","+toh+","+num+","+time+","+func+","+fx+","+num2+")",time);
			} else {
				if( typeof(func) == 'function' ) kj.call(func);
			}
	}
}