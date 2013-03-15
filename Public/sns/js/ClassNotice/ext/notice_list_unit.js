(function($) {
	$.fn.sprintfHtml=function(str) {
		var html = this.html().toString().replace('%s', str);
		this.html(html);
	};
})(jQuery);

function notice_list_unit() {
	this.attachEventUserDefine();
	this.delegateEvent();
}
//用户自定义事件用户页面之间的交互
notice_list_unit.prototype.attachEventUserDefine=function() {
	//老师模板的克隆事件
	$('#clone_teacher').bind('cloneEvent', function(event, options) {
		options = options || {};
		var notice_datas = options.notice_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('notice_datas', notice_datas);
		//数据填充
		$('#notice_title', divObj).html(notice_datas.notice_title);
		$('#notice_content', divObj).html(notice_datas.notice_content);
		$('#add_time', divObj).sprintfHtml(notice_datas.add_time);
		$('#client_name', divObj).sprintfHtml(notice_datas.client_name);
		//按钮的处理
		var access_list = notice_datas.notice_access_list || {};
		//是否显示删除按钮
		var can_delete = access_list.can_delete;
		//是否有权限发送短信信息
		var is_send = access_list.is_send;
		//是否已经发送，1表示已经发送，0表示为发送
		var is_sms = notice_datas.is_sms;
		is_sms = parseInt(is_sms);
		if(isNaN(is_sms) || is_sms < 0) {
			is_sms = 0;
		}
		//处理发送短信按钮
		$('#send_icon,#send_des,#sended_icon,#sended_des', divObj).hide();
		if(is_send) {
			if(is_sms) {
				$('#sended_icon,#sended_des', divObj).show();
			} else {
				$('#send_icon,#send_des', divObj).show();
			}
		}
		//处理删除按钮
		if(!can_delete) {
			$('.delete_a_selector', divObj).parents('li:first').remove();
		}
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
	
	//学生模板的克隆事件
	$('#clone_student').bind('cloneEvent', function(event, options) {
		options = options || {};
		var notice_datas = options.notice_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('notice_datas', notice_datas);
		//数据填充
		$('#notice_title', divObj).html(notice_datas.notice_title);
		$('#notice_content', divObj).html(notice_datas.notice_content);
		$('#add_time', divObj).sprintfHtml(notice_datas.add_time);
		$('#client_name', divObj).sprintfHtml(notice_datas.client_name);
		//按钮的处理
		var show_know_btn = (notice_datas.notice_access_list || {}).show_know_btn;
		$('#viewed_des,#set_view_a', divObj).hide();
		if(show_know_btn) {
			$('#set_view_a', divObj).show();
		} else {
			$('#viewed_des', divObj).show();
		}
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
	
	//家长模板的克隆事件
	$('#clone_family').bind('cloneEvent', function(event, options) {
		options = options || {};
		var notice_datas = options.notice_datas || {};
		//克隆div并将数据进行绑定
		var divObj = $(this).clone().attr('id', '').css('display', 'block');
		divObj.data('notice_datas', notice_datas);
		//数据填充
		$('#notice_title', divObj).html(notice_datas.notice_title);
		$('#notice_content', divObj).html(notice_datas.notice_content);
		$('#add_time', divObj).sprintfHtml(notice_datas.add_time);
		$('#client_name', divObj).sprintfHtml(notice_datas.client_name);
		//回调函数处理复制后的对象
		if(typeof options.callback == 'function') {
			options.callback(divObj);
		}
	});
};
//事件委托
notice_list_unit.prototype.delegateEvent=function() {
	//发送短信
	$('body').delegate('.send_sms_btn_selector', 'click', function() {
		var ancestorObj = $(this).parents('.has_release_main:first');
		var notice_datas = ancestorObj.data('notice_datas') || {};
		$.ajax({
			type:'get',
			url:'/Sns/ClassNotice/Publish/notice_list_send/notice_id/' + notice_datas.notice_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//修改短信的状态
				$('#sended_icon,#sended_des', ancestorObj).show();
				$('#send_icon,#send_des', ancestorObj).hide();
			}
		});
	});
	
	//查看回执
	$('body').delegate('.refer_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.has_release_main:first');
		var notice_datas = ancestorObj.data('notice_datas') || {};
		//打开查看回执的弹出层
		$('#notice_refer_div').trigger('openEvent', [{
			notice_datas:notice_datas,
			follow:ancestorObj.get(0)
		}]);
	});
	
	//删除按钮deleteClassNoticeAjax
	$('body').delegate('.delete_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.has_release_main:first');
		var notice_datas = ancestorObj.data('notice_datas') || {};
		//打开删除层
		$('#delete_prompt_div').trigger('openEvent', [{
			notice_datas:notice_datas,
			callback:function() {
				//删除父级容器
				ancestorObj.remove();
			}
		}]);
	});
	
	//老师， 我知道了
	$('body').delegate('.i_know_btn_selector', 'click', function() {
		var ancestorObj = $(this).parents('.has_release_main:first');
		var notice_datas = ancestorObj.data('notice_datas') || {};
		$.ajax({
			type:'get',
			url:'/Sns/ClassNotice/Manage/setNoticeKnowAjax/notice_id/' + notice_datas.notice_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//重置按钮的相关信息
				$('#set_view_a', ancestorObj).hide();
				$('#viewed_des', ancestorObj).show();
			}
		});
	});
};

$(document).ready(function() {
	new notice_list_unit();
});