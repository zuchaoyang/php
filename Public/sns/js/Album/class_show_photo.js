var YAO = function(){
	var D = document, OA = '[object Array]', FC = "[object Function]", OP = Object.prototype, nt = "nodeType", listeners = [], webkitKeymap = {
		63232: 38, // up
		63233: 40, // down
		63234: 37, // left
		63235: 39, // right
		63276: 33, // page up
		63277: 34, // page down
		25: 9 // SHIFT-TAB (Safari provides a different key code in
	}, patterns = {
		HYPHEN: /(-[a-z])/i,
		ROOT_TAG: /body|html/i
	}, lastError = null;
	
	return {
		isArray: function(obj){
			return OP.toString.apply(obj) === OA;
		},
		isString: function(s){
			return typeof s === 'string';
		},
		isBoolean: function(b){
			return typeof b === 'boolean';
		},
		isFunction: function(func){
			return OP.toString.apply(func) === FC;
		},
		isNull: function(obj){
			return obj === null;
		},
		isNumber: function(num){
			return typeof num === 'number' && isFinite(num);
		},
		isObject: function(str){
			return (str && (typeof str === "object" || this.isFunction(str))) || false;
		},
		isUndefined: function(obj){
			return typeof obj === 'undefined';
		},
		hasOwnProperty: function(obj, prper){
			if (OP.hasOwnProperty) {
				return obj.hasOwnProperty(prper);
			}
			return !this.isUndefined(obj[prper]) && obj.constructor.prototype[prper] !== obj[prper];
		},
		isMobile: function(mobile){
			return /^(13|15|18)\d{9}$/.test(YAO.trim(mobile));
		},
		isName: function(name){
			return /^[\w\u4e00-\u9fa5]{1}[\w\u4e00-\u9fa5 \.]{0,19}$/.test(YAO.trim(name));
		},
		
        keys: function(obj){
            var b = [];
            for (var p in obj) {
                b.push(p);
            }
            return b;
        },
        values: function(obj){
            var a = [];
            for (var p in obj) {
                a.push(obj[p]);
            }
            return a;
        },
        isXMLDoc: function(obj){
            return obj.documentElement && !obj.body || obj.tagName && obj.ownerDocument && !obj.ownerDocument.body;
        },
        formatNumber: function(b, e){
            e = e || '';
            b += '';
            var d = b.split('.');
            var a = d[0];
            var c = d.length > 1 ? '.' + d[1] : '';
            var f = /(\d+)(\d{3})/;
            while (f.test(a)) {
                a = a.replace(f, '$1,$2');
            }
            return e + a + c;
        },
        unformatNumber: function(a){
            return a.replace(/([^0-9\.\-])/g, '') * 1;
        },
        stringBuffer: function(){
            var a = [];
            for (var i = 0; i < arguments.length; ++i) {
                a.push(arguments[i]);
            }
            return a.join('');
        },
        trim: function(str){
            try {
                return str.replace(/^\s+|\s+$/g, '');
            } 
            catch (a) {
                return str;
            }
        },
        stripTags: function(str){
            return str.replace(/<\/?[^>]+>/gi, '');
        },
        stripScripts: function(str){
            return str.replace(/<script[^>]*>([\\S\\s]*?)<\/script>/g, '');
        },
        isJSON: function(obj){
            obj = obj.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
            return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(obj);
        },
        encodeHTML: function(str){
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        },
        decodeHTML: function(str){
            return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        },
		toCamel: function(property){
			if (!patterns.HYPHEN.test(property)) {
				return property;
			}
			if (propertyCache[property]) {
				return propertyCache[property];
			}
			var converted = property;
			while (patterns.HYPHEN.exec(converted)) {
				converted = converted.replace(RegExp.$1, RegExp.$1.substr(1).toUpperCase());
			}
			propertyCache[property] = converted;
			return converted;
		},
		printObj:function (obj){ 
		    var objStr = "print log : \n "; 
		    objStr += "obj: "; 
		    var o = obj; 
		    function toObjSource(){ 
		        objStr += "{"; 
		        for(k in o){ 
		            if(typeof o[k] == "object"){ 
		                objStr += k + ": "; 
		                o = o[k]; 
		                toObjSource(); 
		            }else{ 
		                objStr += k + ": " + o[k] + ", "; 
		            } 
		        } 
		        objStr = objStr.substr(0, objStr.lastIndexOf(",")) 
		        objStr += " }, "; 
		        o = obj; 
		        return objStr; 
		    } 
		    return toObjSource; 
		},
        Cookie: {
            set: function(g, c, f, b){
                var e = new Date();
                var a = new Date();
                if (f == null || f == 0) {
                    f = 1;
                }
                a.setTime(e.getTime() + 3600000 * 24 * f);
                D.cookie = g + '=' + encodeURI(c) + ';expires=' + a.toGMTString() + ';domain=' + b + '; path=/';
            },
            get: function(e){
                var b = D.cookie;
                var d = e + '=';
                var c = b.indexOf('; ' + d);
                if (c == -1) {
                    c = b.indexOf(d);
                    if (c != 0) {
                        return null;
                    }
                }
                else {
                    c += 2;
                }
                var a = D.cookie.indexOf(';', c);
                if (a == -1) {
                    a = b.length;
                }
                return decodeURI(b.substring(c + d.length, a));
            },
            clear: function(b, a){
                if (this.get(b)) {
                    D.cookie = b + '=' + ((domain) ? '; domain=' + a : '') + '; expires=Thu, 01-Jan-70 00:00:01 GMT';
                }
            }
        },
		
		ua: function(){
			var C = {
				ie: 0,
				opera: 0,
				gecko: 0,
				webkit: 0,
				mobile: null,
				air: 0,
				caja: 0
			}, B = navigator.userAgent, A;
			if ((/KHTML/).test(B)) {
				C.webkit = 1;
			}
			A = B.match(/AppleWebKit\/([^\s]*)/);
			if (A && A[1]) {
				C.webkit = parseFloat(A[1]);
				if (/ Mobile\//.test(B)) {
					C.mobile = 'Apple';
				}
				else {
					A = B.match(/NokiaN[^\/]*/);
					if (A) {
						C.mobile = A[0];
					}
				}
				A = B.match(/AdobeAIR\/([^\s]*)/);
				if (A) {
					C.air = A[0];
				}
			}
			if (!C.webkit) {
				A = B.match(/Opera[\s\/]([^\s]*)/);
				if (A && A[1]) {
					C.opera = parseFloat(A[1]);
					A = B.match(/Opera Mini[^;]*/);
					if (A) {
						C.mobile = A[0];
					}
				}
				else {
					A = B.match(/MSIE\s([^;]*)/);
					if (A && A[1]) {
						C.ie = parseFloat(A[1]);
					}
					else {
						A = B.match(/Gecko\/([^\s]*)/);
						if (A) {
							C.gecko = 1;
							A = B.match(/rv:([^\s\)]*)/);
							if (A && A[1]) {
								C.gecko = parseFloat(A[1]);
							}
						}
					}
				}
			}
			A = B.match(/Caja\/([^\s]*)/);
			if (A && A[1]) {
				C.caja = parseFloat(A[1]);
			}
			return C;
		}(),
		
        extend: function(subClass, superClass, override){
            if (!superClass || !subClass) {
                throw new Error('extend failed, please check that all dependencies are included.');
            }
            var F = function(){};
            F.prototype = superClass.prototype;
            subClass.prototype = new F();
            subClass.prototype.constructor = subClass;
            subClass.superclass = superClass.prototype;
            if (superClass.prototype.constructor == Object.prototype.constructor) {
                superClass.prototype.constructor = superClass;
            }
            if (override) {
                for (var p in override) {
                    subClass.prototype[p] = override[p];
                }
            }
        },
        augmentProto: function(sub, sup){
            if (!sub || !sup) {
                throw new Error('augment failed, please check that all dependencies are included.');
            }
            var d = sub.prototype, g = sup.prototype, b = arguments, c, h;
            if (b[2]) {
                for (c = 2; c < b.length; c += 1) {
                    d[b[c]] = g[b[c]];
                }
            }
            else {
                for (h in g) {
                    if (!d[h]) {
                        d[h] = g[h];
                    }
                }
            }
        },
        augmentObject: function(e, d){
            if (!d || !e) {
                throw new Error('augment failed, please check that all dependencies are included.');
            }
            var b = arguments, c, f;
            if (b[2]) {
                if (YAO.isString(b[2])) {
                    e[b[2]] = d[b[2]];
                }
                else {
                    for (c = 0; c < b[2].length; c += 1) {
                        e[b[2][c]] = d[b[2][c]];
                    }
                }
            }
            else {
                for (f in d) {
                    e[f] = d[f];
                }
            }
            return e;
        },
        clone: function(d, f){
            var e = function(){
            }, b, c = arguments;
            e.prototype = d;
            b = new e;
            if (f) {
                for (p in f) {
                    b[p] = f[p];
                }
            }
            return b;
        },
		
		addListener: function(el, sType, fn, obj, overrideContext, bCapture){
			var oEl = null, context = null, wrappedFn = null;
			if(YAO.isString(el)){
				oEl = YAO.getEl(el);
				el = oEl;
			}
			if(!el || !fn || !fn.call){
				return false;
			}
			context = el;
			if (overrideContext) {
				if (overrideContext === true) {
					context = obj;
				}
				else {
					context = overrideContext;
				}
			}
			wrappedFn = function(e){
				return fn.call(context, YAO.getEvent(e, el), obj);
			};
			try {
				try {
					el.addEventListener(sType, wrappedFn, bCapture);
				} 
				catch (e) {
					try {
						el.attachEvent('on' + sType, wrappedFn);
					} 
					catch (e) {
						el['on' + sType] = wrappedFn;
					}
				}
			} 
			catch (e) {
				lastError = e;
				this.removeListener(el, sType, wrappedFn, bCapture);
				return false;
			}
			if ('unload' != sType) {
				// cache the listener so we can try to automatically unload
				listeners[listeners.length] = [el, sType, fn, wrappedFn, bCapture];
			}
			return true;
		},
        removeListener: function(el, sType, fn, bCapture){
			try {
				if (window.removeEventListener) {
					return function(el, sType, fn, bCapture){
						el.removeEventListener(sType, fn, (bCapture));
					};
				}
				else {
					if (window.detachEvent) {
						return function(el, sType, fn){
							el.detachEvent("on" + sType, fn);
						};
					}
					else {
						return function(){
						};
					}
				}
			} 
			catch (e) {
				lastError = e;
				return false;
			}
			
			return true;
		},
		on: function(el, sType, fn, obj, overrideContext){
			var oEl = obj || el, scope = overrideContext || this;
			return YAO.addListener(el, sType, fn, oEl, scope, false);
		},
		stopEvent: function(evt){
			this.stopPropagation(evt);
			this.preventDefault(evt);
		},
		stopPropagation: function(evt){
			
			if (evt.stopPropagation) {
				evt.stopPropagation();
			}
			else {
				evt.cancelBubble = true;
			}
		},
		preventDefault: function(evt){
			if (evt.preventDefault) {
				evt.preventDefault();
			}
			else {
				evt.returnValue = false;
			}
		},
		getEvent: function(e, boundEl){
			var ev = e || window.event;
			
			if (!ev) {
				var c = this.getEvent.caller;
				while (c) {
					ev = c.arguments[0];
					if (ev && Event == ev.constructor) {
						break;
					}
					c = c.caller;
				}
			}
			
			return ev;
		},
		getCharCode: function(ev){
			var code = ev.keyCode || ev.charCode || 0;
			
			// webkit key normalization
			if (YAO.ua.webkit && (code in webkitKeymap)) {
				code = webkitKeymap[code];
			}
			return code;
		},
		_unload: function(e){
			var j, l;
			if (listeners) {
				for (j = listeners.length - 1; j > -1; j--) {
					l = listeners[j];
					if (l) {
						YAO.removeListener(l[0], l[1], l[3], l[4]);
					}
				}
				l = null;
			}
			
			YAO.removeListener(window, "unload", YAO._unload);
		},
		
		getEl: function(elem){
			var elemID, E, m, i, k, length, len;
			if (elem) {
				if (elem[nt] || elem.item) {
					return elem;
				}
				if (YAO.isString(elem)) {
					elemID = elem;
					elem = D.getElementById(elem);
					if (elem && elem.id === elemID) {
						return elem;
					}
					else {
						if (elem && elem.all) {
							elem = null;
							E = D.all[elemID];
							for (i = 0, len = E.length; i < len; i += 1) {
								if (E[i].id === elemID) {
									return E[i];
								}
							}
						}
					}
					return elem;
				}
				else {
					if (elem.DOM_EVENTS) {
						elem = elem.get("element");
					}
					else {
						if (YAO.isArray(elem)) {
							m = [];
							for (k = 0, length = elem.length; k < length; k += 1) {
								m[m.length] = YAO.getEl(elem[k]);
							}
							return m;
						}
					}
				}
			}
			return null;
		},
		hasClass: function(elem, className){
			var has = new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)");
			return has.test(elem.className);
		},
		addClass: function(elem, className){
			if (YAO.hasClass(elem, className)) {
				return;
			}
			elem.className = [elem.className, className].join(" ");
		},
		removeClass: function(elem, className){
			var replace = new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)", "g");
			if (!YAO.hasClass(elem, className)) {
				return;
			}
			var o = elem.className;
			elem.className = o.replace(replace, " ");
			if (YAO.hasClass(elem, className)) {
				YAO.removeClass(elem, className);
			}
		},
		replaceClass: function(elem, newClass, oldClass){
			if (newClass === oldClass) {
				return false;
			}
			var has = new RegExp("(?:^|\\s+)" + newClass + "(?:\\s+|$)", "g");
			if (!YAO.hasClass(elem, newClass)) {
				YAO.addClass(elem, oldClass);
				return;
			}
			elem.className = elem.className.replace(has, " " + oldClass + " ");
			if (YAO.hasClass(elem, newClass)) {
				YAO.replaceClass(elem, newClass, oldClass);
			}
		},
		getElByClassName: function(className, tag, rootTag){
			var elems = [], i, tempCnt = YAO.getEl(rootTag).getElementsByTagName(tag), len = tempCnt.length;
			for (i = 0; i < len; ++i) {
				if (YAO.hasClass(tempCnt[i], className)) {
					elems.push(tempCnt[i]);
				}
			}
			if (elems.length < 1) {
				return false;
			}
			else {
				return elems;
			}
		},
		getStyle: function(el, property){
			if (document.defaultView && document.defaultView.getComputedStyle) {
				var value = null;
				if (property == 'float') {
					property = 'cssFloat';
				}
				var computed = document.defaultView.getComputedStyle(el, '');
				if (computed) {
					value = computed[YAO.toCamel(property)];
				}
				return el.style[property] || value;
			}
			else {
				if (document.documentElement.currentStyle && YAO.ua.ie) {
					switch (YAO.toCamel(property)) {
						case 'opacity':
							var val = 100;
							try {
								val = el.filters['DXImageTransform.Microsoft.Alpha'].opacity;
							} 
							catch (e) {
								try {
									val = el.filters('alpha').opacity;
								} 
								catch (e) {
								}
							}
							return val / 100;
							break;
						case 'float':
							property = 'styleFloat';
						default:
							var value = el.currentStyle ? el.currentStyle[property] : null;
							return (el.style[property] || value);
					}
				}
				else {
					return el.style[property];
				}
			}
		},
		setStyle: function(el, property, val){
			if (YAO.ua.ie) {
				switch (property) {
					case 'opacity':
						if (YAO.isString(el.style.filter)) {
							el.style.filter = 'alpha(opacity=' + val * 100 + ')';
							if (!el.currentStyle || !el.currentStyle.hasLayout) {
								el.style.zoom = 1;
							}
						}
						break;
					case 'float':
						property = 'styleFloat';
					default:
						el.style[property] = val;
				}
			}
			else {
				if (property == 'float') {
					property = 'cssFloat';
				}
				el.style[property] = val;
			}
		},
		setStyles: function(el, propertys){
			for(var p in propertys){
				YAO.setStyle(el,p,propertys[p]);
			}
			return el;
		},
        getElementsBy: function(method, tag, root){
            tag = tag || "*";
            var m = [];
            if (root) {
                root = YAO.getEl(root);
                if (!root) {
                    return m;
                }
            }
            else {
                root = document;
            }
            var oElem = root.getElementsByTagName(tag);
            if (!oElem.length && (tag === "*" && root.all)) {
                oElem = root.all;
            }
            for (var n = 0, j = oElem.length; n < j; ++n) {
                if (method(oElem[n])) {
                    m[m.length] = oElem[n];
                }
            }
            return m;
        },
        getDocumentWidth: function(){
            var k = YAO.getScrollWidth();
            var j = Math.max(k, YAO.getViewportWidth());
            return j;
        },
        getDocumentHeight: function(){
            var k = YAO.getScrollHeight();
            var j = Math.max(k, YAO.getViewportHeight());
            return j;
        },
        getScrollWidth: function(){
            var j = (D.compatMode == "CSS1Compat") ? D.body.scrollWidth : D.Element.scrollWidth;
            return j;
        },
        getScrollHeight: function(){
            var j = (D.compatMode == "CSS1Compat") ? D.body.scrollHeight : D.documentElement.scrollHeight;
            return j;
        },
        getXScroll: function(){
            var j = self.pageXOffset || D.documentElement.scrollLeft || D.body.scrollLeft;
            return j;
        },
        getYScroll: function(){
            var j = self.pageYOffset || D.documentElement.scrollTop || D.body.scrollTop;
            return j;
        },
        getViewportWidth: function(){
            var j = self.innerWidth;
            var k = D.compatMode;
            if (k || c) {
                j = (k == "CSS1Compat") ? D.documentElement.clientWidth : D.body.clientWidth;
            }
            return j;
        },
        getViewportHeight: function(){
            var j = self.innerHeight;
            var k = D.compatMode;
            if ((k || c) && !a) {
                j = (k == "CSS1Compat") ? D.documentElement.clientHeight : D.body.clientHeight;
            }
            return j;
        },
        removeChildren: function(j){
            if (!(prent = YAO.getEl(j))) {
                return false;
            }
            while (j.firstChild) {
                j.firstChild.parentNode.removeChild(j.firstChild);
            }
            return j;
        },
        prependChild: function(k, j){
            if (!(k = YAO.getEl(k)) || !(j = YAO.getEl(j))) {
                return false;
            }
            if (k.firstChild) {
                k.insertBefore(j, k.firstChild);
            }
            else {
                k.appendChild(j);
            }
            return k;
        },
        insertAfter: function(l, j){
            var k = j.parentNode;
            if (k.lastChild == j) {
                k.appendChild(l);
            }
            else {
                k.insertBefore(l, j.nextSibling);
            }
        },
		setOpacity: function(el, val){
			YAO.setStyle(el, 'opacity', val);
		},
		Builder: {
			nidx: 0,
			NODEMAP: {
				AREA: 'map',
				CAPTION: 'table',
				COL: 'table',
				COLGROUP: 'table',
				LEGEND: 'fieldset',
				OPTGROUP: 'select',
				OPTION: 'select',
				PARAM: 'object',
				TBODY: 'table',
				TD: 'table',
				TFOOT: 'table',
				TH: 'table',
				THEAD: 'table',
				TR: 'table'
			},
			ATTR_MAP: {
				'className': 'class',
				'htmlFor': 'for',
				'readOnly': 'readonly',
				'maxLength': 'maxlength',
				'cellSpacing': 'cellspacing'
			},
			EMPTY_TAG: /^(?:BR|FRAME|HR|IMG|INPUT|LINK|META|RANGE|SPACER|WBR|AREA|PARAM|COL)$/i,
			// 追加Link节点（添加CSS样式表）
			linkNode: function(url, cssId, charset){
				var c = charset || 'utf-8', link = null;
				var head = D.getElementsByTagName('head')[0];
				link = this.Node('link', {
					'id': cssId || ('link-' + (YAO.Builder.nidx++)),
					'type': 'text/css',
					'charset': c,
					'rel': 'stylesheet',
					'href': url
				});
				head.appendChild(link);
				return link;
			},
			// 追加Script节点
			scriptNode: function(url, scriptId, win, charset){
				var d = win || document.body;
				var c = charset || 'utf-8';
				return d.appendChild(this.Node('script', {
					'id': scriptId || ('script-' + (YAO.Builder.nidx++)),
					'type': 'text/javascript',
					'charset': c,
					'src': url
				}));
			},
			// 创建元素节点
			Node: function(tag, attr, children){
				tag = tag.toUpperCase();
				// try innerHTML approach
				var parentTag = YAO.Builder.NODEMAP[tag] || 'div';
				var parentElement = D.createElement(parentTag);
				var elem = null;
				try { // prevent IE "feature": http://dev.rubyonrails.org/ticket/2707
				    if (this.EMPTY_TAG.test(tag)) {
						//alert(tag);
					}
					else {
						parentElement.innerHTML = "<" + tag + "></" + tag + ">";
					}
				} 
				catch (e) {
				}
				elem = parentElement.firstChild;
				
				// see if browser added wrapping tags
				if (elem && (elem.tagName.toUpperCase() != tag)) {
					elem = elem.getElementsByTagName(tag)[0];
				}
				// fallback to createElement approach
				if (!elem) {
					if (YAO.isString(tag)) {
						elem = D.createElement(tag);
					}
				}
				// abort if nothing could be created
				if (!elem) {
					return;
				}
				else {
					if (attr) {
						this.Attributes(elem, attr);
					}
					if (children) {
						this.Child(elem, children);
					}
					return elem;
				}
			},
			// 给节点添加属性
			Attributes: function(elem, attr){
				var attrName = '', i;
				for (i in attr) {
					if (attr[i] && YAO.hasOwnProperty(attr, i)) {
						attrName = i in YAO.Builder.ATTR_MAP ? YAO.Builder.ATTR_MAP[i] : i;
						if (attrName === 'class') {
							elem.className = attr[i];
						}
						else {
							elem.setAttribute(attrName, attr[i]);
						}
					}
				}
				return elem;
			},
			// 追加子节点
			Child: function(parent, child){
				if (child.tagName) {
					parent.appendChild(child);
					return false;
				}
				if (YAO.isArray(child)) {
					var i, length = child.length;
					for (i = 0; i < length; i += 1) {
						if (child[i].tagName) {
							parent.appendChild(child[i]);
						}
						else {
							if (YAO.isString(child[i])) {
								parent.appendChild(D.createTextNode(child[i]));
							}
						}
					}
				}
				else {
					if (YAO.isString(child)) {
						parent.appendChild(D.createTextNode(child));
					}
				}
			}
		},
		
		batch: function(el, method, o, override){
			var id = el;
			el = YAO.getEl(el);
			var scope = (override) ? o : window;
			if (!el || el.tagName || !el.length) {
				if (!el) {
					return false;
				}
				return method.call(scope, el, o);
			}
			var collection = [];
			for (var i = 0, len = el.length; i < len; ++i) {
				if (!el[i]) {
					id = el[i];
				}
				collection[collection.length] = method.call(scope, el[i], o);
			}
			return collection;
		},

		fadeUp: function(elem){
			if (elem) {
				var level = 0, fade = function(){
					var timer = null;
					level += 0.05;
					if (timer) {
						clearTimeout(timer);
						timer = null;
					}
					if (level > 1) {
						YAO.setOpacity(elem, 1);
						return false;
					}
					else {
						YAO.setOpacity(elem, level);
					}
					timer = setTimeout(fade, 50);
				};
				fade();
			}
		},
		zebra: function(){
			var j, length = arguments.length;
			for (j = 0; j < length; ++j) {
				(function(config){
					var root = YAO.getEl(config.rootTag) || (config.root || null), rows = root.getElementsByTagName(config.rowTag) || (config.rows || null), i, len = rows.length, lastClass = [];
					if (root && rows && len > 1) {
						for (var i = 0; i < len; ++i) {
							rows[i].className = i % 2 === 0 ? 'even' : 'odd';
							lastClass[i] = rows[i].className;
							YAO.on(rows[i],'mouseover', function(index){
								return function(){
									YAO.replaceClass(this, lastClass[index], 'hover');
								}
							}(i),rows[i],true);
							YAO.on(rows[i], 'mouseout', function(index){
								return function(){
									YAO.replaceClass(this, 'hover', lastClass[index]);
								}
							}(i),rows[i],true);
						}
					}
					else {
						return false;
					}
				})(arguments[j]);
			}
		},
		moveElement: function(element, finalX, finalY, speed){
			var elem = YAO.isString(element) ? YAO.getEl(element) : element, style = null;
			if (elem) {
				if (elem.movement) {
					clearTimeout(elem.movement);
				}
				if (!elem.style.left) {
					elem.style.left = "0";
				}
				if (!elem.style.top) {
					elem.style.top = "0";
				}
				var xpos = parseInt(elem.style.left);
				var ypos = parseInt(elem.style.top);
				if (xpos == finalX && ypos == finalY) {
					return true;
				}
				if (xpos < finalX) {
					var dist = Math.ceil((finalX - xpos) / 10);
					xpos = xpos + dist;
				}
				if (xpos > finalX) {
					var dist = Math.ceil((xpos - finalX) / 10);
					xpos = xpos - dist;
				}
				if (ypos < finalY) {
					var dist = Math.ceil((finalY - ypos) / 10);
					ypos = ypos + dist;
				}
				if (ypos > finalY) {
					var dist = Math.ceil((ypos - finalY) / 10);
					ypos = ypos - dist;
				}
				elem.style.left = xpos + "px";
				elem.style.top = ypos + "px";
				elem.movement = setTimeout(function(){
					YAO.moveElement(element, finalX, finalY, speed);
				}, speed);
			}
		},
		
		ajax: function(config){
			var oXhr, method = config.method ? config.method.toUpperCase() : 'GET', url = config.url || '', fn = config.fn || null, postData = config.data || null, elem = config.id ? YAO.getEl(config.id) : (config.element || null), load = config.loadFn ? config.loadFn : (config.loading || '正在获取数据，请稍后...');
			if (!url) {
				return;
			}
			if (window.XMLHttpRequest) {
				oXhr = new XMLHttpRequest();
			}
			else {
				if (window.ActiveXObject) {
					oXhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
			}
			if (oXhr) {
				try {
					oXhr.open(method, url, true);
					oXhr.onreadystatechange = function(){
						if (oXhr.readyState !== 4) {
							return false
						}
						if (oXhr.readyState == 4) {
							if (oXhr.status == 200 || location.href.indexOf('http') === -1) {
								if (fn) {
									fn.success(oXhr);
								}
								else {
									elem.innerHTML = oXhr.responseText;
								}
							}
							else {
								if (fn) {
									fn.failure(oXhr.status);
								}
								else {
									if (YAO.isFunction(load)) {
										load();
									}
									else {
										elem.innerHTML = load;
									}
								}
							}
						}
					};
					oXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
					if (postData) {
						oXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
					}
					oXhr.send(postData);
				} 
				catch (e) {
					throw new Error(e);
					return false;
				}
			}
			else{
				throw new Error("Your browser does not support XMLHTTP.");
				return false;
			}
		},
		JSON: function(){
			function f(n){
				return n < 10 ? '0' + n : n;
			}
			
			Date.prototype.toJSON = function(){
				return this.getUTCFullYear() + '-' + f(this.getUTCMonth() + 1) + '-' + f(this.getUTCDate()) + 'T' + f(this.getUTCHours()) + ':' + f(this.getUTCMinutes()) + ':' + f(this.getUTCSeconds()) + 'Z';
			};
			
			var m = {
				'\b': '\\b',
				'\t': '\\t',
				'\n': '\\n',
				'\f': '\\f',
				'\r': '\\r',
				'"': '\\"',
				'\\': '\\\\'
			};
			
			function stringify(value, whitelist){
				var a, i, k, l, r = /["\\\x00-\x1f\x7f-\x9f]/g, v;
				switch (typeof value) {
					case 'string':
						return r.test(value) ? '"' +
						value.replace(r, function(a){
							var c = m[a];
							if (c) {
								return c;
							}
							c = a.charCodeAt();
							return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
						}) +
						'"' : '"' + value + '"';
					case 'number':
						return isFinite(value) ? String(value) : 'null';
					case 'boolean':
					case 'null':
						return String(value);
					case 'object':
						if (!value) {
							return 'null';
						}
						
						if (typeof value.toJSON === 'function') {
							return stringify(value.toJSON());
						}
						a = [];
						if (typeof value.length === 'number' && !(value.propertyIsEnumerable('length'))) {
						
							l = value.length;
							for (i = 0; i < l; i += 1) {
								a.push(stringify(value[i], whitelist) || 'null');
							}
							
							return '[' + a.join(',') + ']';
						}
						if (whitelist) {
							l = whitelist.length;
							for (i = 0; i < l; i += 1) {
								k = whitelist[i];
								if (typeof k === 'string') {
									v = stringify(value[k], whitelist);
									if (v) {
										a.push(stringify(k) + ':' + v);
									}
								}
							}
						}
						else {
							for (k in value) {
								if (typeof k === 'string') {
									v = stringify(value[k], whitelist);
									if (v) {
										a.push(stringify(k) + ':' + v);
									}
								}
							}
						}
						return '{' + a.join(',') + '}';
				}
			}
			
			return {
				stringify: stringify,
				parse: function(text, filter){
					var j;
					
					function walk(k, v){
						var i, n;
						if (v && typeof v === 'object') {
							for (i in v) {
								if (OP.hasOwnProperty.apply(v, [i])) {
									n = walk(i, v[i]);
									if (n !== undefined) {
										v[i] = n;
									}
									else {
										delete v[i];
									}
								}
							}
						}
						return filter(k, v);
					}
					
					if (/^[\],:{}\s]*$/.test(text.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
						j = eval('(' + text + ')');
						
						return typeof filter === 'function' ? walk('', j) : j;
					}
					
					throw new SyntaxError('parseJSON');
				}
			};
		}(),
		
		YTabs: function(){
			var j, len = arguments.length, Tabs = [];
			for (j = 0; j < len; ++j) {
				Tabs[j] = new YAO.singleTab(arguments[j]);
			}
			return Tabs;
		},
		scrollNews: function(S, SI, RT, CT){
            var SN = new YAO.scrollVertical(S, SI, RT, CT);
            SN.speed = 4000;
            SN.isPause = true;
            var TM = setTimeout(function(){
                if (TM) {
                    clearTimeout(TM);
                }
                SN.isPause = false;
            }, 2000);
            YAO.on(SN.scrollArea, 'mouseover', function(){
                SN.isPause = true;
            });
            YAO.on(SN.scrollArea, 'mouseout', function(){
                SN.isPause = false;
            });
        }
	};
	
	YAO.on(window, "unload", YAO._unload);
}();


