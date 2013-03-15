(function($) {
	$.fn.sprintfHtml=function(str) {
		var html = this.html().toString().replace('%s', str);
		this.html(html);
	};
})(jQuery);

/* 注明：
 * 1. 事件绑定使用的是事件的委托delegate函数;
 * 2. 使用向上查找最小关系域圈定范围避免不同元素之间通过id建立映射关系；
 * 3. 克隆div的时候使用各自的cloneEvent与外界进行交互;
 * 4. 页面使用的数据隐藏和回调函数
 * 5. 委托给自己关联的元素可以减少程序的复杂度;
 */
function homework_list_unit() {
	this.cache_accepters = {};
	this.attachEvent();
}

//事件绑定
homework_list_unit.prototype.attachEvent=function() {
	this.delegateEvent();
	this.attachEventForUserDefine();
	this.attachEventForShowDeleteDiv();
};

//事件委托
homework_list_unit.prototype.delegateEvent=function() {
	var me = this;
	//查看回执
	$('body').delegate('.show_receipt_btn_selector', 'click', function() {
		//变换层的显示/隐藏效果
		var toggled_nums = $(this).data('toggled_nums') || 1;
		if(toggled_nums % 2 != 0) {
			var ancestorObj = $(this).parents('.work_main:first');
			var homework_datas = ancestorObj.data('homework_datas') || {};
			$('body').trigger('openShowHomeworkReplyPopDivEvent', [{
				'homework_id':homework_datas.homework_id,
				'follow':ancestorObj.get(0)
			}]);
		} else {
			$('body').trigger('closeShowHomeworkReplyPopDivEvent');
		}
		$(this).data('toggled_nums', toggled_nums + 1);
	});
	
	//查看作业详情
	$('body').delegate('.show_detail_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.work_main:first');
		//判断元素是否已经初始化,没有初始化则创建
		if($('.show_homework_selector', ancestorObj).length == 0) {
			var homework_datas = ancestorObj.data('homework_datas') || {};
			var divObj = $('#show_homework').clone().attr('id', '').addClass('show_homework_selector').css('display', 'block');
			$('#show_homework_content', divObj).html(homework_datas.content);
			//绑定事件
			$('#downloadfile', divObj).click(function() {
				window.location.href='/Sns/ClassHomework/Published/download_file/homework_id/' + homework_datas.homework_id;
			});
			//附件信息内容的填充
			if(homework_datas.attachment) {
				$('#downfile', divObj).html(homework_datas.attachment_name);
			}
			//将元素追加到相应的容器中
			$('#homework_detail', ancestorObj).append(divObj);
		}
		//变换层的显示/隐藏效果
		var toggled_nums = $(this).data('toggled_nums') || 1;
		if(toggled_nums % 2 != 0) {
			$('#homework_detail', ancestorObj).show();
		} else {
			$('#homework_detail', ancestorObj).hide();
		}
		$(this).data('toggled_nums', toggled_nums + 1);
	});
	
	//删除按钮
	$('body').delegate('.delete_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.work_main:first');
		var homework_datas = ancestorObj.data('homework_datas') || {};
		var btnObj = $(this);
		$('body').trigger('openShowDeletePopDivEvent', [{
			'homework_id':homework_datas.homework_id,
			'follow':btnObj.get(0),
			'callback':function() {
				ancestorObj.remove();
			}
		}]);
		return false;
	});
	
	//发送短信
	$('body').delegate('.send_msg_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.work_main:first');
		var homework_datas = ancestorObj.data('homework_datas') || {};
		$.ajax({
			type:"post",
			url:"/Sns/ClassHomework/Publish/SendReissue",
			data:{'homework_id':homework_datas.homework_id},
			dataType:"json",
			async:false,
			success:function(json) {
				if(json.status < 0){
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//按钮的显示的切换
				$('#sended_img,#sended_des', ancestorObj).show();
				$('#send_img,#send_des', ancestorObj).hide();
			}
		});
		//阻止a元素的事件冒泡和默认行为导致的页面跳动
		return false;
	});
	
	//老师我知道了
	$('body').delegate('.i_know_btn_selector', 'click', function() {
		var btnObj = $(this);
		var ancestorObj = btnObj.parents('.work_main:first');
		var homework_datas = ancestorObj.data('homework_datas') || {};
		$.ajax({
			type:'get',
			url:'/Sns/ClassHomework/Manage/setHomeworkKnowAjax/homework_id/' + homework_datas.homework_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//成功后删掉该按钮
				btnObj.parents('a:first').remove();
			}
		});
	});
};

