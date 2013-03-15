(function() {
	$.showError=function(msg) {
		$('#prompt_div').trigger('openEvent', [{
			'msg':msg
		}]);
	};
	
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_success_dialog',
			title:'成功提示',
			content:msg,
			icon:'succeed'
		}).lock().time(3);
	};
})(jQuery);


function publish() {
	this.max_length =180;
	this.limitInterval = null;
	this.attachEvent();
	this.attachEventForStudentListDiv();
	this.attachEventForAcceptListDiv();
};

publish.prototype.attachEvent = function() {
	var me = this;
	//短信内容字数控制
	var context = $('#send_sms_div');
	$('#sms_content', context).focus(function() {
		me.limitInterval = setInterval(function() {
			var len = $.trim($('#sms_content').val()).toString().length;
			if(len > me.max_length){
				$(".span_width").html("超出<b><font size=3 color=red>" + (len - me.max_length) + "</font></b>字无法进行保存!");
				return false;
			}
			$(".span_width").html("还能输入" + (me.max_length - len) + "字");
			return true;
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
};

publish.prototype.attachEventForStudentListDiv=function() {
	var me = this;
	var context = $('#student_list_div');
	
	//全选按钮
	$("#checkall", context).click(function() {
		$(".checked_selector", context).attr("checked",$(this).attr("checked"));
	});
	
	$(".checked_selector", context).click(function() {
		if(!$(this).attr('checked')) {
			$("#checkall", context).attr('checked', false);
		}
	});
	
	//添加发送对象按钮
	$('#sure_btn', context).click(function() {
		//收集选中的相关数据
		var accept_name_list = [];
		var selected_accounts = [];
		var phone_arr =[];
		
		$('.checked_selector', context).each(function() {
			if(!$(this).attr('checked')) {
				return true;
			}
			var trObj = $(this).parents('tr:first');
			var client_account = trObj.attr('id').toString().match(/(\d+)/)[1];
			var client_name = $('.client_name_selector', trObj).html();
			var father_phone_id = $("#father_phone_id",trObj).val();
			var mother_phone_id = $("#mother_phone_id",trObj).val();
			
			accept_name_list.push({
				father_name : client_name + "父亲",
				mother_name : client_name + "母亲",
			    father_phone_id: father_phone_id,
			    mother_phone_id: mother_phone_id
			});
			
			selected_accounts.push(client_account);
		});
		
		//填充家长的相关信息
		$('#accept_list_div').data('selected_accounts', selected_accounts);
		var tabContext = $('#accept_list_tab');
		var trClone = $('.clone', tabContext);
		$('tr:gt(0)', tabContext).remove();
		for(var i in accept_name_list) {
			var data = accept_name_list[i] || {};
			var trObj = trClone.clone().removeClass('clone').appendTo(tabContext).show();
			if(data.father_phone_id) {
				$('td:eq(0)', trObj).css('color','#317400');
			}
			if(data.mother_phone_id) {
				$('td:eq(1)', trObj).css('color','#317400');
			}
			
			$('td:eq(0)', trObj).html(data.father_name);
			$('td:eq(1)', trObj).html(data.mother_name);
		}
	});
};

publish.prototype.attachEventForAcceptListDiv=function() {
	var context = $('#accept_list_div');
	$('#submit_btn', context).click(function() {
		var selected_accounts = $('#accept_list_div').data('selected_accounts') || [];
		var sms_content = $('#sms_content').val();
		var class_code = $("#class_code").val();
		if(!sms_content) {
			$.showError('短信内容不能空!');
			return false;
		}
		if($.isEmptyObject(selected_accounts)) {
			$.showError('请选择发送对象!');
			return false;
		}
		$.ajax({
			type:'post',
			url:'/Sns/ClassIndex/Mailbook/maillist_send/class_code/' + class_code,
			data:{
				'selected_accounts':selected_accounts,
				'sms_content': sms_content
			},
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
			}
		});
		
	});
};

function prompt_div() {
	this.attachEventUserDefine();
}

prompt_div.prototype.attachEventUserDefine=function() {
	$('#prompt_div').bind({
		openEvent:function(evt, options) {
			options = options || {};
			var divObj = $(this); 
			art.dialog({
				id:'prompt_div_dialog',
				title:'提示信息',
				content:divObj.get(0),
				init:function() {
					$('#prompt_content_p', divObj).html(options.msg);
				}
			}).lock().time(3);
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['prompt_div_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

$(document).ready(function() {
	new publish();
	new prompt_div();
});