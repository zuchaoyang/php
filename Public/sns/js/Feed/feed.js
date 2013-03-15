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


function dump(obj) {
	for(var i in obj)
		alert(i + "=>" + obj[i]);

}

//工具类
var feed_tool = {
	collectParams:function(divObj) {
		var params = {
			post_params:{}
		};
		$(':input', divObj).each(function() {
			var inpObj = $(this);
			var name = inpObj.attr('name');
			var val = inpObj.val();
			params[name] = val;
			if(inpObj.parent().is('.post_selector')) {
				params.post_params[name] = val;
			}
		});
		return params;
	}
};

function feed(options, elem) {
	this.init(options, elem);
	this.loadFeed(1);
};

//标示是否进行了全局初始化
feed.globalInited = false;

//动态相关的默认设置
feed.defaults = {
	template:'/Sns/Feed/List/loadFeedTemplateAjax'
};

//整个页面的模板信息
feed.templates = [];

//全局待初始化函数列表
feed.initFunctions = [];
feed.register=function(fn) {
	feed.initFunctions.push(fn || $.noop);
};

/**
 * 动态相关的全局初始化函数
 * 1. 事件的委托
 * 2. 加载子模板信息
 * @return
 */
feed.globalInit=function() {
	if(feed.globalInited) {
		return true;
	}
	for(var name in feed.initFunctions) {
		feed.initFunctions[name].call();
	}
	feed.globalInited = true;
};

feed.prototype = {
	feed_type_map:{
		1:'mood',
		2:'blog',
		3:'photo'
	},
	//父级容器对象
	$elem : {},
	//表示是否已经初始化
	inited:false,
	
	//是否有下一页
	hasNextPage : true,
	
	//相关的设置
	settings: {},
	
	//动态列表容器
	feedListDivObj : {},
	
	//加载更多$对象
	loadMoreDivObj:{},
	
	/**
	 * 初始化
	 * 1. 主要在于模板加载;如何避免模板的重复加载
	 * 2. 以及相关的设置的处理,
	 * 3. div对象的创建
	 * @return
	 */
	init:function(options, elem) {
		var me = this;
		//合并相关配置
		this.mergeSettings(options);
		//加载模板信息
		this.loadTemplate();
		//初始化全局配置信息,要在模板加载完成之后进行相应的事件委托
		feed.globalInit();
		//创建div容器对象
		this.createContain(elem);
	},
	
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
	},
	
	//创建相关的容器
	createContain:function(elem) {
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
	},
	
	//加载动态的模板信息
	loadTemplate:function() {
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
	},
	
	//加载动态信息
	loadFeed:function(page) {
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
				
				alert(me.feedListDivObj.length);
				
				//填充动态信息
				for(var i in feed_list) {
					var feed_datas = feed_list[i] || {};
					//获取对应的动态的名称信息
					var feed_type_name = me.feed_type_map[feed_datas.feed_type] || me.feed_type_map[1];
					var divObj = feed_unit.createByFeedType(feed_datas, feed_type_name);
					
					alert(divObj.length);
					
					divObj.data('datas', feed_datas);
					me.feedListDivObj.append(divObj);
				}
			}
		});
	}
};

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

//评论相关的事件需要集中处理
function comment_events() {
	this.delegateEventForFeedUnit();
	this.delegateEventForComment1stUnit();
	this.delegateEventForComment2ndUnit();
}

feed.register(function() {
	new comment_events();
});