YAO.Carousel = function(oConfig){
	this.btnPrevious = oConfig.btnPrevious;
	this.lnkBtnPrevious = this.btnPrevious.getElementsByTagName('a')[0];
	this.Container = oConfig.ContainerID ? YAO.getEl(oConfig.ContainerID) : (oConfig.Container || null);
	this.Scroller = oConfig.scrollId ? YAO.getEl(oConfig.scrollId) : (oConfig.Scroller || null);
	this.btnNext = oConfig.btnNext;
	this.lnkBtnNext = this.btnNext.getElementsByTagName('a')[0];
	this.items = oConfig.itemTag ? this.Container.getElementsByTagName(oConfig.itemTag) : (oConfig.items || null);
	this.length = this.items.length;
	this.itemWidth = this.items[0].offsetWidth;
	this.itemHeight = this.items[0].offsetHeight;
	this.scrollerWidth = this.itemWidth * this.length;
	this.scrollHeight = this.itemHeight * this.length;
	this.derection = oConfig.derection || 'H';
	this.stepHeight = oConfig.stepHeight || this.itemHeight;
	this.stepWidth = oConfig.stepWidth || this.itemWidth;
	this.groups = this.derection === 'H' ? Math.ceil(this.scrollerWidth / this.stepWidth) : Math.ceil(this.scrollHeight / this.stepHeight);
	this.maxMovedNum = this.derection === 'H' ? (this.groups - (this.Container.offsetWidth / this.stepWidth)) : (this.groups - (this.Container.offsetHeight / this.stepHeight));
	this.scrollSpeed = oConfig.speed || 50;
	
	this.init();
};
YAO.Carousel.prototype.movedNum = 0;
YAO.Carousel.prototype.init = function(){
	var oSelf = this;
	if (this.derection === 'H') {
		this.Scroller.style.width = this.scrollerWidth + 'px';
	}
	else {
		this.Scroller.style.height = this.scrollerHeight + 'px';
	}
	this.Container.style.overflow = 'hidden';
	if (this.lnkBtnNext && this.movedNum === this.maxMovedNum) {
		YAO.addClass(this.lnkBtnNext, 'dis');
	}
	if (this.lnkBtnPrevious && this.movedNum === 0) {
		YAO.addClass(this.lnkBtnPrevious, 'dis');
	}
	YAO.on(this.btnPrevious, 'click', this.scrollPrevious, this.btnPrevious, oSelf);
	YAO.on(this.btnNext, 'click', this.scrollNext, this.btnNext, oSelf);
};
YAO.Carousel.prototype.scrollPrevious = function(event){
	var evt = event || window.event;
	if (this.movedNum > 0) {
		this.movedNum -= 1;
		if (this.lnkBtnNext && YAO.hasClass(this.lnkBtnNext, 'dis')) {
			YAO.removeClass(this.lnkBtnNext, 'dis');
		}
		if (this.movedNum <= 0) {
			this.movedNum = 0;
			if (this.lnkBtnPrevious) {
				YAO.addClass(this.lnkBtnPrevious, 'dis');
			}
		}
		this.scroll(this.movedNum);
	}
	YAO.stopEvent(evt);
};
YAO.Carousel.prototype.scrollNext = function(event){
	var evt = event || window.event;
	if (this.movedNum < this.maxMovedNum) {
		this.movedNum += 1;
		if (this.lnkBtnPrevious && YAO.hasClass(this.lnkBtnPrevious, 'dis')) {
			YAO.removeClass(this.lnkBtnPrevious, 'dis');
		}
		if (this.movedNum >= this.maxMovedNum) {
			this.movedNum = this.maxMovedNum;
			if (this.lnkBtnNext) {
				YAO.addClass(this.lnkBtnNext, 'dis');
			}
		}
		this.scroll(this.movedNum);
	}
	YAO.stopEvent(evt);
}; 
YAO.Carousel.prototype.scroll = function(steps){
	var scrollWidth = 0, scrollHeight = 0;
	if (this.derection === 'H') {
		if (this.stepWidth) {
			scrollWidth = -(this.stepWidth * steps);
		}
		else {
			scrollWidth = -(this.itemWidth * steps);
		}
	}
	else {
		if (this.stepHeight) {
			scrollHeight = -(this.stepHeight * steps);
		}
		else {
			scrollHeight = -(this.itemHeight * steps);
		}
	}
	YAO.moveElement(this.Scroller, scrollWidth, scrollHeight, this.scrollSpeed);
};

