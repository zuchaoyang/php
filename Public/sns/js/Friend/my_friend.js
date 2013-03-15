function Friend() {
	this.attachEventForSearch();
	this.attachEventForLoadMore();
	this.delegateEvent();
	this.delegateEventForLeftMenu();
	this.attachEventUserDefine();
	
	this.init();
	this.initFriendGroup();
}

Friend.prototype.init=function() {
	var me = this;
	
	$('#load_more_a').data('page', 2);
	$('#delete_friend_group_div').hide();
	$('#add_friend_group_div').hide();
	$('#add_friend_group_div').hide();
	$('#edit_friend_group_div').hide();
	$('#friend_group_menu_div').hide();
	$('#delete_friend_relation_div').hide();
	$('#send_private_msg_div').hide();
	
	//加载页面数据
	Friend.registerFilters({
		type:'get',
		url:'/Sns/Friend/Manage/getMyFriendListAjax',
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				return false;
			}
			
			me.fillFriendList(json.data || {});
		}
	});
	
};

Friend.prototype.attachEventUserDefine=function() {
	var me = this;
	$('body').bind({
		//刷新分组相关的信息
		refreshGroupEvent:function() {
			me.initFriendGroup();
		}
	});
};

Friend.prototype.initFriendGroup=function() {
	//获取好友的分组信息
	var friend_group_list = {};
	$.ajax({
		type:'get',
		url:'/Sns/Friend/Manage/getFriendGroupAjax',
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				return false;
			}
			$("#friend_total_count").html(json.info);
			friend_group_list = json.data || {};
		}
	});
	//渲染左侧数据
	$('#friend_group_list_ul').renderHtml({
		friend_group_list:friend_group_list
	}).show();
	
	//渲染浮动层的数据
	$('#friend_group_menu_p').renderHtml({
		friend_group_list:friend_group_list
	}).show();
};

Friend.prototype.attachEventForSearch=function() {
	var me = this;
	var context = $('#search_p');
	//搜索部分
	var default_search_name = "搜索姓名";
	$('#search_name', context).focus(function() {
		var search_name = $.trim($(this).val());
		if(search_name == default_search_name) {
			$(this).val('');
		}
	}).blur(function() {
		var search_name = $.trim($(this).val());
		if(!search_name) {
			$(this).val(default_search_name);
		}
	});
	
	//搜索按钮
	$('#search_btn', context).click(function() {
		var search_name = $('#search_name', context).val();
		if(!$.trim(search_name)) {
			$.showError('请先输入好友姓名!');
			return false;
		}
		
		Friend.registerFilters({
			type:'post',
			url:'/Sns/Friend/Manage/search_my_friend',
			data:{search_name:search_name},
			async:false,
			success:function(json) {
				if(json.status < 0) {
					alert(json.info);
					return false;
				}
				var data = json.data || {};
				me.fillFriendList(data);
			}
		});
		
	});
};

Friend.prototype.attachEventForLoadMore=function() {
	var me = this;
	//加载更多
	$('#load_more_a').click(function() {
		//获取相关的页数设置
		var page = $(this).data('page');
		page = page > 1 ? page : 1;
		//处理相关的数据
		var handler = $(this).data('handler') || $.noop;
		handler(page + 1);
		$(this).data('page', page + 1);
		
		return false;
	});
};

//注册想要的过了条件到加载更多按钮
Friend.registerFilters=function(options) {
	options = options || {};
	$('#load_more_a').show();
	//清空已有的好友列表信息
	$('#friend_list_div').children(':gt(0)').remove();
	//将操作的句柄绑定到load_more_a元素上
	var handler = function(page) {
		page = page > 1 ? page : 1;
		$.ajax({
			type:options.type || 'get',
			url:options.url + "/page/" + page,
			data:options.data || {},
			dataType:options.dataType || 'json',
			success:function(json) {
				json = json || {};
				if(typeof options.success == 'function') {
					options.success(json);
				}
				if($.isEmptyObject(json.data)) {
					$('#load_more_a').hide();
				}
				
			}
		});
	};
	$('#load_more_a').data('handler', handler);
	$('#load_more_a').data('page', 1);
	//加载第一页信息
	handler(1);
};

