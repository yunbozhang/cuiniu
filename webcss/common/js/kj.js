/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
/*
kj 全局对象
*/
//增加系统对象相关属性
String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
} 
String.prototype.ltrim = function() {
	return this.replace(/(^\s*)/g, "");
}   
String.prototype.rtrim = function() {
	return this.replace(/(\s*$)/g, "");
}
Array.prototype.indexOf = function(key) {
	for(var i = 0 ; i < this.length ; i ++) {
		if(this[i] == key) return i;
	}
	return -1;
}
Array.prototype.remove = function(key) {
	for(var i = 0 ; i < this.length ; i ++) {
		if(this[i] == key) {
			this.splice(i,1);
			i--;
		}
	}
}
Array.prototype.removeat = function(index) {
	this.splice(index,1);
}

document.getElementsByClassName=function(strClassName , strTagName, oElm ){ 
	if(!strTagName) strTagName = "*";
	if(!oElm) oElm = document;
    var arrElements = (strTagName == "*" && oElm.all)? oElm.all : 
        oElm.getElementsByTagName(strTagName); 
    var arrReturnElements = new Array(); 
    strClassName = strClassName.replace(/\-/g, "\\-"); 
    var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)"); 
    var oElement; 
    for(var i=0; i < arrElements.length; i++){ 
        oElement = arrElements[i]; 
        if(oRegExp.test(oElement.className)){ 
            arrReturnElements.push(oElement); 
        } 
    }
    return arrReturnElements;
}

