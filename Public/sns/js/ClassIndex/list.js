(function($) {
	$.showError=function(msg){
		art.dialog({
			title:'错误提示',
			content:msg,
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			title:'成功提示',
			content:msg,
			icon:'succeed'
		}).lock().time(3);
	};
	$.fn.sprintfHtml=function(str) {
		var html = this.html().toString().replace('%s', str);
		this.html(html);
	};
	//简单的数据渲染,支持反复渲染
	$.fn.renderHtml=function(datas) {
		datas = datas || {};
		//判断页面是否渲染过
		if(!this.data('is_rendered')) {
			this.data('tpl_html', this.html().toString());
			this.data('is_rendered', true);
		}
		var tpl_html = this.data('tpl_html');
		var html = tpl_html.toString().replace(/\{([^\}]+?)\}/ig, function(a, b) {
			return datas[b] || "";
		});
		this.html(html);
	};
})(jQuery);

function mail_list() {
	this.delegateEvent();
}

mail_list.prototype.delegateEvent=function() {
	var me = this;
	$('body').delegate('.send_phone', 'click', function() {
		var aObj = $(this);
		//提取的次序是按钮的父级div，祖父级div
		var parentObj = $(this).parents('.list_main_nr:first');
		var ancestorObj = parentObj.parents('.list_main:first');
		//数据提取
		var client_name = $('.client_name_selector', ancestorObj).html();
		var phone_id = $('.phone_id_selector', parentObj).html();
		var parent_type = $('.parent_type_selector', parentObj).html();
		var parent_account = $('.parent_account_selector', parentObj).html();
		//打开弹出
		//关闭已经打开的对话框
		$('#send_sms_div').trigger('openEvent', [{
			'datas': {
				'client_name':client_name,
				'phone_id':phone_id,
				'parent_type':parent_type,
				'parent_account':parent_account
			},
			'follow' : aObj.get(0)
		}]);
		
		return false;
	});
};

function send_sms() {
	this.max_length = 60;
	this.limitInterval = null;
	
	this.attachEvent();
	this.attachEventUserDefine();
}

send_sms.prototype.attachEvent=function() {
	var me = this;
	var context = $('#send_sms_div');
	$('#sms_content', context).focus(function() {
		me.limitInterval = setInterval(function() {
			var len = $.trim($('#sms_content').val()).toString().length;
			if(len > me.max_length){
				$(".span_width").html("超出<b><font size=3 color=red>" + (len - me.max_length) + "</font></b>字无法进行保存!");
				return false;
			}
			$(".span_width").html("还能输入<font size=2 color=red>" + (me.max_length - len) + "</font>字");
			return true;
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
	
	//关闭弹出层
	$('#isure_btn', context).click(function() {
		var ancestorObj = $('#send_sms_div');
		var options = ancestorObj.data('options') || {};
		var datas = options.datas || {};
		var parent_account = datas.parent_account;
		var sms_content = $('#sms_content').val();
		var class_code = $('#class_code').val();
		if(!sms_content) {
			$.showError('短信内容不能空!');
			return false;
		}
		$.ajax({
			type:'post',
			url:'/Sns/ClassIndex/Mailbook/maillist_send_phone/class_code/' + class_code,
			dataType:'json',
			data:{
				'parent_account':parent_account,
				'sms_content' : sms_content
			},
			success:function(json) {
				//关闭当前的编辑对话框 
				ancestorObj.trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
			}
		});
		$('#sms_content').val('');
	});
	//取消按钮
	$('#cancel_btn', context).click(function() {
		$('#send_sms_div').trigger('closeEvent');
	});
};

send_sms.prototype.attachEventUserDefine=function() {
	//注册py_input_div的初始事件
	$('#send_sms_div').bind({
		openEvent:function(evt, options) {
			options = options || {};
			//将数据绑定到div上
			$(this).data('options', options);
			art.dialog({
				id:'send_sms_dialog',
				//lock: true,
				title:'短信编辑',
				content:$('#send_sms_div').get(0),
				follow:options.follow || {},
				init:function() {
					$('#prompt_h1', $('#send_sms_div')).renderHtml(options.datas || {});
		    	}
			});
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['send_sms_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

$(document).ready(function() {
	new mail_list();
	new send_sms();
});