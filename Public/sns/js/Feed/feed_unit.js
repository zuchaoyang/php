/**
 * 面临的问题：
 * 1. 不同的动态拥有不同的模板，但是js的实现只有一套代码；
 * 2. 动态相关的事件都是走委托，而事件的内部必然会涉及到元素的操作，而元素相关的对象与feed_unit创建的时候
 *    的一些设置相关；
 * 3. 严格意义上的事件委托，相应的事件只能和其当时所在的运行环境有关；
 * 
 * =》因此在feed_comment初始化的时候，对当前的对象进行绑定；
 * 
 * 阅读此段代码应该注意以下几点:
 * 1. 具备js面向对象的编程思想；
 * 2. 严格的事件委托机制；
 * 3. 基于配置可扩展的编程思路；
 */

function dump(obj) {
	
	for(var i in obj) {
		if(typeof obj[i] == 'object') {
			for(var j in obj[i])
				alert(i + "=>" + j + "=>" + obj[i][j]);
		} else {
			alert(i + "=>" + obj[i]);
		}
	}
}

//扩展图片的处理函数
(function($) {
	//加载容器内部的图片信息
	$.fn.loadImg=function() {
		$('img', $(this)).each(function() {
			var data_original = $(this).attr('data-original');
			var data_from = $(this).attr('data-from');
			if(data_original) {
				$(this).attr('src', data_original);
			} else if(data_from) {
				$(this).attr('src', $(data_from).val());
			}
		});
		return this;
	};
})(jQuery);

(function($) {
	
	$(document).click(function(evt) {
		var pageX = evt.pageX;
		var pageY = evt.pageY;
		
		//$.sendboxHandler.close(aObj[0].handler_id);
	});
	
})(jQuery);



//针对sendbox句柄管理的扩展
(function($) {
	function handler() {
		this.current_handler_id = 0;
		this.handler_pointer = 1;
		this.handler_list = [];
	}
	
	handler.prototype = {
		register:function(openFunc, closeFunc) {
			var me = this;
			
			openFunc = openFunc || $noop;
			closeFunc = closeFunc || $.noop;
			
			me.handler_list[me.handler_pointer] = [openFunc, closeFunc];
			
			return me.handler_pointer++;
		}
	
		,open:function(handler_id) {
			this.close(this.current_handler_id);
			
			var handler_funcs = this.handler_list[handler_id];
			if(!$.isEmptyObject(handler_funcs)) {
				var fn = handler_funcs[0];
				typeof fn == 'function' && fn.call();
			}
			this.current_handler_id = handler_id;
		}
		
		,close:function(handler_id) {
			var handler_funcs = this.handler_list[handler_id];
			if(!$.isEmptyObject(handler_funcs)) {
				var fn = handler_funcs[1];
				typeof fn == 'function' && fn.call();
			}
		}
	};
	
	$.sendboxHandler = new handler();
})(jQuery);