var kj = new function(){
	this.platform = '';//客户端平台
	this.x = 0;
	this.y = 0;
	this.data = [];//缓存数据
	//重写加载事件
	this.onload = function(func) {
		var oldonload = window.onload;
		if(typeof window.onload != 'function') {
			window.onload = func;
		}else{
			window.onload=function() {
				oldonload();
				func();
			}
		}
	}
	//重写加载事件
	this.onresize = function(func) {
		var oldonresize = window.onresize;
		if(typeof window.onresize != 'function') {
			window.onresize = func;
		}else{
			window.onresize=function() {
				oldonresize();
				func();
			}
		}
	}
	//触发事件
	this.event = function(o,e){
		var obj = this.obj(o);
		if( !obj ) return;
		if(document.all) {
			//obj.click();
			var fun='kj.obj(o).'+e+'()';
			eval(fun);
		} else {
			var evt = document.createEvent("HTMLEvents");
			evt.initEvent(e,true,true);
			obj.dispatchEvent(evt);
		}  
	}
	//取事件发生对象
	this.event_target = function(e) {
		e = e || window.event;
		return e.target || e.srcElement;
	}

	//为事件增加方法
	this.handler = function(o,e,f){
		var obj=this.obj(o);
		//obj=new Array(1,2,3);
		if(!obj) return;
		if('length' in obj) {
			for(var i = 0 ; i < obj.length ; i++){
				this._handler(obj[i],e,f);
			}
		}else{
			this._handler(obj,e,f);
		}
	}
	//事件处理
	this._handler = function(o,e,f){
		if(o.addEventListener){
			o.addEventListener(e,f,false);
		}else if(o.attachEvent){
			o.attachEvent('on'+e,function(){
				return f.call(o,window.event);
			});
		}else{
			o['on'+e]=f;
		}
	}
	//取对象,类似jQuery的选择器
	this.obj = function(o , p , ischild) {
		if( typeof(o) == 'string' ) {
			if( p ) {
				var obj;
				p = this.obj(p);
				if(p && 'length' in p) {
					var objx,j;
					obj = [];
					for(var i = 0 ; i < p.length ; i++ ) {
						objx = this._obj(o , p[i]);
						if('length' in objx) {
							for(j = 0 ; j < objx.length; j++){
								if(!ischild || objx[j].parentNode == p[i]) obj[obj.length] = objx[j];
							}
						} else {
							if(!ischild || objx.parentNode == p[i]) obj[obj.length] = objx;
						}
					}
					o = obj;
				} else {
					o = this._obj(o , p);
					if(ischild) {
						if('length' in o) {
							obj = [];
							for(var i = 0 ; i < o.length; i++ ) {
								if(o[i].parentNode == p) obj[obj.length] = o[i];
							}
							o = obj;
						} else {
							if(o.parentNode != p) return null;
						}
					}
				}
			} else {
				o = this._obj(o);
			}
			return o;
		} else {
			return o;
		}
	}
	//取父级对象
	this.objp = function(o,p) {
		var arr = false;
		var isin;
		o = this.obj(o);
		if('length' in o) {
			arr = new Array();
			for(var i = 0 ; i < o.length ; i++ ) {
				isin = this._objp(o[i] , p);
				if(isin) arr[arr.length] = o[i];
			}
			if(arr.length<1) arr = false;
		} else {
			isin = this._objp(o , p);
			if(isin) arr = o;
		}
		return arr;
	}
	this._objp = function(o,p) {
		if(!o) return false;
		if('parentNode' in o) {
			if( o.parentNode == p) {
				return true;
			} else {
				return this._objp(o.parentNode,p);
			}
		} else {
			return false;
		}
	}
	/**
	 * input<<name,selid[] 表示取name=selid 的input 对象
	 */
	this._obj = function(o , p) {
		var obj;
		var xxx= o;
		o = o.trim();
		var arr_x = o.split(" ");
		var arr = arr_x[0].split("<<");//是否全用正则条件
		var obj_p;
		arr_x[0] = arr[0];
		( p && p != '') ? obj_p = p : obj_p = document;
		switch(arr_x[0].substring(0,1)){
			case "."://className
				obj = document.getElementsByClassName(arr_x[0].substring(1),'',p);
				if(p) obj = this.objp(obj , p);
				break;
			case "#"://id
				obj = obj_p.getElementById(arr_x[0].substring(1));
				break;
			case ":"://name
				obj = document.getElementsByName(arr_x[0].substring(1));
				if(p) obj = this.objp(obj , p);
				break;
			default:
				obj = obj_p.getElementsByTagName(arr_x[0]);
		}
		if(!obj || ('length' in obj && obj.length==0) ) return obj;
		//是否取下级对象
		if( arr_x.length>1) {

			var o_next = o.substring(arr_x[0].length);
			//if( is_array  ){
			if( obj.length  ) {
				var obj_next;
				var obj_new = new Array();
				for(var i=0; i<obj.length; i++) {
					obj_next = this._obj(o_next , obj[i]);
					if( !obj_next || ('length' in obj_next && obj_next.length<1) ) continue;
					if(obj_next.length) {
						for(j=0; j<obj_next.length; j++) {
							obj_new[obj_new.length] = obj_next[j];
						}
					} else {
						obj_new[obj_new.length] = obj_next;
					}
				}
				obj = obj_new;
			} else {
				obj = this._obj(o_next , obj);
			}
		}
		//循环设置对象索引
		if(obj && 'length' in obj) {
			for(var i=0; i < obj.length; i++) {
				obj[i].index = i;
			}
		}
		//是否全用正则条件
		if( arr.length>1 ) {
			var t1,t2,p,val;
			arr = arr[1].split(",");
			t1 = arr[0];
			arr.remove(t1);
			t2 = arr.join(",");
			p = t1.split(".");
			if('length' in obj) {
			    obj_new = new Array();
				for(var i=0 ; i < obj.length ; i++) {
					if( t1 in obj[i] ) {
						(p.length > 1) ? val = obj[i][p[0]][p[1]] : val = obj[i][t1];
						if( this.reg_test(t2,val) ){
							obj_new[obj_new.length] = obj[i];
						}
					}
				}
				obj = obj_new;
			} else {
				if( t1 in obj ) {
					(p.length > 1) ? val = obj[p[0]][p[1]] : val = obj[t1];
					if( !this.reg_test(t2,val) ){
						obj = null;
					}
				} else {
						obj = null;
				}
			}
		}
		return obj;
	}
	this.index = function(o , o2 , again) {
		var arr = kj.obj(o);
		if('length' in arr) {
			o2=kj.obj(o2);
			if ('index' in o2 && !again ) return o2.index;
			for( var i = 0 ; i < arr.length ; i++) {
				if(o2 == arr[i]) return i;
			}
			return -1;
		} else {
			return -1;
		}
	}
	this.parent = function(o , tag) {
		var obj,objx;
		o = this.obj(o);
		if( o && 'length' in o ) {
			obj = new Array();
			for(var i = 0 ; i < o.length ; i++ ) {
				objx = this._parent( o[i] , tag );
				if( objx != '' ) obj[obj.length] = objx;
			}
		} else if(o) {
			objx = this._parent( o , tag );
			if( objx != '' ) obj = objx;
		}
		return obj;
	}
	this._parent = function( o , tag) {
		if(typeof o != 'object') return '';
		if('parentNode' in o && o.parentNode) {
			var x = tag.substr(0,1);
			if(x == '.' && 'className' in o.parentNode) {
				var arr = o.parentNode.className.split(" ");
				x = tag.substring(1);
				if(arr.indexOf(x)>=0) return o.parentNode;
			}
			if(x == '#' && 'id' in o.parentNode) {
				x = tag.substring(1);
				if(o.parentNode.id == x) return o.parentNode;
			}
			if( 'tagName' in o.parentNode && o.parentNode.tagName.toLowerCase() == tag.toLowerCase() ) {
				return o.parentNode;
			} else {
				return this._parent( o.parentNode , tag );
			}
		} else {
			return '';
		}
	}
	this.reg_test = function(reg,val) {
		if(reg.substr(0,1) == '/') {
			eval("reg="+reg);
			if(reg.test(val)) return true;
		} else {
			if(reg == val) return true;
		}
		return false;
	}
	//取高度
	this.h = function (obj , val) {
		if(val){
			if( isNaN(val) ) val=val.replace(/px/i, "");
			this.set(obj , 'style.height' , val + "px");
		} else {
			var h=0;
			if(obj){
				obj = this.obj(obj);
				if(!obj) return 0;
				if(obj.offsetHeight){
					h = obj.offsetHeight;
				} else if(obj.scrollHeight) {
					h = obj.scrollHeight;
				}else if(obj.height){
					h = obj.height;
				}
			} else {
				h = document.body.scrollHeight;
				if(document.documentElement.scrollHeight && (!h || document.documentElement.scrollHeight > h)){
					h = document.documentElement.scrollHeight;
				}
			}
			return this.toint(h);
		}
	}
	//取对象高度
	this.w = function (obj , val){
		if(val || !isNaN(val)){
			if( isNaN(val) ) val=kj.toint(val.replace(/px/i, ""));
			if(val<0) val=0;
			this.set(obj , 'style.width' , val + "px");
		} else {
			var w,pleft,pright;
			if(obj){
				obj = this.obj(obj);
				if(!obj) return 0;
				var reg=/^\d{1,}px$/i;
				if('offsetWidth' in obj) {
					w = obj.offsetWidth;
				} else if('scrollWidth' in obj){
					w = obj.scrollWidth;
				} else if('style.width' in obj) {
					w = obj.style.width;
				} else if('width' in obj) {
					w = obj.width;
				} else {
					return 0;
				}
				if( reg.test(w) ) {
					w = this.toint(w.replace(/px/i,''));
				}
				pleft = this.toint(obj.style.paddingLeft.replace(/px/i,''));
				pright = this.toint(obj.style.paddingRight.replace(/px/i,''));
				w = w-pleft-pright;
			}else{
				w = document.body.scrollWidth;
				if(document.documentElement.scrollWidth && (!w || document.documentElement.scrollWidth > w)){
					w = document.documentElement.scrollWidth;
				}
				//w = w-pleft-pright;
			}
			w = this.toint(w) ;
			return w;
		}
	}
	this.get = function( obj , property) {
		obj = this.obj(obj);
		if(!obj) return;
		var val='';
		exe = "val = obj." + property + ";";
		eval(exe);
		return val;
	}
	this.set = function(obj,property,val) {
		obj = this.obj(obj);
		if(!obj || obj=='') return;
		var p = property.split(".");
		var exe;

		if(typeof val == 'string') val = "'" + val.replace("'","\'") + "'";
		if('length' in obj && obj[0]!=null) {
			for(var i=0 ; i < obj.length ; i++) {
				(p.length > 1) ? exe = "obj["+i+"]." + property + " = " + val + ";" : exe = "obj["+i+"]." + property + " = " + val + ";";
				if(typeof(obj[i]) == 'object' && 'style' in obj[i]) eval(exe);
			}
		} else if(typeof(obj) == 'object' && 'style' in obj) {
			(p.length > 1) ? exe = "obj." + property + " = " + val + ";" : exe = "obj." + property + " = " + val + ";";
			eval(exe);
		}
	}
	//格式化url , addparam : 追加参数(数组)
	this.urlencode = function(url , addparam) {
		var i;
		var arr_url = [];
		var arr_key = [];
		var reg = /^[a-z0-9%]+$/i;
		if(addparam) {
			if(typeof(addparam) == 'string') {
				addparam = addparam.split("&");
			}
			for(i = 0 ; i < addparam.length; i++ ) {
				val = addparam[i].split("=");
				key = val[0];
				val.removeat(0);
				if(!reg.test(val)) val = encodeURIComponent(val.join("="));
				addparam[i] = key + "=" + val;
				arr_url[arr_url.length] = addparam[i];
				arr_key[arr_key.length] = key;
			}
		}
		var arr = url.split("?");
		url = arr[0];
		if(arr.length>1) {
			arr.removeat(0);
			var str = arr.join("?");
			arr = str.split("&");
			var key;
			for(i=0 ; i < arr.length ; i++) {
				val = arr[i].split("=");
				key = val[0];
				val.removeat(0);
				if(arr_key.indexOf(key)<0) {
					if(!reg.test(val)) val = encodeURIComponent(val.join("="));
					arr_url[arr_url.length] = key + "=" + val;//encodeURIComponent(val.join("="));
				}
			}
		}
		var str_param = '';
		if( arr_url.length > 0 ) str_param = "?" + arr_url.join("&");
		url += str_param;
		return url;
	}
	//取表单数据
	this.form_to_url = function(o) {
		var allStr="";
		if(o){
			var elementsObj=o.elements;
			var obj;
			if(elementsObj){
				for(var i=0; i<elementsObj.length;i+=1){
					obj=elementsObj[i];
					if(obj.name!=undefined&&obj.name!=""){
						if(obj.type=="radio"){
							if(obj.checked){
								allStr+="&"+obj.name+"="+encodeURIComponent(obj.value);
							}
						}else if(obj.type=="checkbox"){
							if(obj.checked){
								allStr+="&"+obj.name+"="+encodeURIComponent(obj.value);
							}
						}else{
							allStr+="&"+obj.name+"="+encodeURIComponent(obj.value);
						}
					}
				}
			}else{
				return "";
			}
		}else{
			return "";
		}
		return allStr;
	}
	//json数据
	this.json = function(data) {
		data = data.trim();
		if( data.substr(0,1)!="{" && data.substr(0,1)!="[" || data.substr(data.length-1,1)!="}" && data.substr(data.length-1,1)!="]" ){
			var obj_y={"isnull":"1"};
			return obj_y;
		}
		eval("var obj_x="+data);
		return obj_x;
	}
	//取配置文件信息
	this.cfg = function(key) {
		if(typeof web_config == 'object') {
			if(key in web_config) {
				return web_config[key];
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
	//显示元素
	this.show = function(o) {
		this.set(o,'style.display','');
	}
	//隐藏元素
	this.hide = function(o) {
		this.set(o,'style.display','none');
	}
	this.toint = function(o ,tofixed) {
		if(o == null || typeof(o) == 'undefined') return 0;
		if(typeof(o) == 'number') return this.tofloat(o , tofixed);
		var reg=/^[-]{0,1}\d{1,}[.]{0,1}\d*$/;
		if( isNaN(o) ) o = o.replace(/px/i, "");
		if( isNaN(o) ) o = o.replace(/￥/i, "");
		if( isNaN(o) ) o = o.replace(/\$/i, "");
		var coinsign = kj.cfg("coinsign");
		if( isNaN(o) && coinsign) {
			var reg2 = new RegExp(coinsign, "ig");
			o = o.replace(reg2, "");
		}
		if( o == '' ||  !reg.test(o) ){
			return 0;
		}else{
			intv = parseInt(o);
			floatv = parseFloat(o);
			if(intv == floatv) return intv;
			return this.tofloat(floatv , tofixed);
		}
	}
	this.tofloat = function(val , tofixed) {
		if(val == parseInt(val)) return val;
		if(!tofixed) tofixed = 2;
		var x = val + '';
		var arr = x.split(".");
		if(arr[1].length>5) return parseFloat(val.toFixed(tofixed));
		return val;
	}
	this.format_price = function(val) {
		return kj.toint(val);
		var x = val + '';
		var arr = x.split(".");
		if(arr.length>1 && arr[1].length>2) {
			return parseFloat(val.toFixed(2));
		} else if(arr.length<2){
			return val + ".00";
		} else {
			return val;
		}
	}
	this.agent = function(isie) {
			var reg_ie = /msie/i;
			var reg_firefox = /firefox/i;
			var reg_safari = /safari/i;
			var agent = 'other';
			if(reg_ie.test(window.navigator.userAgent)) agent = 'ie';
			if(reg_firefox.test(window.navigator.userAgent)) agent = 'firefox';
			if(reg_safari.test(window.navigator.userAgent)) agent = 'safari';
			if(isie && agent=='ie') {
				var version=navigator.appVersion;
				version=version.split(";");
				if(version.length>1) agent = version[1].replace(/[ ]/g,"");
			}
			return agent;
	}
	this.remove = function(o) {
		var obj = kj.obj(o);
		if(!obj) return;
		if('length' in obj && !('parentNode' in obj) ) {
			var len = obj.length;
			for(var i = len; i >= 0 ; i--) {
				this.remove(obj[i]);
			}
		} else {
			('parentNode' in obj) ? obj.parentNode.removeChild(obj) : document.body.removeChild(obj);
		}
	}
	this.left = function(obj,left) {
		if(typeof left == 'number' && !isNaN(left)){
			lng_left=left;
		}else{
			var w1=kj.w();
			var w2 = kj.w();
			if(typeof obj == 'number' && !isNaN(obj) ) {
				w2 = obj ;
			} else if( obj ) {
				w2 = kj.w(obj);
			}
			lng_left=(w1-w2)/2;
		}
		return lng_left;
	}
	this.top = function(o,top) {
		if(typeof top == 'number' && !isNaN(top)){
			lng_top=top;
		}else{
			var h=0;
			if(typeof o == 'number' && !isNaN(o) ) {
				h = o ;
			} else if( o ) {
				h = kj.obj(o).offsetHeight;
			}
			if(document.documentElement.clientHeight>=h){
				lng_top=(document.documentElement.clientHeight-h)/2;
			}else{
				lng_top=0;
			}
		}
		return lng_top;
	}
	this.delClassName = function(o,css_name) {
		var o = this.obj(o);
		if(!o) return;
		if( !('length' in o) ) {
			this._delClassName(o,css_name);
			return;
		}
		for(var i = 0 ; i < o.length; i++) {
			this._delClassName(o[i],css_name);
		}
	}
	this._delClassName = function(o,css_name) {
		if( !('className' in o) ) return;
		var arr_name = css_name.replace(/[\s]{2,}/,' ').split(' ');
		if(arr_name.length>1) {
			for(var i = 0 ; i < arr_name.length ; i++) {
				this._delClassName(o,arr_name[i]);
			}
		} else {
			arr = o.className.replace(/[\s]{2,}/,' ').split(' ');
			arr.remove(arr_name[0]);
			o.className = arr.join(' ');
		}
	}
	this.addClassName = function(o,css_name) {
		var o = this.obj(o);
		if(!o) return;
		if( !('length' in o) ) {
			this._addClassName(o,css_name);
			return;
		}
		for(var i = 0 ; i < o.length; i++) {
			this._addClassName(o[i],css_name);
		}
	}
	this._addClassName = function(o,css_name) {
		if( !('className' in o) ) return;
		arr = o.className.replace(/[\s]{2,}/,' ').split(' ');
		arr.remove(css_name);
		arr[arr.length] = css_name;
		o.className = arr.join(' ');
	}
	this.insert_after = function(o1 , o2 , o3){
		o1 = this.obj(o1);
		o2 = this.obj(o2);
		o3 = this.obj(o3);
		if('nextSibling' in o2 && o2.nextSibling) {
			o1.insertBefore(o3 , o2.nextSibling );
		} else {
			o1.appendChild(o3);
		}
	}
	this.insert_before = function(o1 , o2 , o3){
		o1 = this.obj(o1);
		o2 = this.obj(o2);
		o3 = this.obj(o3);
		if(o2) {
			o1.insertBefore(o3 , o2 );
		} else {
			o1.appendChild(o3);
		}
	}
	/* 轮转显示对象，属性值
	 * o 对象，可以为数组，style 前面对象指定的值，v 轮转变化的值
	 */
	this.trun = function (o , style , v ) {
		o = this.obj(o);
		if(!o) return;
		var val='';
		if('length' in o) {
			//数组模式
			for(var i=0 ; i < style.length ; i++) {
				this.trun(o[i] , style , v);
			}
		} else {
			val = this.get(o , style);
			for( i = 0 ; i < v.length; i++ ) {
				if(val == v[i]) {
					index = i+1;
				}
			}
			if(index >= v.length) index = 0;
			this.set(o , style , v[index] ) ;
		}
	}
	//设置透明度
	this.opacity = function(o , v) {
		o = kj.obj(o);
		var ie = kj.agent(true);
		ie = kj.toint(ie.replace("MSIE" , ""));
		if(kj.agent() == 'ie' && ie < 10 && o.filters.alpha) {
			o.filters.alpha.opacity = v;
		} else {
			v = v/100;
			kj.set(o , "style.opacity" , v);
		}
	}
	/** 定时函数
	 *  func 函数名,t 时间秒,p 前面func所需要参数，可以为多个
	 */
	this.timeout = function(func , t , p) {
		var args = Array.prototype.slice.call(arguments , 2); 
		var _cb;
		if(p) {
			_cb = function(){ func.apply(null,args); }
		} else {
			_cb = func;
		}
		setTimeout( _cb , t); 
	}
	//回调一个函数
	this.call = function( func , p) {
		var args = Array.prototype.slice.call(arguments , 1); 
		func.apply(null,args);
	}
	this.move = function(divObj , targetObj) {
		divObj = kj.obj(divObj);
		if (!divObj) return;
		if(!targetObj) targetObj = divObj;
		divObj.hasDraged = false;
		// 把鼠标的形状改成移动形
		divObj.style.cursor = "move";
		// 定义鼠标按下时的操作
		divObj.onmousedown = function(event) {
			if(kj.agent() == 'ie') event=window.event;
			var str_position = (kj.agent(true) == 'MSIE6.0') ? 'absolute' : 'fixed';
			var ofs = Offset(divObj);
			targetObj.style.position = str_position;//"fixed";
			targetObj.style.left = ofs.l;
			targetObj.style.top = ofs.t;
			targetObj.X = event.clientX - ofs.l;
			targetObj.Y = event.clientY - ofs.t;
			divObj.hasDraged = true;
		};

		// 定义鼠标移动时的操作
		divObj.onmousemove = function(event) {
			if (!divObj.hasDraged) return;
			if(kj.agent() == 'ie'){
				event=window.event;
				divObj.setCapture();
			}
			var lng_x=event.clientX - targetObj.X;
			var lng_y=event.clientY - targetObj.Y;
			targetObj.style.left = lng_x+"px";
			targetObj.style.top = lng_y+"px";
		};
		// 定义鼠标提起时的操作
		divObj.onmouseup = function() {
			divObj.hasDraged = false;
			if(kj.agent() == 'ie'){
				divObj.releaseCapture();
			}
		};
		var arr_btn = kj.obj("li" , divObj);
		for(var i = 0 ; i < arr_btn.length ; i++ ) {
			kj.handler(arr_btn[i],"mousedown",function(event){
				if(kj.agent() == 'ie') {
					event=window.event;
					event.cancelBubble=true;
				} else {
					event.stopPropagation(); 
				}
			});
		}
		function Offset(e) {
			var t = e.offsetTop;
			var l = e.offsetLeft;
			var w = e.offsetWidth;
			var h = e.offsetHeight;
			while(e=e.offsetParent)
			{
				t+=e.offsetTop;
				l+=e.offsetLeft;
			}
			return { t:t, l:l, w:w, h:h }
		};
	};
	this.bglayer = function(id , bgcolor , opacity , json) {
		var obj_div=document.createElement("div");
		var w = kj.w();
		var h = kj.h();
		if(!bgcolor) bgcolor = "#efefef";
		if(!opacity) opacity = 0.6;
		obj_div.id="id_bglayer";
		if(id) obj_div.className = id;
		obj_div.style.cssText="position:absolute;z-index:1;top:0px;left:0px;width:" + w + "px;height:" + h + "px;background:" + bgcolor + ";FILTER:alpha(opacity=" + (opacity*100) + ",);opacity:" + opacity;
		document.body.appendChild(obj_div);
		this.setAttribute("#id_bglayer","kjdata",json);
		if(id) {
			var kjobj = (!json) ? {} : kj.json(json);
			if(!kjobj || !('click' in kjobj) || kjobj.click!='none') {
				this.handler("#id_bglayer","click",function(){
					var kjobj = kj.getAttribute(this,"kjdata");
					if(kjobj && kjobj != '') {
						kjobj = kj.json(kjobj);
					}
					if(kjobj && 'click' in kjobj && kjobj.click=='hide') {
						kj.hide("#"+this.className);
					} else {
						kj.remove("#"+this.className);
					}
					kj.remove(this);
				});
			}
		}

	}
	this.add_option = function(msgobj,msgname,msgval){
		msgobj = this.obj(msgobj);
		if(document.all){
			msgobj.add(new Option(msgname,msgval));
		}else{
			msgobj.add(new Option(msgname,msgval),null);
		}
	}

	this.cookie_set = function(objName,objValue,objHours,msgDomain){//添加cookie
		var cookie_pre = this.cfg("cookie_pre");
		var str = cookie_pre + objName + "=" + encodeURIComponent(objValue);
		if(!objHours) objHours=24;
		var date = new Date();
		var ms = objHours * 3600 * 1000;
		date.setTime(date.getTime() + ms);
		var dirpath = this.cfg("dirpath");
		if(dirpath == '') dirpath = '/';
		var strPath="; path=" + dirpath;
		var strPath2 = strPath;
		var domain = this.cfg("domain").toLowerCase();
		domain = domain.replace('http://','');
		var arr = domain.split(".");
		if(domain != 'localhost' && kj.toint(arr[arr.length-1])<1) {
			var ii = arr.length-1;
			if(ii>=1) domain = arr[ii-1]+"."+arr[ii];
			var arr_ext = Array('com.cn','net.cn','org.cn','gov.cn','com.hk','com.tw','com.au','edu.cn');
			if(arr_ext.indexOf(domain)>=0 && ii>=2) domain = arr[ii-2]+"."+domain;
			var arrx = domain.split(":");
			domain = arrx[0];
			strPath +="; domain=." + domain;
		}
		//处理之前版本
		var date2 =new Date();
		date2.setTime(date2.getTime() - 10000);
		var str2 = str + "; expires=" + date2.toGMTString() + strPath2;
		document.cookie = str2;
		/*结束*/

		str += "; expires=" + date.toGMTString() + strPath;
		document.cookie = str;
	}
	  
	this.cookie_get = function(objName){//获取指定名称的cookie的值
		var cookie_pre = this.cfg("cookie_pre");
		objName = cookie_pre + objName;
		var arrStr = document.cookie.split("; ");
		for(var i = 0;i < arrStr.length;i ++){
			var temp = arrStr[i].split("=");
			if(temp[0] == objName) return decodeURIComponent(temp[1]);
		}
		return '';
	}
	  
	this.cookie_del = function(name){//为了删除指定名称的cookie，可以将其过期时间设定为一个过去的时间
		var date = new Date();
		date.setTime(date.getTime() - 10000);
		document.cookie = name + "=a; expires=" + date.toGMTString();
	}

	//取当前显示url
	this.url_view = function(val) {
		var url = val.toLowerCase();
		if(url.substring(0,7) == "http://") return val;
		var baseurl = this.cfg("dirpath");
		if(baseurl.substring(-1)=="/") baseurl = baseurl.substring(0,-1);
		var str = '';
		if(val.substring(0,1)=="/" || val.substring(0,1)=="\\") {
			if(baseurl == '') return val;
			str = val.substring(0,baseurl.length);
			if(str == baseurl) return val;
			val = val.substring(1);
		} else {
			if(baseurl == '') return "/" + val;
			str = val.substring(0,baseurl.length-1);
			if( ("/"+str) == baseurl) return val;
		}
		val = baseurl + "/" + val;
		return val;
	}
	//复制
	this.copy = function(text,msg) {
		 if(window.clipboardData){//判断是否具有clipboardData对象，IE
			 window.clipboardData.setData("Text",text);
		 }else if(window.netscape){//判断是否存在netscape对象，FF
			 try{//用try来尝试使用对象
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			 }catch(e){//如果不能使用剪贴板，提示用户出错
				 alert('您的浏览器安全限制限制您进行剪贴板操作。\n请取消限制后重试');
				 return false;
			 }
			 var clip,trans,str={},clipid;
			 if(!(clip=Components.classes["@mozilla.org/widget/clipboard;1"].createInstance(Components.interfaces.nsIClipboard))) return;
			 if(!(trans=Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable))) return;
			 trans.addDataFlavor("text/unicode");
			 str=Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
			 str.data=text;
			 trans.setTransferData("text/unicode",str,text.length*2);
			 clipid=Components.interfaces.nsIClipboard;		 
			 try{			 
				 clip.setData(trans,null,clipid.kGlobalClipboard);
			 }catch(e){return false}
		 }
		 if(msg){
			 alert(msg);
		 }else{
			alert('复制成功，您可以转发给您的QQ或微博或其它好友');
		 }
	}
	//对象到顶部距离
	this.offset = function (o , delscroll){
		o = this.obj(o);
		var top = o.offsetTop;
		var left = o.offsetLeft;
		var w = o.offsetWidth;
		var h = o.offsetHeight;
		var i = 0;
		while(o = o.offsetParent){
			top += o.offsetTop;
			left += o.offsetLeft;
			i++;
			if(i>100) break;
		}
		//是否去除滚动距离
		if(delscroll) {
			var scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
			var scrollleft = document.documentElement.scrollLeft || document.body.scrollLeft;
			top = top - scrolltop;
			left = left - scrollleft;
		}
		return {"top":top,"left":left,"height":h,"width":w};
	 }
	 //获取字符长度，中文算两个字节
	 this.len = function(o) {
		var len = 0;
		for(var i = 0 ; i < o.length ; i++) {
			if(this.is_fullchar(o[i]) || this.is_chinese(o[i])) {
				len+=2;
			} else {
				len++;
			}
		}
		return len;
	 }
	//是否含有中文（也包含日文和韩文）   
	this.is_chinese = function(str) {      
	   var reg = /[\u4E00-\u9FA5\uF900-\uFA2D]/g;   
	   return reg.test(str);   
	}   
	//同理，是否含有全角符号的函数   
	this.is_fullchar = function(str) {   
	   var reg = /[\uFF00-\uFFEF]/;   
	   return reg.test(str);   
	}

	//将越url地址解析成数组
	this.url_toarray = function(url , split) {
		if(!split) split = '?';
		var arr1 = url.split(split);
		var arr = [];
		var key,val;
		var arr3 = [],arr2 = [];
		arr['url'] = arr1[0];
		arr1.removeat(0);
		var strx = (arr1.length>0) ? arr1.join(split) : "";
		arr['pval'] = strx;
		arr1 = strx.split("&");
		for (var i = 0; i < arr1.length ; i++ ) {
			arr2 = arr1[i].split('=');
			key = arr2[0];
			arr2.removeat(0);
			val = arr2.join('=');
			arr2 = val.split("#");
			val = arr2[0];
			arr3[key] = val;
		}
		arr['param'] = arr3;
		return arr;
	}
	this.textarea_insertstr = function(o, value){
			o = kj.obj(o);
			o.focus();
			if(document.selection) {
				document.selection.createRange().text = value;
			} else {
				o.value = o.value.substr(0, o.selectionStart) + value + o.value.substr(o.selectionEnd);
			}
	}
	this.getAttribute = function(o,v) {
		o = kj.obj(o);
		return o.getAttribute(v);
	}
	this.setAttribute = function(o,n,v) {
		if(!o) return;
		o = kj.obj(o);
		if('length' in o) {
			for(var i = 0; i < o.length ; i++) {this.setAttribute(o[i],n,v);}
		} else {
			o.setAttribute(n,v);
		}
	}
	this.loadjs = function(name , func) {
		
		var obj_head = document.getElementsByTagName('HEAD').item(0); 
		var obj_script= document.createElement("script"); 
		obj_script.type = "text/javascript";
		name = this.url_view(name);
		obj_script.src = name+"?rnd="+Math.random();
		if(func) {
			if(this.agent() == 'ie') {
				obj_script.onreadystatechange = func;
			} else {
				obj_script.onload = func;
			}
		}
		obj_head.appendChild( obj_script);
	}
	this.childs = function(o) {
		var arr = o.getElementsByTagName('*');
		var obj = [];
		for(i = 0 ; i < arr.length ; i++ ) {
			if(arr[i].parentNode == o) obj[obj.length] = arr[i];
		}
		return obj;
	}
	this.radio = function(o) {
		if('length' in o) {
			for(var i = 0 ; i < o.length ; i++) {
				if(o[i].checked) return o[i];
			}
		} else {
			if(o.checked) return o;
		}
		return null;
	}
	this.add_favorite = function(url, title) {
		try {
			window.external.addFavorite(url, title);
		} catch (e){
			try {
				window.sidebar.addPanel(title, url, '');
				} catch (e) {
				alert("请按 Ctrl+D 键添加到收藏夹");
			}
		}
	}
	this.basename = function() {
		var url = location.href;
		var arr = url.split("/");
		var arrx= arr[arr.length-1].split("?");
		return arrx[0];
	}
	this.filetype = function(file) {
		file = file.toLowerCase();
		var arr = file.split(".");
		var ext = arr[arr.length-1];
		var arr_pic=['jpg','jpeg','gif','png','bmp','ico'];
		var arr_doc=['doc','docx','xls','xlsx'];
		var arr_rar=['rar','zip'];
		if(arr_pic.indexOf(ext)>=0) return 'pic';
		if(arr_doc.indexOf(ext)>=0) return 'doc';
		if(arr_rar.indexOf(ext)>=0) return 'rar';
		return 'other';
	}

}