//填充好友列表
Friend.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone', $('#friend_list_div')).clone().removeClass('clone').show();
		divObj.renderHtml({
			user:user || {}
		});
		return divObj;
	};
	
	var parentObj = $('#friend_list_div');
	for(var i in user_list) {
		var user = user_list[i];
		var divObj = createDiv(user || {});
		divObj && parentObj.append(divObj);
	}
};

Friend.prototype.delegateEvent=function() {
	var me = this;
	
	//修改分组按钮
	$('#friend_list_div').delegate('.change_friend_group_selector', 'click', function() {
		var aObj = $(this);
		var ancestorObj = aObj.closest('.unit_selector');
		$('#friend_group_menu_div').trigger('openEvent', [{
			follow:aObj[0],
			datas:{
				group_id:$('.group_id_selector', ancestorObj).val(),
				relation_id:$('.relation_id_selector', ancestorObj).val()
			},
			//将新的分组信息重写
			callback:function(new_group_id) {
				$('.group_id_selector', ancestorObj).val(new_group_id);
			}
		}]);
		return false;
	});
	
	//删除好友关系
	$('#friend_list_div').delegate('.delete_friend_relation_selector', 'click', function() {
		var ancestorObj = $(this).closest('.unit_selector');
		$('#delete_friend_relation_div').trigger('openEvent', [{
			datas:{
				friend_account:$('.friend_account_selector', ancestorObj).val(),
				client_name:$('#client_name_p', ancestorObj).html()
			},
			callback:function() {
				ancestorObj.remove();
			}
		}]);
	});
	
	//发送私信
	$('#friend_list_div').delegate('.send_private_smg_selector', 'click', function() {
		var aObj = $(this);
		var ancestorObj = $(this).closest('.unit_selector');
		$('#send_private_msg_div').trigger('openEvent', [{
			datas: {
				client_name:$('#client_name_p', ancestorObj).html(),
				friend_account:$('.friend_account_selector', ancestorObj).val()
			},
			follow:aObj.get(0)
		}]);
	});
};

//委托左侧好友分类相关的事件
Friend.prototype.delegateEventForLeftMenu=function() {
	//删除好友分组
	var me = this;
	$('.group_delete_selector_a').live('click', function() {
		var ancestorObj = $(this).closest('li');
		var group_id = ancestorObj.attr('id').toString().match(/(\d+)/)[1];
		$('#delete_friend_group_div').trigger('openEvent', [{
			datas:{
				group_id:group_id
			},
			follow:ancestorObj.get(0),
			callback:function() {
				$('body').trigger('refreshGroupEvent');
			}
		}]);
	});
	
	//添加好友分组
	$('#add_group_btn').live('click', function() {
		var btnObj = $(this);
		$('#add_friend_group_div').trigger('openEvent', [{
			follow:btnObj.get(0),
			callback:function() {
				$('body').trigger('refreshGroupEvent');
			}
		}]);
	});
	
	//编辑好友分组
	$('.group_edit_selector_a').live('click', function() {
		var ancestorObj = $(this).closest('li');
		var group_name = $('.group_name_selector', ancestorObj).val();
		var group_id = ancestorObj.attr('id').toString().match(/(\d+)/)[1];
		
		$('#edit_friend_group_div').trigger('openEvent', [{
			datas:{
				group_id:group_id,
				group_name:group_name
			},
			follow:ancestorObj.get(0),
			callback:function(new_group_name) {
				var html = $('.group_name_selector_a', ancestorObj).html();
				$('.group_name_selector_a', ancestorObj).html(new_group_name + html.match(/\(.+\)/)[0]);
				$('.group_name_selector', ancestorObj).val(new_group_name);
			}
		}]);
	});
	
	//隐藏和展示编辑删除按钮
	$('.group_list_selector').live('mouseover', function() {
		$("#edit_del_btn",$(this)).show();
	});
	
	$('.group_list_selector').live('mouseout', function() {
		$("#edit_del_btn",$(this)).hide();
	});
	
	//点击分类
	$('.group_name_selector_a').live('click', function() {
		var ancestorObj = $(this).closest('li');
		var group_name = $('.group_name_selector', ancestorObj).val();
		var group_id = ancestorObj.attr('id').toString().match(/(\d+)/)[1];
		Friend.registerFilters({
			type:'post',
			url:'/Sns/Friend/Manage/getMyFriendByGroupIdAjax',
			dataType:'json',
			data:{
				group_id:group_id
			},
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				me.fillFriendList(json.data || {});
			}
		});
	});
	
	
};