YAO.YAlbum = function(){
	var oSelf = this;
	this.limitInterval = null;
	this.max_length = 20;
	
	this.client_account = $("#client_account").val();
	this.class_code = $("#class_code").val();
	this.album_id = $("#album_id").val();
	this.is_edit = $("#is_edit").val();
	this.img_server = $("#img_server").val();
	this.photo_info_list = {};
	this.album_list = {};
	this.oCarousel = new YAO.Carousel({
		btnPrevious: oSelf.CARSOUEL_BTN_PREVIOUS,
		Container: oSelf.CARSOUEL_CONTAINER,
		Scroller: oSelf.CARSOUEL_SCROLLER,
		btnNext: oSelf.CARSOUEL_BTN_NEXT,
		itemTag: oSelf.CARSOUEL_ITEM_TAG,
		stepWidth: oSelf.CARSOUEL_STEP_WIDTH
	}) || null;
	var host = 'http://'+window.location.host;
	
	this.oSamples = this.oCarousel.Scroller.getElementsByTagName('a') || null;
	this.length = this.oSamples.length || 0;
	this.lastSample = this.oSamples[0] || null;
	var small_pic_obj = this.oSamples;
	
	var current_pic = host+$("img", $("#"+this.PHOTO_CONTAINER_ID)).attr('src');
	for(var n in small_pic_obj) {
		if(current_pic == small_pic_obj[n]) {
			this.lastSample = small_pic_obj[n];
			this.lastIndex = parseInt(n);
		}
	}
	this.photoContainer = YAO.getEl(this.PHOTO_CONTAINER_ID) || null;
	this.photo = YAO.getEl(this.PHOTO_ID) || null;
	this.photoIntro = YAO.getEl(this.PHOTO_INTRO_ID) || null;
	this.sIntro = this.photo.alt || '';
	

	this.init();
	this.delegateEvent();
	this.editor();
};