//动态相关的参数配置
(function($) {

function settings() {
	this.params = {};
	
	this.params.blog = {
		feed_unit : {
			feed_id:'{feed_id}',
			feed_type:'{feed_type}',
			from_id:'{from_id}',
			publish_comment_url:'/Sns/Blog/Comments/publishBlogCommentAjax',
			get_comments_url:'/Sns/Blog/Comments/getBlogCommentsForFeedAjax/blog_id/{from_id}/page/1',
			post_params:{
				blog_id : '{from_id}',
				up_id:0
			}
		}
		
		,comment_1st_unit:{
			publish_comment_url:'/Sns/Blog/Comments/publishBlogCommentAjax',
			delete_comment_url:'/Sns/Blog/Comments/deleteBlogCommentsAjax/comment_id/{comment_id}',
			child_items_name:'child_items',
			post_params: {
				blog_id:'{blog_id}',
				up_id:'{comment_id}'
			}
		}
		
		,comment_2nd_unit:{
			delete_comment_url:'/Sns/Blog/Comments/deleteBlogCommentsAjax/comment_id/{comment_id}'
		}
	};
	
	this.params.mood = {
		feed_unit : {
			feed_id:'{feed_id}',
			feed_type:'{feed_type}',
			from_id:'{from_id}',
			publish_comment_url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
			get_comments_url:'/Sns/Mood/Comments/getMoodCommentsForFeedAjax/mood_id/{from_id}/page/1',
			post_params:{
				mood_id : '{from_id}',
				up_id:0
			}
		}
		
		,comment_1st_unit:{
			publish_comment_url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
			delete_comment_url:'/Sns/Mood/Comments/deleteMoodCommentsAjax/comment_id/{comment_id}',
			child_items_name:'child_items',
			post_params: {
				mood_id:'{mood_id}',
				up_id:'{comment_id}'
			}
		}
		
		,comment_2nd_unit:{
			delete_comment_url:'/Sns/Mood/Comments/deleteMoodCommentsAjax/comment_id/{comment_id}'
		}
	};
	
	this.params.photo = {
		feed_unit : {
			feed_id:'{feed_id}',
			feed_type:'{feed_type}',
			from_id:'{from_id}',
			publish_comment_url:'/Sns/Album/PhotoComments/publishPhotoCommentsAjax',
			get_comments_url:'/Sns/Album/PhotoComments/getPhotoCommentsForFeedAjax/photo_id/{from_id}/page/1',
			post_params:{
				photo_id : '{from_id}',
				up_id:0
			}
		}
		
		,comment_1st_unit:{
			publish_comment_url:'/Sns/Album/PhotoComments/publishPhotoCommentsAjax',
			delete_comment_url:'/Sns/Album/PhotoComments/deletePhotoCommentsAjax/comment_id/{comment_id}',
			child_items_name:'child_items',
			post_params: {
				photo_id:'{photo_id}',
				up_id:'{comment_id}'
			}
		}
		
		,comment_2nd_unit:{
			delete_comment_url:'/Sns/Album/PhotoComments/deletePhotoCommentsAjax/comment_id/{comment_id}'
		}
	};
}

settings.prototype = {
	//获取对应的参数设置
	getParams:function(feed_type, mode_name, datas) {
		var me = this;
		if($.isEmptyObject(datas)) {
			return {};
		}
		
		var mode_params = this.params[feed_type][mode_name];
		for(var name in mode_params) {
			var val = mode_params[name];
			if(typeof val == 'object') {
				for(var i in val) {
					val[i] = me.replace(val[i], datas);
				}
			} else {
				val = me.replace(val, datas);
			}
			mode_params[name] = val;
		}
		
		return mode_params;
	}

	,replace:function(str, datas) {
		if(!str) return "";
		
		str = str.toString();
		datas = datas || {};
		
		return str.replace(/\{([^\}]+?)\}/m, function(a, b) {
			return datas[b];
		});
	}
};

$.getFeedParams = function(feed_type, mode_name, datas) {
	var settingsObj = new settings();
	return settingsObj.getParams(feed_type, mode_name, datas);
};

})(jQuery);

(function($) {
	$.createFeedUnit=function(feed_datas, feed_type, skin) {
		//获取系统自动注册的模块信息
		var FeedUnit = new feed_unit(feed_datas, feed_type, skin);
		var divObj = FeedUnit.getElement();
		return FeedUnit.getElement();
	};
})(jQuery);

function feed_unit(feed_datas, feed_type, skin) {
	this.skin = skin || 'default';
	//初始化全局设置
	feed_unit.globalInit();
	
	this.inited = false;
	this.divObj = {};
	this.feed_datas = feed_datas || {};
	this.feed_type = feed_type;
	
	this.params = $.getFeedParams(feed_type, 'feed_unit', feed_datas);
	
	this.init();
}

feed_unit.initList = [];
	
feed_unit.register = function(fn) {
	feed_unit.initList.push(fn || $.noop);
};

feed_unit.globalInit = function() {
	while(!$.isEmptyObject(feed_unit.initList)) {
		var fn = feed_unit.initList.shift() || $.noop;
		fn.call();
	}
};