//自定义事件用户外界通讯
homework_list_unit.prototype.attachEventForUserDefine=function() {
	var me = this;
	$('body').bind({
		//弹层作业回执页面
		'openShowHomeworkReplyPopDivEvent':function(event, options) {
			options = options || {};
			var homework_id = options.homework_id;
			art.dialog({
				id:'show_homework_reply_dialog',
				title:'查阅回执名单',
				content:$('#homework_reply').get(0),
				follow:options.follow || {},
				init:function() {
					//将数据绑定到homework_reply上
					var accepters_datas = me.loadHomeworkAccepters(homework_id);
					//数据填充
					me.fillHomeworkReplyDiv(accepters_datas);
				}
			});
		},
		//关闭按钮
		'closeShowHomeworkReplyPopDivEvent':function() {
			var dialogObj = art.dialog.list['show_homework_reply_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		},
		//弹出层删除的确定按钮
		'openShowDeletePopDivEvent':function(event, options) {
			var divObj = $('#show_delete_div');
			divObj.data('options', options || {});
			art.dialog({
				id:'show_delete_dialog',
				title:'删除提示',
				follow:options.follow || {},
				content:divObj.get(0)
			});
		},
		//删除框的关闭事件
		'closeShowDeletePopDivEvent':function() {
			var dialogObj = art.dialog.list['show_delete_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
	//老师模板的克隆事件
	$('#clone_teacher').bind('cloneEvent', function(event, options) {
		options = options || {};
		var homework_datas = options.homework_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('homework_datas', homework_datas);
		//数据填充
		$('#end_time', divObj).sprintfHtml(homework_datas.end_time);
		$('#status', divObj).sprintfHtml(homework_datas.status);
		$('#accepters', divObj).sprintfHtml(homework_datas.accepters);
		$('#add_time', divObj).sprintfHtml(homework_datas.add_time);
		$('#client_name', divObj).sprintfHtml(homework_datas.client_name);
		$('#subject_name', divObj).html(homework_datas.subject_name);
		//按钮的处理
		var access_list = homework_datas.homework_access_list || {};
		//是否显示删除按钮
		var can_delete = access_list.can_delete;
		//是否有权限发送短信信息
		var is_send = access_list.is_send;
		//是否已经发送，1表示已经发送，0表示为发送
		var is_sms = homework_datas.is_sms;
		//处理发送短信按钮
		$('#sended_img,#sended_des,#send_img,#send_des', divObj).hide();
		if(is_send) {
			if(is_sms) {
				$('#sended_img,#sended_des', divObj).show();
			} else {
				$('#send_img,#send_des', divObj).show();
			}
		}
		//处理删除按钮
		if(!can_delete) {
			$('.delete_a_selector', divObj).remove();
		}
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
	
	//学生模板的克隆事件
	$('#clone_student').bind('cloneEvent', function(event, options) {
		options = options || {};
		var homework_datas = options.homework_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('homework_datas', homework_datas);
		//数据填充
		$('#end_time', divObj).sprintfHtml(homework_datas.end_time);
		$('#status', divObj).sprintfHtml(homework_datas.status);
		$('#add_time', divObj).sprintfHtml(homework_datas.add_time);
		$('#client_name', divObj).sprintfHtml(homework_datas.client_name);
		//按钮的处理
		var show_know_btn = (homework_datas.homework_access_list || {}).show_know_btn;
		if(!show_know_btn) {
			$('.i_know_btn_selector', divObj).parents('a:first').remove();
		}
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
	
	//家长模板的克隆事件
	$('#clone_family').bind('cloneEvent', function(event, options) {
		options = options || {};
		var homework_datas = options.homework_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('homework_datas', homework_datas);
		//数据填充
		$('#end_time', divObj).sprintfHtml(homework_datas.end_time);
		$('#status', divObj).sprintfHtml(homework_datas.status);
		$('#add_time', divObj).sprintfHtml(homework_datas.add_time);
		$('#client_name', divObj).sprintfHtml(homework_datas.client_name);
		//按钮的处理
		var show_know_btn = (homework_datas.homework_access_list || {}).show_know_btn;
		if(!show_know_btn) {
			$('.i_know_btn_selector', divObj).parents('a:first').remove();
		}
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
};

//绑定和删除的弹出层相关的事件
homework_list_unit.prototype.attachEventForShowDeleteDiv=function() {
	var context = $('#show_delete_div');
	//删除确定按钮
	$('#sure_btn', context).click(function() {
		//此处的代码不能提到外面去，因为闭包的范围不一致
		var options = context.data('options') || {};
		var homework_id = options.homework_id;
		$.ajax({
			type:'get',
			url:'/Sns/ClassHomework/Manage/deleteHomeworkAjax/homework_id/' + homework_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//删除成功后的回调函数处理
				if(typeof options.callback == 'function') {
					options.callback();
				}
				//关闭弹出层
				$('body').trigger('closeShowDeletePopDivEvent');
			}
		});
	});
	//删除取消按钮
	$('#cancel_btn', context).click(function() {
		$('body').trigger('closeShowDeletePopDivEvent');
	});
};

//加载作业的接受对象信息,有js缓存
homework_list_unit.prototype.loadHomeworkAccepters=function(homework_id) {
	var me = this;
	var cache_datas = me.cache_accepters[homework_id];
	if($.isEmptyObject(cache_datas)) {
		$.ajax({
			type:"post",
			url:"/Sns/ClassHomework/Published/accepters_json",
			data:{'homework_id':homework_id},
			dataType:"json",
			async:false,
			success:function(json) {
				cache_datas = me.cache_accepters[homework_id] = json.data || {};
			}
		});
	}
	return cache_datas || {};
};

//填充作业的接受对象
homework_list_unit.prototype.fillHomeworkReplyDiv=function(datas) {
	datas = datas || {};
	var contextDiv = $('#homework_reply');
	//填充已回执
	var num_id = 1;
	var html_str = "<tr>";
	var viewed_list = datas.viewed_list || {};
	for(var i in viewed_list) {
		html_str += "<td>" + viewed_list[i].client_name + "</td>";
		if(num_id++ % 8 == 0) {
			html_str += "</tr><tr>";
		}
	}
	var append_nums = 8 - (num_id - 1) % 8;
	for(var i=1; i<=append_nums; i++) {
		html_str += "<td>&nbsp;</td>";
	}
	$('table:eq(0)', contextDiv).html(html_str);
	$("#viewed_num").html(datas.viewed_num);
	
	//填充未回执
	var num_id = 1;
	var html_str = "<tr>";
	var no_view_list = datas.no_view_list || {};
	for(var i in no_view_list) {
		html_str += "<td>" + no_view_list[i].client_name + "</td>";
		if(num_id++ % 8 == 0) {
			html_str += "</tr><tr>";
		}
	}
	var append_nums = 8 - (num_id - 1) % 8;
	for(var i=1; i<=append_nums; i++) {
		html_str += "<td>&nbsp;</td>";
	}
	var tabObj = $('table:eq(1)', contextDiv).html(html_str);
	$("#no_view_num").html(datas.no_view_num);
};

$(document).ready(function() {
	new homework_list_unit();
});