//初始化函数列表
Friend.initFunctions=[];
Friend.register=function(func) {
	if(typeof func == 'function') {
		Friend.initFunctions.push(func);
	}
};

Friend.globalInit=function() {
	while(!$.isEmptyObject(Friend.initFunctions)) {
		var fn = Friend.initFunctions.pop();
		if(typeof fn == 'function') {
			fn.call();
		}
	}
};

//添加好友分组
Friend.register(function() {
	var divObj = $('#add_friend_group_div');
	
	$('#add_friend_group_div').bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			art.dialog({
				id:'add_friend_group_dialog',
				title:'添加好友分组',
				content:divObj.get(0),
				follow:options.follow || {},
				init:function() {
					$('*[name="group_name"]', divObj).val('');
				}
			});
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['add_friend_group_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//事件绑定
	$('#sure_btn', divObj).bind('click', function() {
		var group_name = $('*[name="group_name"]', divObj).val();
		var group_type = $('*[name="group_type"]', divObj).val();
		if(!$.trim(group_name)) {
			$.showError('分组名称不能为空!');
			return false;
		}
		var options = divObj.data('options') || {};
		//数据提交
		$.ajax({
			type:'post',
			url:'/Sns/Friend/Manage/addGroupAjax',
			data:{
				group_name:group_name,
				group_type:group_type
			},
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				if(typeof options.callback == 'function') {
					options.callback();
				}
				//关闭弹出层
				divObj.trigger('closeEvent');
			}
		});
	});
	
	//取消按钮
	$('#cancel_btn', divObj).click(function() {
		divObj.trigger('closeEvent');
	});
});

//编辑好友分组
Friend.register(function() {
	var divObj = $('#edit_friend_group_div');
	divObj.bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			art.dialog({
				id:'edit_friend_group_dialog',
				title:'编辑好友分组',
				content:divObj.get(0),
				follow:options.follow || {},
				padding:'0px',
				init:function() {
					var datas = options.datas || {};
					$('*[name="group_id"]', divObj).val(datas.group_id);
					$('*[name="group_name"]', divObj).val(datas.group_name).focus();
				}
			});
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['edit_friend_group_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//确定按钮
	$('#sure_btn', divObj).click(function() {
		var group_id = $('*[name="group_id"]', divObj).val();
		var group_name = $('*[name="group_name"]', divObj).val();
		if(!$.trim(group_name)) {
			$.showError('分组名称不能为空!');
			return false;
		}
		var options = divObj.data('options') || {};
		//判断名字是否做了修改
		var old_group_name = options.datas.group_name;
		if($.trim(group_name) == $.trim(old_group_name)) {
			//关闭弹出层
			divObj.trigger('closeEvent');
			return false;
		}
		//数据提交
		$.ajax({
			type:'post',
			url:'/Sns/Friend/Manage/modify_group',
			data:{
				group_name:group_name,
				group_id:group_id
			},
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				if(typeof options.callback == 'function') {
					options.callback(group_name);
				}
				//关闭弹出层
				divObj.trigger('closeEvent');
			}
		});
	});
	
	//取消按钮
	$('#cancel_btn', divObj).click(function() {
		divObj.trigger('closeEvent');
	});
});

