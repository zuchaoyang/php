(function($) {
	/**
	 * options = {
	 *	//处理评论删除和回复后的评论数的更新 
	 *	callback:function() {
	 * 
	 *	 },
	 *	 //是否显示加载更多的工具条
	 *   show_load_more: true | false
	 * }
	 */
	$.fn.loadMoodComments=function(mood_id, options) {
		comment.globalInit();
		this.each(function() {
			var elem = this;
			if($.isEmptyObject(elem.commentObj)) {
				elem.commentObj = new comment(elem, mood_id, options || {});
				elem.commentObj.init();
			}
		});
		return this;
	};
	
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

function comment(elem, mood_id, options) {
	this.hasNextPage = true;
	this.$elem = $(elem);
	this.mood_id = mood_id;
	this.options = options || {};
};

comment.globalInitList = [];
comment.register=function(fn) {
	comment.globalInitList.push(typeof fn == 'function' ? fn : $.noop);
};

comment.globalInit=function() {
	var fn = $.noop;
	while(!$.isEmptyObject(comment.globalInitList)) {
		var fn = comment.globalInitList.shift();
		if(fn && typeof fn == 'function') {
			fn();
		}
	}
};

comment.prototype = {
	//第一次运行的时候加载相关的信息
	init:function() {
		var me = this;
		
		//创建加载更多的按钮
		var divObj = $('#load_more_div').clone().removeAttr('id').show();
		divObj.appendTo(me.$elem);
		$('#load_more_comment_a', divObj).click(function() {
			var page = $(this).data('page') || 1;
			var hasNextPage = me.loadDatas(page + 1);
			if(!hasNextPage) {
				divObj.hide();
			} else {
				divObj.show();
				$(this).data('page', page + 1);
			}
			//防止页面跳转和事件冒泡
			return false;
		});
		//隐藏加载更多的按钮
		if(me.options.show_load_more === false) {
			$('#load_more_comment_a', divObj).parent().hide();
		}
		//初始化第一页的数据
		me.loadDatas(1);
		//绑定刷新的相关事件
		me.$elem.addClass('ancestor_selector').data('options', me.options || {});
	},
	
	//加载说说的评论信息
	loadDatas:function(page) {
		var me = this;
		page = page > 1 ? page : 1;
		var hasNextPage = true;
		$.ajax({
			type:'get',
			url:'/Sns/Mood/Comments/getMoodCommentsAjax/mood_id/' + me.mood_id + '/page/' + page,
			dataType:'json',
			async:false,
			success:function(json) {
				if(json.status < 0 || $.isEmptyObject(json.data) || !json.data.has_next_page) {
					hasNextPage = false;
				}
				//填充相应的数据
				me.fillCommentDatas(json.data.comment_list || {});
			}
		});
		return hasNextPage;
	},
	
	//填充相应的动态信息
	fillCommentDatas:function(comment_list) {
		var me = this;
		comment_list = comment_list || {};
		var insertPosObj = $('.load_more_selector', me.$elem);
		for(var i in comment_list) {
			var divObj = comment_1st_unit.create(comment_list[i]);
			if(insertPosObj.length >= 1) {
				divObj.insertBefore(insertPosObj);
			} else {
				divObj.appendTo(me.$elem);
			}
		}
	}
};

function comment_1st_unit() {
	this.delegateEvent();
}

comment_1st_unit.prototype = {
	//关闭所有的回复对话框
	closeAllSendBoxDiv:function() {
		$('.comment_1st_reply_div_selector, .say_div_selector').hide();
		$('.say_text_selector').show();
	},
	
	delegateEvent:function() {
		var me = this;
		//一级评论的删除按钮
		$('.comment_1st_delete_a_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_1st_unit_selector');
			var child_nums = $('.comment_2nd_unit_selector', ancestorObj).length;
			
			var comment_id = $(':input[name="comment_id"]', ancestorObj).val();
			$('#comment_delete_div').trigger('openEvent', [{
				data:{
					comment_id:comment_id
				},
				follow:aObj.get(0),
				callback:function() {
					//评论数减1
					var options = aObj.closest('.ancestor_selector').data('options') || {};
					if(typeof options.callback == 'function') {
						options.callback(-(child_nums + 1));
					}
					//移除一级评论对象
					ancestorObj.remove();
				}
			}]);
		});
		
		//一级评论的回复按钮
		$('.comment_1st_reply_a_selector').live('click', function() {
			//关闭其他sendbox对应的div
			me.closeAllSendBoxDiv();
			
			var aObj = $(this);
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var replyDivObj = $('.comment_1st_reply_div_selector', ancestorObj);
			var click_nums = aObj.data('click_nums') || 1;
			
			//绑定sendbox相关的事件
			if(!aObj.data('inited')) {
				var comment_id = $(':input[name="comment_id"]', ancestorObj).val();
				var mood_id = $(':input[name="mood_id"]', ancestorObj).val();
				$('.reply_textarea', replyDivObj).sendBox({
					panels:'emote',
					skin:'mini',
					type:'post',
					url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
					dataType:'json',
					data: {
						mood_id:mood_id,
						up_id:comment_id
					},
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						//创建一个2级评论对象并追加
						comment_2nd_unit.create(json.data).prependTo($('#comment_2nd_list_div', ancestorObj));
						//隐藏回复框，重置click_nums的值
						replyDivObj.hide();
						//重置click_nums的值为奇数是为了保证下次点击的时候回复框能够显示
						aObj.data('click_nums', 1);
						//评论数加一
						var options = aObj.closest('.ancestor_selector').data('options') || {};
						if(typeof options.callback == 'function') {
							options.callback(1);
						}
					}
				});
				aObj.data('inited', true);
			}
			
			//控制显示与隐藏的切换
			if(click_nums % 2 == 0) {
				replyDivObj.hide();
			} else {
				replyDivObj.show();
			}
			aObj.data('click_nums', click_nums + 1);
		});
		
		//我也来说一句
		$('.say_text_selector').live('click', function() {
			//关闭其他sendbox对应的div
			me.closeAllSendBoxDiv();
			
			var inpObj = $(this).hide();
			var ancestorObj = $(this).closest('.comment_1st_unit_selector');
			var sayDivObj = $('.say_div_selector', ancestorObj).show();
			
			if($.isEmptyObject(sayDivObj[0].sendBoxObj)) {
				var comment_id = $(':input[name="comment_id"]', ancestorObj).val();
				var mood_id = $(':input[name="mood_id"]', ancestorObj).val();
				sayDivObj[0].sendBoxObj = $('.reply_textarea', sayDivObj).sendBox({
					panels:'emote',
					skin:'mini',
					type:'post',
					url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
					dataType:'json',
					data: {
						mood_id:mood_id,
						up_id:comment_id
					},
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						//创建一个2级评论对象并追加
						comment_2nd_unit.create(json.data).prependTo($('#comment_2nd_list_div', ancestorObj));
						
						sayDivObj.hide();
						inpObj.show();
						
						//评论数加一
						var options = inpObj.closest('.ancestor_selector').data('options') || {};
						if(typeof options.callback == 'function') {
							options.callback(1);
						}
					}
				});
			}
		});
	}
};

