(function($) {
	$.showError=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg || '操作失败!',
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'成功提示',
			content:msg || '操作成功!',
			icon:'succeed'
		}).lock().time(3);
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


function dump(obj) {
	for(var i in obj)
		alert(i + "=>" + obj[i]);

}

function feed(options, elem) {
	this.init(options, elem);
	this.loadFeed(1);
};

//动态相关的默认设置
feed.defaults = {
	template:'/Sns/Feed/List/loadFeedTemplateAjax'
};

//整个页面的模板信息
feed.templates = [];

feed.prototype = {
	//父级容器对象
	$elem : {}
	
	//表示是否已经初始化
	,inited:false
	
	//是否有下一页
	,hasNextPage : true
	
	//相关的设置
	,settings: {}
	
	//动态列表容器
	,feedListDivObj : {}
	
	//加载更多$对象
	,loadMoreDivObj:{}
	
	/**
	 * 初始化
	 * 1. 主要在于模板加载;如何避免模板的重复加载
	 * 2. 以及相关的设置的处理,
	 * 3. div对象的创建
	 * @return
	 */
	,init:function(options, elem) {
		var me = this;
		//合并相关配置
		this.mergeSettings(options);
		//加载模板信息
		this.loadTemplate();
		//创建div容器对象
		this.createContain(elem);
	}
	
	//合并设置信息，优先满足用户设置信息
	,mergeSettings:function(options) {
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
	
	//创建相关的容器
	,createContain:function(elem) {
		var me = this;
		me.$elem = $(elem);
		me.feedListDivObj = $('#feed_list_div').clone().removeAttr('id').show();
		me.loadMoreDivObj = $('#load_more_div').clone().removeAttr('id').show();
		//将对象追加到父级容器
		me.$elem.append(me.feedListDivObj);
		me.$elem.append(me.loadMoreDivObj);
		//绑定元素的事件
		$('#load_more_feed_a', me.loadMoreDivObj).click(function() {
			if(!me.hasNextPage) {
				return false;
			}
			
			var page = $(this).data('page') || 1;
			me.loadFeed(page + 1);
			$(this).data('page', page + 1);
			
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
	,loadFeed:function(page) {
		var me = this;
		page = page >= 1 ? page : 1;
		$.ajax({
			type:'get',
			url:me.settings['url'] + "/page/" + page,
			dataType:'json',
			success:function(json) {
				var feed_list = json.data || {};
				if($.isEmptyObject(feed_list)) {
					me.hasNextPage = false;
					return false;
				}
				
				//填充动态信息
				for(var i in feed_list) {
					var feed_datas = feed_list[i] || {};
					
					var divObj = $.createFeedUnit(feed_datas);
					
					divObj.data('datas', feed_datas);
					me.feedListDivObj.append(divObj);
				}
			}
		});
	}
};

$(document).ready(function() {
	          
	$('#show_feed').loadFeed({
		url:'/Sns/Feed/List/getUserAllFeedAjax'
	});
});