comment_events.prototype = {
	delegateEventForFeedUnit:function() {
		//删除按钮
		$('.feed_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.feed_unit_selector');
			var aObj = $(this);
			
			var params = feed_tool.collectParams($('.feed_unit_params_selector', ancestorObj));
			//触发删除相关的事件
			$('#feed_delete_div').trigger('openEvent', [{
				datas : params,
				follow:aObj.get(0),
				callback:function() {
					$(ancestorObj[0].followDiv).remove();
					ancestorObj.remove();
				}
			}]);
		});
		
		//评论按钮, 点击有切换的效果
		$('.feed_comment_selector').live('click', function() {
			
			//alert('call me here create comment_div');
			
			var ancestorObj = $(this).closest('.feed_unit_selector');
			var aObj = $(this);
			
			alert(ancestorObj[0].outerHTML);
			alert($('.feed_unit_params_selector', ancestorObj)[0].outerHTML);
			
			var params = feed_tool.collectParams($('.feed_unit_params_selector', ancestorObj));
			
			dump(params);
			
			
			var toggled_nums = aObj.data('toggled_nums') || 1;
			if(toggled_nums == 1) {
				feed_comment.createByFeedType(params.feed_type_name, params.from_id, function(divObj) {
					divObj.insertAfter(ancestorObj);
					ancestorObj[0].followDiv = divObj;
				});
			}
			
			if(toggled_nums % 2 == 0) {
				$(ancestorObj[0].followDiv).hide();
			} else {
				$(ancestorObj[0].followDiv).show();
			}
			aObj.data('toggled_nums', toggled_nums + 1);
		});
	}
	
	//comment相关的事件
	,delegateEventForComment1stUnit:function() {
		//一级评论的删除事件
		$('.comment_1st_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var params = feed_tool.collectParams($('.comment_1st_unit_params_selector', ancestorObj));
			$.ajax({
				type:'get',
				url:params.delete_comment_url,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					ancestorObj.remove();
				}
			});
		});
		
		//一级评论的回复事件,多次点击有切换功能
		$('.comment_1st_reply_selector').live('click', function() {
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var reply2ndObj = $('.reply_2nd_selector', ancestorObj);
			
			var params = feed_tool.collectParams($('.comment_1st_unit_params_selector', ancestorObj));
			//第一次点击的时候初始化相应的sendbox对象
			if($.isEmptyObject(reply2ndObj[0].sendBoxObj)) {
				reply2ndObj[0].sendBoxObj = $('.reply_2nd_content_selector', ancestorObj).sendBox({
					panels:'emote',
					type:'post',
					url:params.publish_comment_url || "",
					data:params.post_params || {},
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							alert(json.info);
							return false;
						}
						
						//创建一个二级评论的对象
						var unit2divObj = comment_2nd_unit.createByFeedType(json.data || {}, params.feed_type_name);
						$('#second_comment_list', ancestorObj).prepend(unit2divObj);
						
						$('.reply_2nd_content_selector', ancestorObj).val('');
						reply2ndObj.hide();
					}
				});
			}
			//显示状态的切换
			if(reply2ndObj.css('display') == 'none') {
				reply2ndObj.css('display', 'block');
			} else {
				reply2ndObj.css('display', 'none');
			}
		});
		
		//绑定"我也来说一句"
		$('.reply_2nd_simple_selector').live('click', function() {
			//按钮所在的P元素范围
			var pObj = $(this).closest('p');
			//处理相关的逻辑
			var ancestorObj = $(this).closest('.reply_2nd_simple_div_selector');
			var scopeObj = $(this).closest('.comment_1st_unit_selector');
			
			var params = feed_tool.collectParams($('.comment_1st_unit_params_selector', scopeObj));
			//获取要提交的相关数据信息
			//处理编辑框的相关事件
			if($.isEmptyObject(pObj[0].sendBoxObj)) {
				pObj[0].sendBoxObj = $('.reply_2nd_simple_content_selector', ancestorObj).sendBox({
					panels:'emote',
					type:'post',
					url:params.publish_comment_url || "",
					data:params.post_params || {},
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							alert(json.info);
							return false;
						}
						
						//创建一个二级评论的对象
						var unit2divObj = comment_2nd_unit.createByFeedType(json.data || {}, params.feed_type_name);
						$('#second_comment_list', scopeObj).prepend(unit2divObj);
						pObj.show();
						$('.simple_reply_div_selector', ancestorObj).hide();
					}
				});
			}
			//获取输入焦点
			pObj[0].sendBoxObj.focus();
			//显示sendbox所在的div
			pObj.hide();
			$('.simple_reply_div_selector', ancestorObj).show();
		});
	}
	
	//委托二级评论对应的事件
	,delegateEventForComment2ndUnit:function() {
		//2级评论的删除事件
		$('.comment_2nd_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.comment_2nd_unit_selector');
			var params = feed_tool.collectParams($('.comment_2nd_unit_params_selector', ancestorObj));
			$.ajax({
				type:'get',
				url:params.delete_comment_url,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					ancestorObj.remove();
				}
			});
		});
	}
	
};

var feed_unit = {
	//创建feed_unit对象
	createByFeedType:function(feed_datas, feed_type_name) {
		var div_id = "feed_unit_%s".replace('%s', feed_type_name);
		var divObj = $('#' + div_id).clone().removeAttr('id').show();
		//根据feed类型创建不同的对象
		divObj.renderHtml({
			feed:feed_datas || {}
		});
		return divObj;
	}
};