feed_unit.prototype = {
	init:function() {
		var me = this;
		
		me.divObj = $('#feed_unit_div').clone().removeAttr('id').show();
		me.divObj.renderHtml({
			feed:me.feed_datas
		});
		
		//加载内容内的图片信息
		me.divObj.loadImg();
		//处理评论层相关
		var commentDivObj = $('#comment_main_div', me.divObj);
		//绑定操作句柄
		me.divObj[0].handler = me;
	}

	//获取创建后的元素对象
	,getElement:function() {
		return this.divObj;
	}
	
	,getParams:function() {
		return this.params;
	}
	
	,getSkin:function() {
		return this.skin;
	}
	
	//创建一个孩子节点
	,createChildren:function(comment_datas) {
		var me = this;
		
		var containObj = $('#first_comment_list', me.divObj);
		
		var comment1stUnit = new comment_1st_unit(comment_datas, me.feed_type);
		var childDivObj = comment1stUnit.getElement();
		
		containObj.append(childDivObj);
	}
	
	//向前追加孩子节点
	,prependChildren:function(comment_datas) {
		var me = this;
		
		var containObj = $('#first_comment_list', me.divObj);
		
		var comment1stUnit = new comment_1st_unit(comment_datas, me.feed_type);
		var childDivObj = comment1stUnit.getElement();
		
		containObj.prepend(childDivObj);
	}

	//加载一级评论信息
	,loadComments:function() {
		var me = this;
		//加载实体的评论信息
		$.ajax({
			type:'get',
			url:me.params.get_comments_url || "",
			dataType:'json',
			async:false,
			success:function(json) {
				var comment_list = json.data || {};
				for(var i in comment_list) {
					me.createChildren(comment_list[i] || {});
				}
			}
		});
	}
	
	//刷新动态对应实体的评论信息
	,reflushComments:function() {
		var me = this;
		
		$.ajax({
			type:'get',
			url:"/Sns/Feed/List/getEntityAjax/feed_type/" + me.params.feed_type + "/from_id/" + me.params.from_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					return false;
				}
				var comments = json.data.comments || 0;
				var aObj = $('.feed_comment_selector', me.divObj);
				var html = aObj.html().toString() || "";
				var parttern = /([^\d]+)(\d+)([^\d*?])/;
				if(html.match(parttern)) {
					html = html.replace(parttern, function(a, b, c, d) {
						return b + comments + d;
					});
				} else {
					html = html + "(" + comments + ")";
				}
				aObj.html(html);
			}
		});
	}
};

function comment_1st_unit(comment_datas, feed_type) {
	this.divObj = {};
	this.comment_datas = comment_datas || {};
	this.feed_type = feed_type;
	
	this.params = $.getFeedParams(feed_type, 'comment_1st_unit', comment_datas);
	this.init();
}

comment_1st_unit.prototype = {
	init:function() {
		var me = this;
		
		me.divObj = $('#comment_1st_unit_div').clone().removeAttr('id').show();
		me.divObj.renderHtml({
			comment:me.comment_datas
		});
		me.divObj = $(me.divObj);
		//获取模板的参数设置
		var child_item_name = me.params.child_items_name || 'child_list';
		var child_list = me.comment_datas[child_item_name] || {};
		for(var i in child_list) {
			me.createChildren(child_list[i]);
		}
		//加载内容内的图片信息
		me.divObj.loadImg();
		//绑定操作句柄
		me.divObj[0].handler = me;
	}

	//获取创建后的元素对象
	,getElement:function() {
		return this.divObj;
	}
	
	//创建孩子节点
	,createChildren:function(child_comment) {
		var me = this;
		
		var parentObj = $('#second_comment_list', me.divObj);
		
		var comment2ndUnit = new comment_2nd_unit(child_comment, me.feed_type);
		var childDivObj =comment2ndUnit.getElement();
		childDivObj.data('datas', child_comment);

		parentObj.append(childDivObj);
	}
	
	//创建孩子节点
	,prependChildren:function(child_comment) {
		var me = this;
		
		var parentObj = $('#second_comment_list', me.divObj);
		
		var comment2ndUnit = new comment_2nd_unit(child_comment, me.feed_type);
		var childDivObj = comment2ndUnit.getElement();
		childDivObj.data('datas', child_comment);
		
		parentObj.prepend(childDivObj);
	}
	
	,getParams:function() {
		return this.params;
	}
};

function comment_2nd_unit(child_comment, feed_type) {
	this.divObj = {};
	this.child_comment = child_comment;
	this.feed_type = feed_type;
	this.params = $.getFeedParams(feed_type, 'comment_2nd_unit', child_comment);
	
	this.init();
}