//删除好友分组
Friend.register(function() {
	var divObj = $('#delete_friend_group_div');
	divObj.bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			art.dialog({
				id:'delete_friend_group_dialog',
				title:'删除好友分组',
				content:divObj.get(0),
				init:function() {
					var datas = options.datas || {};
					$('#group_id', divObj).val(datas.group_id);
				}
			}).lock();
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['delete_friend_group_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//确定按钮
	$('#sure_btn', divObj).click(function() {
		var group_id = $('#group_id', divObj).val();
		var options = divObj.data('options') || {};
		$.ajax({
			type:'get',
			url:'/Sns/Friend/Manage/del_group/group_id/' + group_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				if(typeof options.callback == 'function') {
					options.callback();
				}
				divObj.trigger('closeEvent');
			}
		});
	});

	//取消按钮
	$('#cancel_btn', divObj).click(function() {
		divObj.trigger('closeEvent');
	});
	
});

//删除好友关系
Friend.register(function() {
	var divObj = $('#delete_friend_relation_div');
	divObj.bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			art.dialog({
				id:'delete_friend_relation_dialog',
				title:'删除好友关系',
				content:divObj.get(0),
				init:function() {
					var datas = options.datas || {};
					$('#friend_account', divObj).val(datas.friend_account);
					datas.client_name && $('#client_name', divObj).html("(" + datas.client_name + ")");
				}
			}).lock();
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['delete_friend_relation_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	$('#sure_btn', divObj).click(function() {
		var friend_account = $('#friend_account', divObj).val();
		var options = divObj.data('options') || {};
		$.ajax({
			type:'get',
			url:'/Sns/Friend/Manage/delAccountRelationAjax/relation_account/' + friend_account,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				if(typeof options.callback == 'function') {
					options.callback();
				}
				
				divObj.trigger('closeEvent');
				$('body').trigger('refreshGroupEvent');
			}
		});
	});
	
	//关闭按钮
	$('#cancel_btn', divObj).click(function() {
		divObj.trigger('closeEvent');
	});
	
});

//修改好友的分组关系
Friend.register(function() {
	var divObj = $('#friend_group_menu_div');
	divObj.bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			//只允许单个实例出现
			divObj.trigger('closeEvent');
			art.dialog({
				id:'friend_group_menu_dialog',
				title:'修改好友分组',
				content:divObj.get(0),
				follow:options.follow || {},
				init:function() {
					var datas = options.datas || {};
					var group_id = datas.group_id || 0;
					var parentObj = $('#friend_group_menu_p');
					$('a', parentObj).css('background', '');
					$('a[id="group_' + group_id + '"]', parentObj).css('background', '#72ad00');
					$('#relation_id', divObj).val(datas.relation_id);
				}
			});
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['friend_group_menu_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//委托a元素的点击事件
	divObj.delegate('a', 'click', function() {
		var aObj = $(this);
		var group_id = $(this).attr('id').toString().match(/(\d+)/)[1];
		var relation_id = $('#relation_id', divObj).val();
		$.ajax({
			type:'get',
			url:'/Sns/Friend/Manage/moveFriendGroupAjax/relation_id/' + relation_id + '/group_id/' + group_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				var options = divObj.data('options') || {};
				if(typeof options.callback == 'function') {
					options.callback(group_id);
				}
				
				divObj.trigger('closeEvent');
				$('body').trigger('refreshGroupEvent');
			}
		});
	});
});

