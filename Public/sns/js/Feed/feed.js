(function($) {
	$.showError=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg || '操作失败!',
			icon:'error'
		}).time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'成功提示',
			content:msg || '操作成功!',
			icon:'succeed'
		}).time(3);
	};
})(jQuery);

//将动态的加载扩展到$全局变量
$.fn.loadFeed=function(settings) {
	this.each(function() {
		elem = this;
		if($.isEmptyObject(elem.feed)) {
			elem.feed = new feed(settings, elem);
		}
	});
	return this;
};

//将动态的加载扩展到$全局变量
$.fn.prependChild=function(feed_datas) {
	this.each(function() {
		elem = this;
		if(!$.isEmptyObject(elem.feed)) {
			elem.feed.prependChild(feed_datas);
		}
	});
	return this;
};


function dump(obj) {
	for(var i in obj)
		alert(i + "=>" + obj[i]);

}

function feed(options, elem) {
	//父级容器对象
	this.$elem = $(elem);
	//表示是否已经初始化
	this.inited = false;
	//是否有下一页
	this.hasNextPage = true;
	this.isFristTimeLoad = true;
	
	this.feedDivObj = {};
	//动态列表容器
	this.feedListDivObj = {};
	//加载更多$对象
	this.loadMoreDivObj = {};
	
	//合并相关配置
	this.settings = this.mergeSettings(options);
	//加载样式文件,可选值：default | mini
	var skin_file_map = {
		'default':'layer_dynamic',
		'mini':'layer_dynamic_02'
	};
	this.loadCss(skin_file_map[this.settings['skin']]);
	//加载模板信息
	this.loadTemplate();
	//创建div容器对象
	this.createContain(elem);
	
	this.loadFeed();
};

//动态相关的默认设置
feed.defaults = {
	template:'/Sns/Feed/List/loadFeedTemplateAjax',
	skin:'default'
};

//整个页面的模板信息
feed.templates = [];

feed.prototype = {
	//合并设置信息，优先满足用户设置信息
	mergeSettings:function(options) {
		var me = this;
		me.settings = $.extend({}, options || {});
		for(var i in feed.defaults) {
			if(me.settings[i]) {
				continue;
			}
			me.settings[i] = feed.defaults[i];
		}
		return me.settings;
	}
	
	//加载样式文件
	,loadCss:function(skin) {
		if(!skin) {
			return false;
		}
		
		//加载样式文件
		var cssHref = "/Public/sns/css/Common/" + skin + ".css";
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
	}
	
	//创建相关的容器
	,createContain:function() {
		var me = this;
		
		me.feedDivObj = $('#feed_div').clone().removeAttr('id').show();
		me.feedListDivObj = $('#feed_list_div', me.feedDivObj);
		me.loadMoreDivObj = $('#load_more_div', me.feedDivObj);
		//将对象追加到父级容器
		me.$elem.append(me.feedDivObj);
		//绑定元素的事件
		$('#load_more_feed_a', me.loadMoreDivObj).click(function() {
			if(!me.hasNextPage) {
				return false;
			}
			var last_id = $(this).data('last_id') || 0;
			me.loadFeed(last_id);
			return false;
		});
	}
	
	//加载动态的模板信息
	,loadTemplate:function() {
		var me = this;
		//加载模板信息
		if($.inArray(me.settings['template'], feed.templates) < 0) {
			$.ajax({
				url:me.settings['template'],
				dataType:'html',
				async:false,
				success:function(html) {
					$('body').append($(html));
				}
			});
			feed.templates.push(me.settings['template']);
		}
	}
	
	//加载动态信息
	,loadFeed:function(last_id) {
		var me = this;
		
		last_id = last_id || 0;
		$.ajax({
			type:'get',
			url:me.settings['url'] + "/last_id/" + last_id,
			dataType:'json',
			async:false,
			success:function(json) {
				var rs_list = json.data || {};
				
				var feed_list = rs_list.feed_list || {};
				var last_id = rs_list.last_id || 0;
				
				if($.isEmptyObject(feed_list)) {
					me.hasNextPage = false;
					$('#load_more_feed_a', me.loadMoreDivObj).parent().hide();
					//如果第一次加载没有拿到动态信息
					if(me.isFristTimeLoad) {
						$("<span style='padding:10px 0;display:block;text-align:center;clear:both;'>" + json.info + "</span>").appendTo(me.$elem);
					}
					
					return false;
				}
				
				me.isFristTimeLoad = false;
				//通知加载更多信息是的last_id参数
				$('#load_more_feed_a', me.loadMoreDivObj).parent().show();
				$('#load_more_feed_a', me.loadMoreDivObj).data('last_id', last_id);
				//填充动态信息
				for(var i in feed_list) {
					me.createChild(feed_list[i] || {});
				}
			}
		});
	}
	
	//创建一个孩子节点
	,createChild:function(feed_datas) {
		var me = this;
		feed_datas = feed_datas || {};
		var feed_type_maps = {
			1 : 'mood',
			2 : 'blog',
			3 : 'photo'
		};
		
		var feed_type = feed_datas.feed_type || 1;
		var divObj = $.createFeedUnit(feed_datas, feed_type_maps[feed_type], me.settings['skin']);
		divObj.data('datas', feed_datas);
		me.feedListDivObj.append(divObj);
	}
	
	//添加一个子节点到最前面
	,prependChild:function(feed_list) {
		
		var me = this;
		for(var i in feed_list) {
			var feed_datas = feed_list[i] || {};

			var feed_type_maps = {
				1 : 'mood',
				2 : 'blog',
				3 : 'photo'
			};
			
			var feed_type = feed_datas.feed_type || 1;
			var divObj = $.createFeedUnit(feed_datas, feed_type_maps[feed_type], me.settings['skin']);
			divObj.data('datas', feed_datas);
			me.feedListDivObj.prepend(divObj);
		}
	}
};