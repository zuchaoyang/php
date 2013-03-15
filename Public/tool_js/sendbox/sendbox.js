(function($) {
	//简单的模板渲染
	$.fn.simpleRender=function(datas) {
		datas = datas || {};
		//判断页面是否渲染过
		var html = this.html().toString().replace(/\{([^\}]+?)\}/ig, function(a, b) {
			return datas[b] || "";
		});
		this.html(html);
		return this;
	};
	
})(jQuery);

var Util; // 实用工具类
Util = {

    ltrim: function (text) {
        return text == null ? 
                "" :
                text.toString().replace(/^\s+/ ,"")
    }

   ,rtrim: function (text) {
        return text == null ? 
                "" :
                text.toString().replace(/\s+$/ ,"")
   }

   ,trim: function (text) {
       return this.ltrim(this.rtrim(text));
   }

   ,msglen: function (text) { // 微博字数计算规则 汉字 1 英文 0.5 网址 11 除去首尾空白
        text = text.replace(new RegExp("((news|telnet|nttp|file|http|ftp|https)://){1}(([-A-Za-z0-9]+(\\.[-A-Za-z0-9]+)*(\\.[-A-Za-z]{2,5}))|([0-9]{1,3}(\\.[0-9]{1,3}){3}))(:[0-9]*)?(/[-A-Za-z0-9_\\$\\.\\+\\!\\*\\(\\),;:@&=\\?/~\\#\\%]*)*","gi"),'填充填充填充填充填充填');
        return Math.ceil(($.trim(text.replace(/[^\u0000-\u00ff]/g,"aa")).length)/2);
   }

   ,limit: function (text ,max ,suff) {
        if (text == null) {
            return "";
        }
        text = text.toString();
        suff = suff || "...";
        if (text.length <= max) {
            return text;
        } else {
            return text.substring(0,max) + suff;
        }
   }

   ,limitFileName: function (filename,max) {
        max = max || 10;
        var fileExt;
        var fileNameArr = filename.split(".");
        if (fileNameArr.length > 1) {
            fileExt = fileNameArr.splice(-1);
            return this.limit(fileNameArr.join("") ,max ,"") + "." + fileExt.join("");
        } else {
            return this.limit(fileNameArr[0] ,max ,"");
        }
   }

   ,timedesc: function (nowtime,timestamp) {
       var diff = nowtime - timestamp;
       var nowtimeDate = new Date(nowtime*1000);
       var previousDayDate = new Date( (nowtime - 24 * 3600) * 1000 );
       var timestampDate = new Date(timestamp*1000);
       var timestampYear = timestampDate.getFullYear();
       var timestampMonth = timestampDate.getMonth();
       var timestampDay = timestampDate.getDate();
       var timestampHours = timestampDate.getHours()<10?"0"+timestampDate.getHours():timestampDate.getHours();
       var timestampMinutes = timestampDate.getMinutes()<10?"0"+timestampDate.getMinutes():timestampDate.getMinutes();
       
       if(diff < 60){
           return "刚刚";
       }else if(diff < 3600){
           return Math.floor(diff/60)+"分钟前";
       }
       
       if(nowtimeDate.getFullYear() == timestampYear){
           if(nowtimeDate.getMonth() == timestampMonth){
               if(nowtimeDate.getDate() == timestampDay){
                   return "今天"+timestampHours+":"+timestampMinutes;
               }else if( previousDayDate.getDate() == timestampDay ){
                   return "昨天"+timestampHours+":"+timestampMinutes;
               }else{
                   return [timestampMonth+1,"月",timestampDay,"日"," ",timestampHours,":",timestampMinutes].join("");
               }
           }else{
               return [timestampMonth+1,"月",timestampDay,"日"," ",timestampHours,":",timestampMinutes].join("");
           }
       }else{
           return [timestampYear,"年",timestampMonth+1,"月",timestampDay,"日"," ",timestampHours,":",timestampMinutes].join("");
       }
   }

   // 向text holder插入指定的文本后高亮选中，若已有指定的文本则只高亮

   ,highlightOrInsert: function (text ,holder ,trailer ,suff) {
       trailer = trailer || "#";
       suff = suff || "";
       var range;
       var holderText = holder.value || "";
       var start = holderText.lastIndexOf(text);
       var end = start + text.length;
       var ht = [];
	   if(holder.caret>=0){
	   ht = [holderText.substring(0,holder.caret),holderText.substring(holder.caret)];	   
	   } else {
	   ht = [holderText,""];	   
	   }
       // 插入文字
       if (start < 0) {
           holder.value = [ht[0],trailer ,text ,trailer ,ht[1]," " ,suff].join("");
           start = holder.value.lastIndexOf(text);
           end = start + text.length;
       } else {
       	   holder.value = [holderText ," ",suff].join("");
       }
       	   
       // 高亮文字
       if (document.createRange) {
            holder.setSelectionRange( start, end );
            holder.focus();
       } else {
            range = holder.createTextRange();
            range.collapse(true);
            range.moveStart( 'character', start );
            range.moveEnd( 'character', end - start );
            range.select();
       }
   }
   
   // 输入框中指定位置插入文本
   ,insertText: function (text ,caret ,holder) {

       var pre;
       var suff;
       var holderText;

       caret = caret || 0;

       if (holder.nodeName) {
           holder = $(holder);
       }

       holderText = holder.val();
       pre = holderText.substr(0,caret);
       suff = holderText.substr(caret);
       holderText = [pre,text,suff].join("");
       holder.val(holderText);
       holder.focus();
       holder.cursorPos([pre,text].join("").length);
   }
};