YAO.YAlbum.prototype.lastIndex = 0;
YAO.YAlbum.prototype.isLoading = false;
YAO.YAlbum.prototype.lastPhotoHeight = 0;
YAO.YAlbum.prototype.loadShardow = null;
YAO.YAlbum.prototype.loadImg = null;

YAO.YAlbum.prototype.CARSOUEL_BTN_PREVIOUS = YAO.getEl('carousel_btn_lastgroup');
YAO.YAlbum.prototype.CARSOUEL_CONTAINER = YAO.getEl('carousel_container');
YAO.YAlbum.prototype.CARSOUEL_SCROLLER = YAO.getEl('samples_list');
YAO.YAlbum.prototype.CARSOUEL_BTN_NEXT = YAO.getEl('carousel_btn_nextgroup');
YAO.YAlbum.prototype.CARSOUEL_ITEM_TAG = 'li';
YAO.YAlbum.prototype.CARSOUEL_STEP_WIDTH = 560;
YAO.YAlbum.prototype.PHOTO_MAX_WIDTH = 650;
YAO.YAlbum.prototype.PHOTO_CONTAINER_ID = 'carousel_photo_container';
YAO.YAlbum.prototype.PHOTO_ID = 'carousel_photo';
YAO.YAlbum.prototype.PHOTO_INTRO_ID = 'carousel_photo_intro';
YAO.YAlbum.prototype.BTN_NEXT_ID = 'carousel_next_photo';
YAO.YAlbum.prototype.BTN_NEXT_CLASS = 'next';
YAO.YAlbum.prototype.BTN_PREVIOUS_ID = 'carousel_previous_photo';
YAO.YAlbum.prototype.BTN_PREVIOUS_CLASS = 'previous';
YAO.YAlbum.prototype.BTN_DISABLED_CLASS = 'dis';
YAO.YAlbum.prototype.IMG_BTN_PREVIOUS = 'url(/Public/sns/images/Album/class_photo_show/last-photo.gif)';
YAO.YAlbum.prototype.IMG_BTN_NEXT = 'url(/Public/sns/images/Album/class_photo_show/next-photo.gif)';
YAO.YAlbum.prototype.SHARDOW_ID = 'carousel_photo_shardow';
YAO.YAlbum.prototype.LOAD_IMG_PATH = '/Public/sns/images/Album/class_photo_show/loading.gif';
YAO.YAlbum.prototype.LOAD_IMG_ID = 'carousel_photo_loading';