comment_1st_unit.create=function(comment) {
	if($.isEmptyObject(comment)) {
		return false;
	}
	
	var comment1stUnitDiv = $('#comment_1st_unit_div').clone().removeAttr('id');
	comment1stUnitDiv.renderHtml({
		comment:comment
	});
	//创建孩子节点
	if(!$.isEmptyObject(comment.child_items)) {
		var parentObj = $('#comment_2nd_list_div', comment1stUnitDiv);
		for(var i in comment.child_items) {
			comment_2nd_unit.create(comment.child_items[i]).appendTo(parentObj);
		}
	}
	
	$(comment1stUnitDiv).loadImg();
	
	return $(comment1stUnitDiv).show();
};

function comment_2nd_unit() {
	this.delegateEvent();
}

comment_2nd_unit.prototype = {
	delegateEvent:function() {
	    //2级评论的删除事件
		$('.comment_2nd_delete_a_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_2nd_unit_selector');
			var comment_id = $(':input[name="child_comment_id"]', ancestorObj).val();
			$('#comment_delete_div').trigger('openEvent', [{
				data:{
					comment_id:comment_id
				},
				follow:aObj.get(0),
				callback:function() {
					//评论数减1
					var options = aObj.closest('.ancestor_selector').data('options') || {};
					if(typeof options.callback == 'function') {
						options.callback(-1);
					}
					ancestorObj.remove();
				}
			}]);
		});
	}
};

comment_2nd_unit.create=function(child_comment) {
	child_comment = child_comment || {};
	var comment2ndUnitDiv = $('#comment_2nd_unit_div').clone().removeAttr('id');
	comment2ndUnitDiv.renderHtml({
		child_comment:child_comment
	});
	
	$(comment2ndUnitDiv).loadImg();
	
	return $(comment2ndUnitDiv).show();
};

function comment_delete() {
	this.attachEvent();
	this.attachEventUserDefine();
}

comment_delete.prototype = {
	attachEventUserDefine:function() {
		var divObj = $('#comment_delete_div');
		$('#comment_delete_div').bind({
			//打开删除层
			openEvent:function(evt, options) {
				options = options || {};
				divObj.data('options', options);
				art.dialog({
					id:'comment_delete_dialog',
					title:'删除评论',
					content:divObj.get(0),
					follow:options.follow || {},
					init:function() {
						$('input[name="comment_id"]', divObj).val(options.data.comment_id);
					}
				});
			},
			
			//关闭删除层
			closeEvent:function() {
				var dialogObj = art.dialog.list['comment_delete_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
			
		});
	},
	
	//删除评论部分的事件绑定
	attachEvent:function() {
		var divObj = $('#comment_delete_div');
		//确定删除按钮
		$('#sure_btn', divObj).click(function() {
			var options = divObj.data('options') || {};
			var comment_id = $('input[name="comment_id"]', divObj).val();
			$.ajax({
				type:'get',
				url:'/Sns/Mood/Comments/deleteMoodCommentsAjax/comment_id/' + comment_id,
				dataType:'json',
				success:function(json) {
					divObj.trigger('closeEvent');
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					if(typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		});
		
		//取消按钮
		$('#cancel_btn').click(function() {
			divObj.trigger('closeEvent');
		});
	}
};

comment.register(function() {
	new comment_1st_unit();
	new comment_2nd_unit();
	new comment_delete();
});