var feed_comment = {
	//根据动态类型创建评论框
	createByFeedType:function(feed_type_name, from_id, callback) {
		var div_id = "comment_%s_main_div".replace("%s", feed_type_name);
		var divObj = $("#" + div_id).clone().removeAttr('id');
		//渲染页面的元素
		divObj.renderHtml({
			from_id:from_id
		});
		
		divObj = $(divObj);
		var params = feed_tool.collectParams($('.comment_params_selector', divObj));
		//加载实体的评论信息
		var comment_list = {};
		$.ajax({
			type:'get',
			url:params.get_comments_url || "",
			dataType:'json',
			async:false,
			success:function(json) {
				comment_list = json.data || {};
			}
		});
		
		for(var i in comment_list) {
			var comment_datas = comment_list[i];
			var childDivObj = comment_1st_unit.createByFeedType(comment_datas, feed_type_name);
			childDivObj.data('datas', comment_datas);
			parentObj.append(childDivObj);
		}
		
		//将div对象加载到document
		if(typeof callback == 'function') {
			//初始一级菜单的sendbox对象
			callback(divObj, function(divObj) {
				$('.reply_1st_content_selector', divObj).sendBox({
					panels:'emote,upload',
					type:'post',
					url:params.publish_comment_url || "",
					data:params.post_params || {},
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							alert(json.info);
							return false;
						}
						//添加对应的评论层
						var unitDivObj = comment_1st_unit.createByFeedType(json.data || {}, feed_type_name);
						$('#first_comment_list', divObj).prepend(unitDivObj);
						
						$('.reply_1st_content_selector', divObj).val('');
					}
				});
			});
		}
	}
};

var comment_1st_unit = {
	createByFeedType:function(comment_datas, feed_type_name) {
		comment_datas = comment_datas || {};
		
		var div_id = "comment_%s_1st_unit_div".replace("%s", feed_type_name);
		var divObj = $('#' + div_id).clone().removeAttr('id').show();
		
		var parentObj = $('#second_comment_list', divObj);
		var child_list = comment_datas.child_list || {};
		for(var i in child_list) {
			var child_comment = child_list[i];
			var childDivObj = comment_2nd_unit.createByFeedType(child_comment, feed_type_name);
			childDivObj.data('datas', child_comment);
			parentObj.append(childDivObj);
		}
		divObj.renderHtml({
			comment:comment_datas || {}
		});
		
		return divObj;
	}
};

var comment_2nd_unit = {
	createByFeedType:function(child_comment, feed_type_name) {
		var div_id = "comment_%s_2nd_unit_div".replace('%s', feed_type_name);
		var divObj = $('#' + div_id).clone().removeAttr('id').show();
		divObj.renderHtml({
			child_comment:child_comment || {}
		});
		return divObj;
	}
};

//动态的删除部分
var feed_delete = {
	//绑定用户自己定义事件
	attachEventUserDefine:function() {
		$('#feed_delete_div').bind({
			//打开删除层
			openEvent:function(evt, options) {
				options = options || {};
				var divObj = $(this);
				divObj.data('options', options);
				art.dialog({
					id:'feed_delete_dialog',
					title:'动态删除',
					content:divObj.get(0),
					follow:options.follow || {},
					init:function() {
						
					}
				}).lock();
			},
			//关闭删除层
			closeEvent:function() {
				var dialogObj = art.dialog.list['feed_delete_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
		});
	},
	
	//相应的事件委托
	delegateEvent:function() {
		//确定按钮
		$('#feed_delete_sure_btn').live('click', function() {
			var ancestorObj = $(this).parents('#feed_delete_div');
			var options = ancestorObj.data('options') || {};
			var datas = options.datas || {};
			var feed_id = datas.feed_id;
			$.ajax({
				type:'get',
				url:'/Sns/Feed/List/deleteFeedAjax/feed_id/' + feed_id,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					if(typeof options.callback == 'function') {
						options.callback();
					}
					$('#feed_delete_div').trigger('closeEvent');
				}
			});
		});
		
		//关闭按钮
		$('#feed_delete_cancel_btn').live('click', function() {
			$('#feed_delete_div').trigger('closeEvent');
		});
	}
};

feed.register(function() {
	feed_delete.attachEventUserDefine();
	feed_delete.delegateEvent();
});

$(document).ready(function() {
	$('#show_feed').loadFeed({
		url:'/Sns/Feed/List/getUserAllFeedAjax'
	});
});