YAO.YAlbum.prototype.editor = function() {
	if(!this.is_edit) {
		$(".photo_name").remove();
		$("#photo_edit").html('');
	}
}
YAO.YAlbum.prototype.init = function(){
	var oSelf = this, i;
	YAO.addClass(this.lastSample, 'current');
	this.btnPrevious = YAO.Builder.Node('a', {
		href: oSelf.oSamples[oSelf.lastIndex].href,
		id: oSelf.BTN_PREVIOUS_ID,
		className: oSelf.BTN_PREVIOUS_CLASS,
		title: '上一张',
		name:oSelf.oSamples[oSelf.lastIndex].id
	}, '上一张');
	this.photoContainer.appendChild(this.btnPrevious);
	this.btnNext = YAO.Builder.Node('a', {
		href: oSelf.oSamples[oSelf.lastIndex].href,
		id: oSelf.BTN_NEXT_ID,
		className: oSelf.BTN_NEXT_CLASS,
		title: '下一张',
		name:oSelf.oSamples[oSelf.lastIndex].id
	}, '下一张');
	this.photoContainer.appendChild(this.btnNext);
	this.load(this.photo.src);
	
	YAO.on(this.btnPrevious, 'click', function(event){
		var evt = event || window.event;

		this.Previous();
		YAO.stopEvent(evt);
	}, this.btnPrevious, oSelf);
	YAO.on(this.btnNext, 'click', function(event){
		var evt = event || window.event;
		
		this.Next();
		YAO.stopEvent(evt);
	}, this.btnNext, oSelf);
	
	for (i = 0; i < this.length; ++i) {
		YAO.on(this.oSamples[i], 'click', function(index){
			return function(event){
				var evt = event || window.event, curSample = this.oSamples[index];
				if (this.lastSample !== curSample && !this.isLoading) {
					this.lastIndex = index;
					this.btnsEnabled();
					this.chgPhoto();
				}
				YAO.stopEvent(evt);
			};
		}(i), this.oSamples[i], oSelf);
	}
	this.btnsEnabled();
	this.chgPhoto();
};
YAO.YAlbum.prototype.btnsEnabled = function(){
	if (this.lastIndex !== 0 && YAO.hasClass(this.btnPrevious, this.BTN_DISABLED_CLASS)) {
		YAO.removeClass(this.btnPrevious, this.BTN_DISABLED_CLASS);
		if (YAO.ua.ie) {
			this.btnPrevious.style.backgroundImage = this.IMG_BTN_PREVIOUS;
		}
		this.btnPrevious.href = this.oSamples[this.lastIndex - 1];
		this.btnPrevious.name = this.oSamples[this.lastIndex - 1].id;
	}
	else {
		if (this.lastIndex === 0) {
			YAO.addClass(this.btnPrevious, this.BTN_DISABLED_CLASS);
			if (YAO.ua.ie) {
				this.btnPrevious.style.backgroundImage = 'none';
			}
			this.btnPrevious.href = this.oSamples[this.lastIndex];
			this.btnPrevious.name = this.oSamples[this.lastIndex].id;
		}else{
			this.btnPrevious.name = this.oSamples[this.lastIndex - 1].id;
		}
	}
	if (this.lastIndex !== (this.length - 1) && YAO.hasClass(this.btnNext, this.BTN_DISABLED_CLASS)) {
		YAO.removeClass(this.btnNext, this.BTN_DISABLED_CLASS);
		if (YAO.ua.ie) {
			this.btnNext.style.backgroundImage = this.IMG_BTN_NEXT;
		}
		this.btnNext.href = this.oSamples[this.lastIndex + 1];
		this.btnNext.name = this.oSamples[this.lastIndex + 1].id;
	}
	else {
		if (this.lastIndex === (this.length - 1)) {
			YAO.addClass(this.btnNext, this.BTN_DISABLED_CLASS);
			if (YAO.ua.ie) {
				this.btnNext.style.backgroundImage = 'none';
			}
			this.btnNext.href = this.oSamples[this.lastIndex];
			this.btnNext.name = this.oSamples[this.lastIndex].id;
		}else{
			this.btnNext.name = this.oSamples[this.lastIndex + 1].id;
		}
	}
};					
YAO.YAlbum.prototype.load = function(path){
	var oImage = new Image(), oDf = document.createDocumentFragment();
	oImage.src = path;
	
	if (oImage.complete) {
		this.resize(oImage);
	}
	else {
		this.isLoading = true;
		this.loadShardow = YAO.Builder.Node('div', {
			id: this.SHARDOW_ID
		});
		this.loadImg = YAO.Builder.Node('img', {
			src: this.LOAD_IMG_PATH,
			id: this.LOAD_IMG_ID
		});
		oDf.appendChild(this.loadShardow);
		if (YAO.ua.ie) {
			this.loadShardow.style.height = this.lastPhotoHeight ? this.lastPhotoHeight + 'px' : this.photoContainer.offsetHeight + 'px';
		}
		oDf.appendChild(this.loadImg);
		this.photoContainer.appendChild(oDf);
		YAO.on(oImage, 'load', function(){
			this.resize(oImage);
		}, oImage, this);
	}
};
YAO.YAlbum.prototype.resize = function(oImage){
	var oSelf = this;
	var width = oImage.width;
	var height = oImage.height;
	var percent = width / height;
	if (width > this.PHOTO_MAX_WIDTH) {
		width = this.PHOTO_MAX_WIDTH;
		height = width / percent;
	}
	if (YAO.ua.ie) {
		this.lastPhotoHeight = height;
		YAO.setStyles(this.btnPrevious, {
			height: height + 'px',
			backgroundImage: oSelf.IMG_BTN_PREVIOUS
		});
		YAO.setStyles(this.btnNext, {
			height: height + 'px',
			backgroundImage: oSelf.IMG_BTN_NEXT
		});
	}
	if (this.lastIndex === 0) {
		YAO.addClass(this.btnPrevious, this.BTN_DISABLED_CLASS);
		if (YAO.ua.ie) {
			this.btnPrevious.style.backgroundImage = 'none';
		}
	}
	if (this.lastIndex === (this.length - 1)) {
		YAO.addClass(this.btnNext, this.BTN_DISABLED_CLASS);
		if (YAO.ua.ie) {
			this.btnNext.style.backgroundImage = 'none';
		}
	}
	this.photoIntro.innerHTML = this.sIntro;
	
	YAO.setStyle(this.photoContainer, 'width', (width + 'px'));
	YAO.setStyles(this.photo, {
		width: width + 'px',
		height: height + 'px'
	});
	if (this.loadImg && this.loadShardow) {
		this.isLoading = false;
		this.photoContainer.removeChild(this.loadImg);
		this.loadImg = null;
		this.photoContainer.removeChild(this.loadShardow);
		this.loadShardow = null;
	}
};
YAO.YAlbum.prototype.Previous = function(){
	if (this.lastIndex !== 0) {
		this.lastIndex -= 1;
		if (YAO.hasClass(this.btnNext, this.BTN_DISABLED_CLASS)) {
			YAO.removeClass(this.btnNext, this.BTN_DISABLED_CLASS);
		}
		if (this.lastIndex >= 1) {
			this.btnPrevious.href = this.oSamples[this.lastIndex - 1].href;
			this.btnPrevious.name = this.oSamples[this.lastIndex - 1].id;
		}
		if (this.lastIndex < 0) {
			this.lastIndex = 0;
			YAO.addClass(this.btnPrevious, this.BTN_DISABLED_CLASS);
		    this.btnPrevious.href = this.oSamples[this.lastIndex].href;
		    this.btnPrevious.name = this.oSamples[this.lastIndex].id;
		}
		this.btnNext.href = this.oSamples[this.lastIndex+1].href;
		this.btnNext.name = this.oSamples[this.lastIndex+1].id;
		this.chgPhoto();
	}
};
YAO.YAlbum.prototype.Next = function(){
	if (this.lastIndex < (this.length - 1)) {
		this.lastIndex += 1;
		if (YAO.hasClass(this.btnPrevious, this.BTN_DISABLED_CLASS)) {
			YAO.removeClass(this.btnPrevious, this.BTN_DISABLED_CLASS);
		}
		if (this.lastIndex <= (this.length - 2)) {
			this.btnNext.href = this.oSamples[this.lastIndex + 1].href;
			this.btnNext.name = this.oSamples[this.lastIndex + 1].id;
		}
		if (this.lastIndex > (this.length - 1)) {
			this.lastIndex = (this.length - 1);
			YAO.addClass(this.btnNext, this.BTN_DISABLED_CLASS);
			this.btnNext.href = this.oSamples[this.lastIndex].href;
			this.btnNext.name = this.oSamples[this.lastIndex].id;
		}
		
		this.btnPrevious.href = this.oSamples[this.lastIndex-1].href;
		this.btnPrevious.name = this.oSamples[this.lastIndex-1].id;
		this.chgPhoto();
	}
};
YAO.YAlbum.prototype.chgPhoto = function(){
	$('.more_active').data('page',1);
	if(this.lastIndex == this.length-1) {
		art.dialog({
			id:'last_photo_tip',
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'最后一张',
			content:$("#lastPic").get(0),
			drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		}).lock();
	}
	var oSelf = this;
	var photo_id = this.oSamples[this.lastIndex].id;
	var photo_li_obj= $("#"+photo_id).parents("li:first") || {};
	var photo_info = oSelf.photo_info_list[photo_id] || {};
	$('#photo_id').val(photo_id);
	if($.isEmptyObject(photo_info)) {
		$.ajax({
			type:"get",
			dataType:"json",
			url:"/Api/Album/getClassPhotoByPhotoId/photo_id/"+photo_id+"/class_code/"+oSelf.class_code+"/client_account/"+oSelf.client_account,
			async:false,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
				}
				photo_info = oSelf.photo_info_list[photo_id] = json.data[photo_id] || {};

			}
		});
	}
	
	$("#photo_name").html(photo_info.name);
	$("#add_date").html(photo_info.add_date);
	if(photo_info.description != '') {
		$("#description").html('<p><span>描述：</span><font id="description_font">'+photo_info.description+'</font></p>');
	}
	
	$("#photo_edit").data('datas',photo_info);
	
	var path = '';
	this.sIntro = this.oSamples[this.lastIndex].title;
	path = this.oSamples[this.lastIndex].href.replace('_s.','.');
	YAO.removeClass(this.lastSample, 'current');
	YAO.addClass(this.oSamples[this.lastIndex], 'current');
	
	this.lastSample = this.oSamples[this.lastIndex];
	this.photo.src = path;
	this.load(path);
	this.scroll();
	
	setTimeout(function() {
		//获取评论列表
		$('#comment_list_div').trigger('loadEvent', [{
			data:{
				photo_id:photo_id
			}
		}]);
	}, 1000);
	//this.getPhotoComments(1);
};
YAO.YAlbum.prototype.scroll = function(){
	var curScreen = Math.ceil(((this.lastIndex + 1) * this.oCarousel.itemWidth) / this.oCarousel.stepWidth) - 1;
	if (curScreen != this.oCarousel.movedNum) {
		this.oCarousel.scroll(curScreen);
		this.oCarousel.movedNum = curScreen;
		if (this.oCarousel.movedNum !== 0 && YAO.hasClass(this.oCarousel.lnkBtnPrevious, this.BTN_DISABLED_CLASS)) {
			YAO.removeClass(this.oCarousel.lnkBtnPrevious, this.BTN_DISABLED_CLASS);
		}
		else {
			if (this.oCarousel.movedNum === 0) {
				YAO.addClass(this.oCarousel.lnkBtnPrevious, this.BTN_DISABLED_CLASS);
			}
		}
		if (this.oCarousel.movedNum !== this.oCarousel.maxMovedNum && YAO.hasClass(this.oCarousel.lnkBtnNext, this.BTN_DISABLED_CLASS)) {
			YAO.removeClass(this.oCarousel.lnkBtnNext, this.BTN_DISABLED_CLASS);
		}
		else {
			if (this.oCarousel.movedNum === this.oCarousel.maxMovedNum) {
				YAO.addClass(this.oCarousel.lnkBtnNext, this.BTN_DISABLED_CLASS);
			}
		}
	}
};
YAO.YAlbum.prototype.delegateEvent=function() {
	var oSelf = this;
	//移动相片
	$('#photo_edit').delegate("#move_evt", 'click', function() {
		var divObj = $(this).parents("div:first");
		
		$("#move_photo_div").data('parentObj',divObj.data("datas"));
		oSelf.getAlbumList();
		
		var btnObj = $(this);
		art.dialog({
			id:'move_photo_dialog',
			follow:btnObj.get(0),
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'移动照片',
			content:$("#move_photo_div").get(0),
			drag: false,
			fixed: false //固定定位 ie 支持不好回默认转成绝对定位
		});
		//$('.aui_close',$(".aui_titleBar")).hide();return false;
		
	});
	
	//相片详情
	$(".xpxq_a").toggle(
		  function () {
		   $("#icon_img").attr('class','icon_up');
		   var btnObj = $(this);
			art.dialog({
				id:'xpxq_dialog',
				follow:btnObj.get(0),
				//background: '#600', // 背景色
				opacity: 0.5,	// 透明度
				title:'相片详情',
				content:$("#xpxq_div").get(0),
				drag:false,
				fixed:false //固定定位 ie 支持不好回默认转成绝对定位
			});
			$('.aui_close',$(".aui_titleBar")).hide();return false;
		  },
		  function () {
			  $("#icon_img").attr('class','icon_down');
			  var dialogObj = art.dialog.list['xpxq_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
		  }
	);
	//移动相片
	$("#move_photo_div").delegate("a", 'click', function(){
		var self = $(this);
		art.dialog({
		    id: 'move',
		    content: '你确定要移动相片到〈'+self.text()+'〉',
		    button: [
		        {
		            name: '确定',
		            callback: function () {
		        		this.close();
			        	var parentObj = self.parents("div:first");
			    		var album_id = self.attr("id");
			    		oSelf.movePhoto(album_id,parentObj);
		                return false;
		            },
		            focus: true
		        },
		        {
		            name: '取消'
		        }
		    ]
		});
	});
	
	//删除相片
	$("#photo_edit").delegate('#del_evt','click',function() {
		$(".tcc_msg_center",$(".tcc_msg")).data('datas', $(this).parents("div:first").data('datas'));
		art.dialog({
			id:'del_photo_dialog',
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'移动照片',
			content:$(".tcc_msg").get(0),
			drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		});
	});
	
	//删除相片 确定
	$(".tcc_msg").delegate('.qd_btn','click',function() {
		var obj = $(this).parents("div:first");
		oSelf.delPhoto(obj);
	});
	//删除相片 取消
	$(".tcc_msg").delegate('.qx_btn','click',function() {
		var dialogObj = art.dialog.list['del_photo_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
	//设置相册封面
	$("#photo_edit").delegate('#set_img_evt','click',function() {
		var obj = $(this).parents("div:first");
		oSelf.setAlbumImg(obj);
	});
	
	//最后一张的提示
	$("body").delegate('#review_photo', 'click', function() {
		var dialogObj = art.dialog.list['last_photo_tip'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		oSelf.lastIndex = 0;
		oSelf.init();
		
	});
	
	//添加相片描述
	$(".photo_name").delegate('#description', 'click', function(){
		var desscriptionObj = $(".description",$(".photo_name"));
		if(desscriptionObj.is(':hidden')) {
			$(".description",$(".photo_name")).show();
			return false;
		}
		$(".description",$(".photo_name")).hide();
	});
	//取消相片描述
	$(".photo_name").delegate('.gray_btn', 'click', function(){
		var desscriptionObj = $(".description",$(".photo_name"));
		if(!desscriptionObj.is(':hidden')) {
			$(".description",$(".photo_name")).hide();
			return false;
		}
	});
	//描述计算器
	$(".photo_name").delegate('.text', 'keypress', function(evt){
		var content = $.trim($(this).val()).toString();
		if(content.length >= oSelf.max_length) {
			var keyCode = evt.keyCode || evt.which;
			//字符超过限制后只有Backspace键能够按
			if(keyCode != 8) {
				$.showError('相片描述不能超过20字!');
				return false;
			}
		}
	});
	$(".photo_name").delegate('.text', 'focus', function(evt){
		oSelf.limitInterval = setInterval(function() {
			oSelf.reflushCounter();
		}, 10);
	});
	$(".photo_name").delegate('.text', 'blur', function(evt){
		clearInterval(oSelf.limitInterval);
	});
	//添加描述
	$(".photo_name").delegate('.green_btn', 'click', function() {
		if($('.text',$('.photo_name')).val() == '') {
			$.showError('描述内容不可为空！');
			return false;
		}
		oSelf.adddescription();
		$(".description",$(".photo_name")).hide();
		$("#description").html('<p><span>描述：</span><font id="description_font">'+$('.text',$('.photo_name')).val()+'</font></p>');
	});
	
	//评论
	$('.comment_reply_selector').live('click', function(){
		$.sendBox();
	});
	
	//评论删除
	$('.comment_delete_selector'). live('click', function(){
		var parentObj = $(this).parents('div:first');
		var pl_info = parentObj.data('datas');
		var comment_id = pl_info.comment_id;
		$.ajax({
			type:"post",
			dataType:"json",
			data:{"comment_id":comment_id},
			url:"/Api/Album/delPhotoCommentByClass",
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
				}
				parentObj.remove();
				$.showSuccess(json.info);
			}
		});
		
	});
	//评论 确定按钮
	 $("#edit_comment_div").delegate('#pl_qd', 'click', function() {
		var parentDivObj = $("#photo_edit");
		var photo_data = parentDivObj.data('datas');
		var photo_id = photo_data.photo_id;
		var content = $.trim($('.textarea', $("#edit_comment_div")).val()).toString();
		var add_uid = $("#login_account").val() || {};
		$.ajax({
			type:"post",
			dataType:"json",
			data:{"photo_id":photo_id,"content":content,"add_uid":add_uid},
			url:"/Api/Album/addCommentByClass",
			success:function(json) {
				var dialogObj = art.dialog.list['edit_comment_div_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
				if(json.status < 0) {
					$.showError(json.info);
				}
				var current_date = oSelf.getCurrentDate();
				var comment_id = json.data;
				var comment_list = {};
				comment_list[comment_id] =
									{"comment_id":comment_id,
									"photo_id":photo_id,
									"content":content,
									"client_account":add_uid,
									"add_date":current_date
									};
				oSelf.fillPhotoComments(comment_list);
				$.showSuccess(json.info);
			}
		});
		
	});
};
//获取当前时间
YAO.YAlbum.prototype.getCurrentDate = function() {
	var myDate = new Date();
	var date_str = '';
	date_str += myDate.getFullYear();    //获取完整的年份(4位,1970-????)
	date_str += '/'+myDate.getMonth();       //获取当前月份(0-11,0代表1月)
	date_str += '/'+myDate.getDate();
	return date_str;
}
//添加相片描述
YAO.YAlbum.prototype.adddescription = function() {
	var content = $.trim($('.text',$('.photo_name')).val()).toString();
	var photo_info = $("#photo_edit").data('datas');
	var photo_id = photo_info.photo_id;
	$.ajax({
		type:"post",
		data:{'photo_id':photo_id,'content':content},
		dataType:"json",
		async:true,
		url:"/Api/Album/updPhotoDescriptionByClass",
		success:function(json) {
			if(json.status<0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
		}
	});
	
};
YAO.YAlbum.prototype.reflushCounter=function() {
	var oSelf = this;
	var context = $('.photo_name');
	
	var len = $.trim($('.text', context).val()).toString().length;
	var show_nums = this.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$("#span_count", context).html(show_nums);
};

//设为封面
YAO.YAlbum.prototype.setAlbumImg=function(obj) {
	var oSelf = this;
	var dlObj = obj || {};
	var photo_datas = dlObj.data('datas') || {};
	var album_img = photo_datas.file_small;
	album_img = album_img || {};
	$.ajax({
		type:"post",
		data:{'album_id':oSelf.album_id,'album_img':album_img},
		dataType:"json",
		async:false,
		url:"/Api/Album/setAlbumImgByClass",
		success:function(json) {
			if(json.status<0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
		}
	});
};
//删除相片
YAO.YAlbum.prototype.delPhoto=function(obj) {
	var oSelf = this;
	var ancestorOb = obj;
	var photo_datas = ancestorOb.data('datas') || {};
	var photo_id = photo_datas.photo_id;
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/Api/Album/delPhotoByClass/class_code/" + oSelf.class_code + "/photo_id/" + photo_id,
		async:true,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			//oSelf.minusPhotoInAlbum();
			var liObj = $("#"+photo_id,$("#samples_list")).parents("li:first");
			liObj.remove();
			var dialogObj = art.dialog.list['del_photo_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
			$.showSuccess(json.info);
			var indexlast = 0;
			if(oSelf.lastIndex < oSelf.length-1) {
				var iniLength = oSelf.length-1;
				oSelf.length = parseInt(iniLength);
				indexlast = oSelf.lastIndex;
			}
			if(oSelf.lastIndex == oSelf.length-1) {
				var iniLength = oSelf.length-1;
				oSelf.length = parseInt(iniLength);
				indexlast = oSelf.lastIndex-1;
			}
			oSelf.lastIndex = parseInt(indexlast);
			oSelf.init();
		}
	});
};
//减少相册中相片数
YAO.YAlbum.prototype.minusPhotoInAlbum=function() {
	var oSelf = this;
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/Api/Album/minusPhotoCountByAlbumId/album_id/" + oSelf.album_id,
		async:false,
		success:function(json) {
			return true;
		}
	});
};
//照片移动
YAO.YAlbum.prototype.getAlbumList=function() {
	var oSelf = this;
	var album_list_tmp = this.album_list || {};
	if($.isEmptyObject(album_list_tmp)) {
		$.ajax({
			type:"get",
			dataType:"json",
			url:"/Api/Album/getOnlyAlbumListByClassCode/class_code/"+oSelf.class_code,
			async:false,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
				}
				album_list_tmp = oSelf.album_list = json.data;
				delete album_list_tmp[oSelf.album_id];
			}
		});
	}
	oSelf.fillAlbumList(album_list_tmp);
	
};
YAO.YAlbum.prototype.fillAlbumList = function (album_list) {
	var oSelf = this;
	var album_list = album_list || {};
	var move_obj = $("#move_photo_div");
	var a_str = "";
	for(var i in album_list) {
		var album_info = album_list[i];
		a_str += '<a id="'+album_info.album_id+'" href="javascript:;"><span>'+album_info.album_name+'</span></a>';
	}
	$("p",move_obj).html('');
	$(a_str).appendTo($("p",move_obj));
};
//移动照片
YAO.YAlbum.prototype.movePhoto=function(album_id, photoObj) {
	var oSelf = this;
	album_id = album_id || {};
	var photo_datas = photoObj.data('parentObj') || {};
	var photo_id = photo_datas.photo_id || {};
	var from_album_id = photo_datas.album_id || {};
	var img_name = photo_datas.file_small || {};
	$.ajax({
		type:"post",
		data:{'to_album_id':album_id,'photo_id':photo_id,'from_album_id':from_album_id,'img_name':img_name},
		dataType:"json",
		url:"/Api/Album/movePhotoByClass",
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			var liObj = $("#"+photo_id,$("#samples_list")).parents("li:first");
			liObj.remove();
			$.showSuccess(json.info);
			var dialogObj = art.dialog.list['move_photo_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
			var indexlast = 0;
			if(oSelf.lastIndex < oSelf.length-1) {
				var iniLength = oSelf.length-1;
				oSelf.length = parseInt(iniLength);
				indexlast = oSelf.lastIndex;
			}
			if(oSelf.lastIndex == oSelf.length-1) {
				var iniLength = oSelf.length-1;
				oSelf.length = parseInt(iniLength);
				indexlast = oSelf.lastIndex-1;
			}
			oSelf.lastIndex = parseInt(indexlast);
			oSelf.init();
		}
	});
};

//一流素材网收藏整理：www.16sucai.com