/**
 * 功能点:
 * 1. 一个页面支持多个实例化对象；
 * 2. 将对应的功能扩展到jQuery对象上；
 * 3. 支持工具的动态扩展；
 * 4. 页面涉及到的html代码使用内置js的方式实现，避免多次加载；
 * 5. 需要实现表情、图片上传、表单的提交等相关的功能；
 * 
 * 
 * 难点：
 * 1. 事先评估类的静态属性和动态属性；
 * 2. 插件的扩展方式的实现；
 * 3. 插件功能扩展的时候，事件响应都走委托；
 * @return
 */
(function($) {

//获取js的存放路径
var src = $('script[src*="sendbox.js"]:first').attr('src').toString();
var sendBoxRoot = src.substring(0, src.indexOf('sendbox.js') - 1);

//解析sendbox中url路径中附带的参数
var pos = -1,
    params = {};
if((pos = src.indexOf('?')) >= 0) {
	var arr = src.substring(pos + 1, src.length).split('&');
	for(var i in arr) {
		var s = arr[i].toString().split('=');
		params[s[0]] = s[1];
	}
}

function sendBox(textarea, options) {
	var me = this;
	if(!textarea) {
		return false;
	} else if(!this.checkOptions(options)) { //检测必要参数的完整
		return false;
	}
	
	//全局初始化
	sendBox.globalInit();
	//初始化函数
	this.init(textarea, options);
};

sendBox.prototype = {
	//默认设置
	defaults:{
		chars:140,
		file_size:2,
		skin:'default'
	},
	
	//sendbox的容器的额外大小,单位：px
	frame_size:12,
	
	//原始的textarea对象
	_jTextarea : {},
	
	//jForm对象
	_jForm:{},
	
	//div容器对象
	_jDivObj:{},
	
	//表单提交前参数
	params : {},
	
	//初始化函数
	init:function(textarea, options) {
		//合并默认参数信息
		options = this.mergeOptions(options || {});
		//加载样式信息
		this.loadCss(options['skin']);
		//创建相关的元素信息
		this.createElem(textarea, options);
		//创建工具栏
		this.createPanel(options.panels);
		//绑定textarea相关的事件
		this.attachEventForTextarea(options);
		//绑定form表单相关的事件
		this.attachEventForForm(options);
	},
	
	//合并函数,自定义参数优先级高于默认参数
	mergeOptions:function(options) {
		for(var name in this.defaults) {
			if(options[name]) {
				continue;
			}
			options[name] = this.defaults[name];
		}
		return options;
	},
	
	//创建相应的元素对象
	createElem:function(textarea, options) {
		//原始的textarea元素
		this._jTextarea = $(textarea).hide();
		//创建sendBox对象
		var divObj = $('.sendbox_selector').clone().removeClass('sendbox_selector').insertAfter(this._jTextarea).simpleRender(options || {});
		this._jDivObj = $(divObj).show();
		//新生成的form元素
		this._jForm = $('form:first', this._jDivObj);
		
		//初始化内部的textarea的大小
		$('textarea', this._jForm).css({
			width:(this._jTextarea.outerWidth() - this.frame_size) + 'px',
			height:this._jTextarea.outerHeight() + 'px'
		});
	},
	
	//销毁对象
	destory:function() {
		this._jTextarea.show();
		this._jDivObj.remove();
	},
	
	//加载样式信息，一个页面可能存在不同的编辑框,需要定制加载不同的css文件
	loadCss:function(skin) {
		if(!skin) {
			return false;
		}
		
		var cssHref = sendBoxRoot + "/skins/" + skin + ".css";
		//加载样式文件
		if($('link[href*="' + cssHref + '"]').length == 0) {
			//IE浏览器下的Css文件的动态加载问题
			if(document.createStyleSheet) {
				document.createStyleSheet(cssHref);
			} else {
				$('<link></link>').attr({
					rel:'stylesheet',
					href:cssHref,
					type:'text/css'
				}).appendTo($('head'));
			}
		}
	},
	
	//创建工具栏
	createPanel:function(panels) {
		var panelObj = $('.actionlist', this._jForm);
		var arr = panels.toString().split(',');
		for(var i in arr) {
			$('.' + arr[i] + '_selector', panelObj).show();
		}
	},
	
	//附加表单提交时的额外参数
	appendParams:function(params) {
		this.params = $.extend({}, this.params, params || {});
	},
	
	//设置元素内容
	setSource:function(value) {
		$('textarea', this._jForm).val(value);
		return this;
	},

	//获取元素内容
	getSource:function() {
		return $('textarea', this._jForm).val();
	},

	//隐藏当前的编辑框
	hide:function() {
		this._jDivObj.hide();
	},

	//显示当前编辑框
	show:function() {
		this._jDivObj.show();
	},
	
	//检测配置是否完整
	checkOptions:function(options) {
		var required_params = ['url'];
		for(var i in required_params) {
			var param = required_params[i];
			if(!options[param]) {
				alert('配置参数:' + param + "是必须的!");
				return false;
			}
		}
		return true;
	},

	//编辑框获取焦点
	focus:function() {
		$('textarea:first', this._jForm).focus();
	},
	
	//班级器相关的事件
	attachEventForTextarea:function(options) {
		var me = this;
		//绑定相关的事件
		var contentInterval;
		//获取text的内容
		function getContent() {
			return $.trim($('textarea', me._jForm).val());
		};
		//绑定textarea的相关事件
		$('textarea', me._jForm).keypress(function(evt) {
			var content = getContent();
			if(content.length > options.chars) {
				var keyCode = evt.keyCode || evt.which;
				//字符超过限制后只有Backspace键能够按
				if(keyCode != 8) {
					$.showError('公告内容不能超过' + options.chars + '字!');
					return false;
				}
			}
		}).focus(function() {
			contentInterval = setInterval(function() {
				var content = getContent();
				$('#sendmsgtip', me._jForm).find('big').html(options.chars - content.length);
			}, 200);
		}).blur(function() {
			clearInterval(contentInterval);
		});
	},
	
	//绑定表单相关的事件
	attachEventForForm:function(options) {
		var me = this;
		//初始化系统的附加参数
		if(!$.isEmptyObject(options.data)) {
			for(var name in options.data) {
				$('<input type="hidden" name="' + name + '" value="' + options.data[name] + '"/>').appendTo(me._jForm);
			}
		}
		
		//处理提交前的操作
		function beforeSubmit() {
			if(typeof options.beforeSubmit == 'function' && !options.beforeSubmit()) {
				return false;
			}
			
			//将后续追加的参数附加到form表单
			if(!$.isEmptyObject(me.params)) {
				for(var name in me.params) {
					if($(':input[name="' + name + '"]', me._jForm).length == 0) {
						$('<input type="hidden" name="' + name + '" value="' + me.params[name] + '"/>').appendTo(me._jForm);
					} else {
						$(':input[name="' + name + '"]', me._jForm).val(me.params[name]);
					}
				}
			}
			//将编辑后的值回写到原始的textarea上
			me._jTextarea.val(me.getSource());
			return true;
		};
		
		//绑定form表单的相关事件
		me._jForm.submit(function() {
			//使用ajax提交表单
			if(!beforeSubmit()) {
				return false;
			}
			$(this).ajaxSubmit({
				type:options.type || 'post',
				url:options.url,
				dataType:options.dataType || 'json',
				success:function(json) {
					if(typeof options.success == 'function') {
						options.success(json);
					}
					//重置表单信息
					$('textarea', me._jForm).val('');
					//重置文件上传的状态
					$('.upload_file_cancel_selector', me._jForm).trigger('resetEvent');
				}
			});
			return false;
		});
		
		//提交按钮
		$('.sendbtn', me._jForm).click(function() {
			me._jForm.submit();
		});
	}
	
};

sendBox.extend=function(copy) {
	if($.isEmptyObject(copy)) {
		return;
	}
	for(var name in copy) {
		sendBox[name] = copy[name];
	}
	return true;
};

sendBox.extend({
	globalInited:false,
	
	globalInitList:[],
	
	//全局初始化函数
	globalInit:function() {
		if(sendBox.globalInited) {
			return;
		}
		for(var i in sendBox.globalInitList) {
			sendBox.globalInitList[i].call();
		}
		sendBox.globalInited = true;
	},
	
	//注册到全局的初始化函数
	registorGlobalInit:function(initFunc) {
		if(typeof initFunc == 'function') {
			sendBox.globalInitList.push(initFunc);
		}
	}
});

//加载必要文件jquery.form.js
sendBox.registorGlobalInit(function() {
	//加载模板信息
	$.ajax({
		type:'get',
		url:sendBoxRoot + "/sendbox.html",
		dataType:'html',
		async:false,
		success:function(html) {
			$('body').append(html);
		}
	});
	//加载jQuery.form.js
	if($('script[src*="jquery.form"]').length == 0) {
		$('<script></script>').attr({
			type:'text/javascript',
			src:sendBoxRoot + '/plugins/jquery.form.min.js'
		}).appendTo($('head'));
	}
	//加载jquery.range.js
	if($('script[src*="jquery.range.js"]').length == 0) {
		$('<script></script>').attr({
			type:'text/javascript',
			src:sendBoxRoot + '/plugins/jquery.range.js'
		}).appendTo($('head'));
	}
});

$.fn.sendBox=function(options, reset) {
	var successArr = [];
	this.each(function() {
		var elem = $(this).get(0);
		//重置sendbox
		if(typeof reset == 'boolean' && reset) {
			if(!$.isEmptyObject(elem.sendbox)) {
				elem.sendbox.destory();
				elem.sendbox = {};
			}
		}
		
		//避免重复初始化
		if(!$.isEmptyObject(elem.sendbox)) {
			return elem.sendbox;
		}
		//只支持textarea元素
		if(!$.nodeName(elem, 'TEXTAREA')) {
			return false;
		}
		var _sendbox = new sendBox(this, options);
		elem.sendbox = _sendbox;
		successArr.push(_sendbox);
	});
	
	if(successArr.length > 0) {
		return successArr[0];
	}
	
	return false;
};

var oldVal=$.fn.val;
$.fn.val=function(value) {
	var _this = this;
	var sendbox;
	//读操作
	if(value == undefined) {
		if(_this[0] && (sendbox = _this[0].sendbox)) {
			return sendbox.getSource();
		} else {
			return oldVal.call(_this);
		}
	}
	//写操作
	return _this.each(function() {
		if(senbox = this.sendbox) {
			senbox.setSource(value);
		} else {
			oldVal.call($(this), value);
		}
	});
};

//注册表情相关
(function() {
	//事件委托
	sendBox.registorGlobalInit(function() {
		$('.iwbEmotesBtn').live('click', function () {
	        $(".iwbAutoCloseLayer").hide();
	        var emotesBtn = $(this);
	        
	        var top = emotesBtn.offset().top + emotesBtn.height();
	        var left = emotesBtn.offset().left;
	        
	        $('.iwbQQFace').trigger('openEvent', [{
	        	top:top,
	        	left:left,
	        	callback:function(title) {
		        	var formObj = emotesBtn.closest('form');
			        var targetInput = $('textarea:first', formObj);
		            targetInput.trigger("focus");
		            if (targetInput.length > 0){
		                caret = (targetInput.get(0).caret !== undefined ? targetInput.get(0).caret : targetInput.val().length) ;
		                Util.insertText("/" + title ,caret ,targetInput);
		                targetInput.trigger("keyup");
		            }
	        	}
	        }]);
    	});
	});
	
	//注册到全局初始化函数,初始化表层浮动层
	sendBox.registorGlobalInit(function() {
		  function createEmotion() {
			  var emotions="f14|微笑,f1|撇嘴,f2|色,f3|发呆,f4|得意,f5|流泪,f6|害羞,f7|闭嘴,f8|睡,f9|大哭,f10|尴尬,f11|发怒,f12|调皮,f13|呲牙,f0|惊讶,f15|难过,f16|酷,f96|冷汗,f18|抓狂,f19|吐,f20|偷笑,f21|可爱,f22|白眼,f23|傲慢,f24|饥饿,f25|困,f26|惊恐,f27|流汗,f28|憨笑,f29|大兵,f30|奋斗,f31|咒骂,f32|疑问,f33|嘘,f34|晕,f35|折磨,f36|衰,f37|骷髅,f38|敲打,f39|再见,f97|擦汗,f98|抠鼻,f99|鼓掌,f100|糗大了,f101|坏笑,f102|左哼哼,f103|右哼哼,f104|哈欠,f105|鄙视,f106|委屈,f107|快哭了,f108|阴险,f109|亲亲,f110|吓,f111|可怜,f112|菜刀,f89|西瓜,f113|啤酒,f114|篮球,f115|乒乓,f60|咖啡,f61|饭,f46|猪头,f63|玫瑰,f64|凋谢,f116|示爱,f66|爱心,f67|心碎,f53|蛋糕,f54|闪电,f55|炸弹,f56|刀,f57|足球,f117|瓢虫,f59|便便,f75|月亮,f74|太阳,f69|礼物,f49|拥抱,f76|强,f77|弱,f78|握手,f79|胜利,f118|抱拳,f119|勾引,f120|拳头,f121|差劲,f122|爱你,f123|NO,f124|OK,f42|爱情,f85|飞吻,f43|跳跳,f41|发抖,f86|怄火,f125|转圈,f126|磕头,f127|回头,f128|跳绳,f129|挥手,f130|激动,f131|街舞,f132|献吻,f133|左太极,f134|右太极";
			  var emotionsArr = emotions.split(",");
			  var emotionsHtml = "<div class=\"iwbAutoCloseLayer iwbQQFace\">"
	                        +"<a href=\"javascript:void(0);\" class=\"close\" title=\"关闭\"></a>"
	                        +"<div class=\"qqFaceBox\">";
			  var i;
			  for (i=0,l=emotionsArr.length;i<l;i++){
				  var temp = emotionsArr[i].split("|");
				  emotionsHtml += ("<a href=\"javascript:void(0);\" data-code=\"" + temp[0] +"\" title=\"" + temp[1] +"\"></a>");
			  }
			  emotionsHtml += ("<div class=\"qqFacePreview\"><div class=\"qqFacePreviewImg\"><img src=\"\" alt=\"表情\"/></div><div class=\"qqFacePreviewText\">测试</div></div></div></div>");
			  
			  return $(emotionsHtml);
		  }
	      $('body').append(createEmotion());
	      
	      //事件处理
	      $('.iwbQQFace').live({
			openEvent:function(evt, options) {
				options = options || {};
				var top = options.top || 0;
				var left = options.left || 0;
			 	var iwbQQFace = $(".iwbQQFace");
		        iwbQQFace.css({
		            top: top + 5 + "px",
		            left: left - 60 + "px"
		        });
		        iwbQQFace.hide().fadeIn(200);
		        iwbQQFace.data('callback', options.callback || $.noop);
			}
		});
	    //防止表情层的点击事件的冒泡
	  	$('.iwbQQFace').live('click', function(evt) {
			evt.stopPropagation();
		});
		// 关闭按钮事件
		$('.iwbQQFace .close').live('click', function() {
			$(this).parents('.iwbQQFace:first').fadeOut(200);
		});
		//表情a元素的点击事件
		$('.iwbQQFace .qqFaceBox a').live({
			mouseover:function() {
				var faceBtn = $(this);
				// 每行15个表情，我们使用余数来确定左右区域位置
	            var faceBtnIndex = faceBtn.index() + 1;
	            faceBtnIndex = faceBtnIndex % 15;
	            if (faceBtnIndex===0) {
	                faceBtnIndex = 15;
	            }
	            
	            var previewUrl = sendBoxRoot + '/images/emotions/' + faceBtn.attr("data-code").match(/\d+$/)[0] + ".gif";
	            var iwbQQFacePreview = $(this).parents('.iwbQQFace:first').find(".qqFacePreview");
	            iwbQQFacePreview.find("img").attr("src", previewUrl);
	            iwbQQFacePreview.find(".qqFacePreviewText").text(faceBtn.attr("title"));
	            iwbQQFacePreview.css({"left":"","right":""});
	            iwbQQFacePreview.css(faceBtnIndex > 8 ? "left":"right","0px");
	            iwbQQFacePreview.show();
			},
			
			click:function() {
				var title = $(this).attr("title");
				var iwbQQFace = $(".iwbQQFace");
				var callback = iwbQQFace.data('callback') || $.noop;
				if(typeof callback == 'function') {
					callback(title);
				}
	            iwbQQFace.hide();
			}
		});
		
		// 关闭预览
        $('.qqFaceBox').live('mouseout', function() {
        	 $('.qqFacePreview', $(this).parents('.iwbQQFace:first')).hide();
        });
	      
	});
	
})();

//扩展文件上传
(function() {
	//绑定照片的相关事件
	sendBox.registorGlobalInit(function() {
		$('#uploadPic').live('change', function() {
			var fileObj = $(this);
			var picPath = fileObj.val();
			if(!picPath) {
				return;
			}
			if (!picPath.match(/(\.jpg|\.jpeg|\.gif|\.png)$/i)) {
	            $.showError("请选择jpg、jpeg、gif、png格式，文件小于2M");
	            return;
	        }
			
			var divObj = fileObj.closest('div');
			var picField = $("#zhaopiantxt", divObj).hide();
			
			var cancelPic = $("<span class=\"del upload_file_cancel_selector\" title=\"删除\"></span>").css('background-image', "url(" + sendBoxRoot + "/images/delete_btn.gif" + ")");
			$("<span class=\"gray\">"+Util.limitFileName(picPath.match(/[^\/\\]+$/)[0],10)+"</span>").appendTo(divObj);
			divObj.append(cancelPic);
			
			var textObj = $('textarea:first', $(this).closest('form'));
			//绑定删除按钮的相关事件
			cancelPic.bind('click resetEvent', function() {
				fileObj.clone().insertAfter(fileObj).val('');
	            fileObj.remove();
	            cancelPic.remove();
	            $('.gray', divObj).remove();
	            
	            picField.show();
	            textObj.val(textObj.val().replace(new RegExp("(\\s*#分享照片#\\s*)","g"),"")).trigger("keyup");
			});
			
			Util.highlightOrInsert("分享照片", textObj.get(0));
			textObj.trigger("keyup");
		});
		
	});
})();

})(jQuery);