comment_2nd_unit.prototype = {
	init:function() {
		var me = this;
		
		me.divObj = $('#comment_2nd_unit_div').clone().removeAttr('id').show();
		me.divObj.renderHtml({
			child_comment:me.child_comment
		});
		//加载对应的图片信息
		me.divObj.loadImg();
		//将当期对象绑定到对应的html元素上
		me.divObj[0].handler = me;
	}

	//获取创建后的元素对象
	,getElement:function() {
		return this.divObj;
	}
	
	,getParams:function() {
		return this.params;
	}
};

//扩展评论相关的事件信息
(function(feed_unit) {
	
//评论相关的事件需要集中处理
function comment_events() {
	this.delegateEventForEffect();
	this.delegateEventForFeedUnit();
	this.delegateEventForComment1stUnit();
	this.delegateEventForComment2ndUnit();
}

comment_events.prototype = {
	delegateEventForEffect:function() {
		//一级评论的删除效果
		$('.comment_1st_delete_slide_selector').live('mouseover', function() {
			$('.comment_1st_delete_selector', $(this)).show();
		}).live('mouseleave', function() {
			$('.comment_1st_delete_selector', $(this)).hide();
		});
	
		//二级评论的删除效果
		$('.comment_2nd_delete_slide_selector').live('mouseover', function() {
			$('.comment_2nd_delete_selector', $(this)).show();
		}).live('mouseleave', function() {
			$('.comment_2nd_delete_selector', $(this)).hide();
		});
	}

	,delegateEventForFeedUnit:function() {
		//删除按钮
		$('.feed_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.feed_unit_selector');
			var aObj = $(this);
			
			var handler = ancestorObj[0].handler;
			var params = handler.getParams();
			//触发删除相关的事件
			$('#feed_delete_div').trigger('openEvent', [{
				datas : {
					feed_id:params.feed_id
				},
				follow:aObj.get(0),
				callback:function() {
					//动画相关慢慢移除
					ancestorObj.animate({
						height:0
					}, 'slow').remove();
				}
			}]);
		});
		
		//评论按钮, 点击有切换的效果
		$('.feed_comment_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = $(this).closest('.feed_unit_selector');
			
			var handler = ancestorObj[0].handler;
			var params = handler.getParams();
			var toggled_nums = aObj.data('toggled_nums') || 1;
			//初始化动态对应的评论层信息
			if(toggled_nums == 1) {
				//加载以及评论信息
				handler.loadComments();
			}
			
			if(toggled_nums % 2 == 0) {
				$('#comment_main_div', ancestorObj).hide();
			} else {
				$('#comment_main_div', ancestorObj).show();
			}
			aObj.data('toggled_nums', toggled_nums + 1);
			
			return false;
		});
		
		//发布评论相关的事件
		$('.reply_1st_txt_selector').live('click', function() {
			var textObj = $(this);
			var ancestorObj = $(this).closest('.feed_unit_selector');
			var tabObj = $('#reply_1st_tab', ancestorObj);
			
			var handler = ancestorObj[0].handler;
			var params = handler.getParams();
			var skin = handler.getSkin();
			//初始一级菜单的sendbox对象
			if(!tabObj.data('inited')) {
				$('.reply_1st_content_selector', tabObj).sendBox({
					panels:'emote',
					type:'post',
					skin:'mini',
					url:params.publish_comment_url || "",
					data:params.post_params || {},
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						
						//添加对应的评论层
						handler.prependChildren(json.data || {});
						$('.reply_1st_content_selector', tabObj).val('');
						
						//刷新评论数
						var feedUnitDivObj = ancestorObj.closest('.feed_unit_selector');
						var feedUnitHandler = feedUnitDivObj[0].handler;
						feedUnitHandler.reflushComments();
						
						tabObj.fadeOut(2000, function() {
							$.sendboxHandler.close(textObj[0].sendbox_handler_id);
						});
					}
				});
				//注册到全局的句柄管理中去
				textObj[0].sendbox_handler_id = $.sendboxHandler.register(function() {
					tabObj.show();
					textObj.hide();
				}, function() {
					tabObj.hide();
					textObj.show();
				});
				tabObj.data('inited', true);
			}
			
			$.sendboxHandler.open(textObj[0].sendbox_handler_id);
		});
	}

	//一级评论相关的事件
	,delegateEventForComment1stUnit:function() {

		//一级评论的删除事件
		$('.comment_1st_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var handler = ancestorObj[0].handler;
			
			var params = handler.getParams();
			$.ajax({
				type:'get',
				url:params.delete_comment_url,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					
					//刷新评论数
					var feedUnitDivObj = ancestorObj.closest('.feed_unit_selector');
					var feedUnitHandler = feedUnitDivObj[0].handler;
					feedUnitHandler.reflushComments();
					
					ancestorObj.animate({
						height:'0px'
					}, 'slow').remove();
				}
			});
		});
		
		//绑定"我也来说一句"
		$('.reply_2nd_simple_selector').live('click', function() {
			
			var inpObj = $(this);
			var inpElem = inpObj[0];
			//处理相关的逻辑
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var handler = ancestorObj[0].handler;
			//大范围对象的获取
			var scopeObj = $(this).closest('.feed_unit_selector');
			var scopeHandler = scopeObj[0].handler;
			var skin = scopeHandler.getSkin();
			
			var params = handler.getParams();
			//获取要提交的相关数据信息
			//处理编辑框的相关事件
			if($.isEmptyObject(inpElem.sendBoxObj)) {
				
				inpElem.sendBoxObj = $('.reply_2nd_simple_content_selector', ancestorObj).sendBox({
					panels:'emote',
					skin:'mini',
					type:'post',
					url:params.publish_comment_url || "",
					data:params.post_params || {},
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						
						//创建一个二级评论的对象
						handler.prependChildren(json.data || {});
						
						$.sendboxHandler.close(inpElem.sendbox_handler_id);
						//刷新评论数
						var feedUnitDivObj = ancestorObj.closest('.feed_unit_selector');
						var feedUnitHandler = feedUnitDivObj[0].handler;
						feedUnitHandler.reflushComments();
					}
				});
				
				//注册到sendbox的管理列表
				inpElem.sendbox_handler_id = $.sendboxHandler.register(function() {
					//获取输入焦点
//					inpElem.sendBoxObj.focus();
					//显示sendbox所在的div
					inpObj.hide();
					$('.simple_reply_div_selector', ancestorObj).show();
				}, function() {
					inpObj.show();
					$('.simple_reply_div_selector', ancestorObj).hide();
				});
				
				$.sendboxHandler.open(inpElem.sendbox_handler_id);
			}
			
			
		});
		
		//查看更多评论对应的按钮
		$('.load_more_comment_a').live('click', function() {
			if(!$(this).data('inited')) {
				var ancestorObj = $(this).closest('.feed_unit_selector');
				//加载更多对应的href
				var data_target = $(this).attr('data-target');
				$(this).attr('href', $(data_target, ancestorObj).attr('href'));
				$(this).trigger('click');
				$(this).data('inited', true);
			}
		});
		
	}
	
	//委托二级评论对应的事件
	,delegateEventForComment2ndUnit:function() {
    
		//2级评论的删除事件
		$('.comment_2nd_delete_selector').live('click', function() {
			var ancestorObj = $(this).closest('.comment_2nd_unit_selector');
			var handler = ancestorObj[0].handler;
			
			var params = handler.getParams();
			$.ajax({
				type:'get',
				url:params.delete_comment_url,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					ancestorObj.animate({
						height:'0px'
					}, 'slow').remove();
					//刷新评论数
					//刷新评论数
					var feedUnitDivObj = ancestorObj.closest('.feed_unit_selector');
					var feedUnitHandler = feedUnitDivObj[0].handler;
					feedUnitHandler.reflushComments();
				}
			});
			
			return false;
		});
	}
};

feed_unit.register(function() {
	new comment_events();
});

})(feed_unit);


//扩展feed_unit相关的事件
(function() {

function feed_delete() {
	this.attachEventUserDefine();
	this.delegateEvent();
}

feed_delete.prototype = {
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
	}
	
	//相应的事件委托
	,delegateEvent:function() {
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

feed_unit.register(function() {
	new feed_delete();
});

})();