//发送私信
Friend.register(function() {
	var divObj = $('#send_private_msg_div');
	
	divObj.bind({
		openEvent:function(evt, options) {
			options = options || {};
			divObj.data('options', options);
			divObj.trigger('closeEvent');
			art.dialog({
				id:'send_private_msg_div',
				title:'发送私信',
				content:divObj.get(0),
				follow:options.follow || {},
				init:function() {
					var datas = options.datas || {};
					$('#client_name', divObj).val(datas.client_name);
					$('#friend_account', divObj).val(datas.friend_account);
					//初始化发送框相关的事件
					var inited = divObj.data('inited') || false;
					if(!inited) {
						initSendBox();
						divObj.data('inited', true);
					}
				}
			});
		},
		
		closeEvent:function() {
			var dialogObj = art.dialog.list['send_private_msg_div'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//初始化sendBox相关的代码
	function initSendBox() {
		var sendBoxObj = $('#content', divObj).sendBox({
			panels:'emote,upload',
			type:'post',
			url:'/Sns/Friend/Manage/sendPrivateSmgAjax',
			dataType:'json',
			//表单提交前得验证
			beforeSubmit:function() {
				var formObj = sendBoxObj._jForm || {};
				var content = $('*[name="content"]', formObj).val();
				if(!$.trim(content)) {
					$.showError('私信内容不能为空!');
					return false;
				}
				var friend_account = $('#friend_account', divObj).val();
				if($('*[name="friend_account"]').length == 0) {
					$('<input type="hidden" name="friend_account" value="' + friend_account + '" />').appendTo(formObj);
				} else {
					$('*[name="friend_account"]').val(friend_account);
				}
				return true;
			},
			//发表成功后的回调函数
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				divObj.trigger('closeEvent');
			}
		});
	}
});


$(document).ready(function() {
	Friend.globalInit();
	new Friend();
});


$(document).ready(function() {
//	$(window).resize(function() {
//		//alert($(document).scrollLeft());
//		//alert($(document).scrollTop());
//	});
	
//	$(window).bind('scroll', function() {
//		alert('call me scroll!');
//	});
	
//	function check() {
//		var divObj = $('#div_follow');
//		var offset = divObj.offset();
//		for(var i in offset) {
//			alert(i + "=>" + offset[i]);
//		}
//		
//		setTimeout(function() {
//			check();
//		}, 2000);
//	}
//	
//	check();
});




//关于follow实现的研究

/**
 * 跟随元素
 * @param	{HTMLElement, String}
 */
//function follow (elem) {
//	var $elem, that = this, config = that.config;
//	
//	if (typeof elem === 'string' || elem && elem.nodeType === 1) {
//		$elem = $(elem);
//		elem = $elem[0];
//	};
//	
//	// 隐藏元素不可用
//	if (!elem || !elem.offsetWidth && !elem.offsetHeight) {
//		return that.position(that._left, that._top);
//	};
//	
//	var expando = _expando + 'follow',
//		winWidth = _$window.width(),
//		winHeight = _$window.height(),
//		docLeft =  _$document.scrollLeft(),
//		docTop = _$document.scrollTop(),
//		offset = $elem.offset(),
//		width = elem.offsetWidth,
//		height = elem.offsetHeight,
//		isFixed = _isIE6 ? false : config.fixed,
//		left = isFixed ? offset.left - docLeft : offset.left,
//		top = isFixed ? offset.top - docTop : offset.top,
//		wrap = that.DOM.wrap[0],
//		style = wrap.style,
//		wrapWidth = wrap.offsetWidth,
//		wrapHeight = wrap.offsetHeight,
//		setLeft = left - (wrapWidth - width) / 2,
//		setTop = top + height,
//		dl = isFixed ? 0 : docLeft,
//		dt = isFixed ? 0 : docTop;
//	
//	setLeft = setLeft < dl ? left :
//	(setLeft + wrapWidth > winWidth) && (left - wrapWidth > dl)
//	? left - wrapWidth + width
//	: setLeft;
//
//	setTop = (setTop + wrapHeight > winHeight + dt)
//	&& (top - wrapHeight > dt)
//	? top - wrapHeight
//	: setTop;
//	
//	style.left = setLeft + 'px';
//	style.top = setTop + 'px';
//	
//	that._follow && that._follow.removeAttribute(expando);
//	that._follow = elem;
//	elem[expando] = config.id;
//	that._autoPositionType();
//	return that;
//}















