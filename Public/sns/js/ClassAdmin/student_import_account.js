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

function student_import_account() {
	this.attachEvent();
}

student_import_account.prototype.attachEvent=function() {
	var me = this;
	var context = $('#main_div');
	$('#sure_btn_a', context).click(function() {
		if(!me.checkUserAccount()) {
			return false;
		}
		me.slideRemove(function() {
			//将数据填充到表格中去
			var client_account_list = me.parseUserAccountToArray();
			var tabContext = $('#student_list_tab');
			$('tr:gt(1)', tabContext).remove();
			var trClone = $('.clone', tabContext);
			var num_id = 1;
			for(var i in client_account_list) {
				var account = client_account_list[i];
				var trObj = trClone.clone().removeClass('clone').appendTo(tabContext).show();
				$('td:eq(0)', trObj).html(num_id++);
				$('td:eq(1)', trObj).html(account);
			}
		});
		return false;
	});
	
	//确认生成账号按钮的点击
	$('#submit_btn_a', context).click(function() {
		//收集用户名信息
		var client_account_list = [];
		$('tr:gt(1)', $('#student_list_tab')).each(function() {
			var account = $('td:eq(1)', $(this)).html();
			client_account_list.push(account);
		});
		if($.isEmptyObject(client_account_list)) {
			$.showError('请先录入学生账号信息!');
			return false;
		}
		//弹出层的打开事件
		$('#waiting_div').trigger('openEvent');
		var class_code = $('#class_code').val();
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/StudentImport/execStudentImportAccountAjax/class_code/' + class_code,
			data:{'client_account_list' : client_account_list},
			dataType:'json',
			success:function(json) {
				$('#waiting_div').trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				window.location.href="/Sns/ClassAdmin/StudentList/index/class_code/" + class_code;
			}
		});
		return false;
	});
};

student_import_account.prototype.checkUserAccount=function() {
	var me = this;
	
	//获取第一个有错误的字符串
	function getFirstErrorAccount(arr) {
		arr = arr || [];
		for(var i in arr) {
			var str = arr[i].toString();
			if(!str.match(/^(\d)+$/)) {
				return str;
			}
		}
		return "";
	};
    //选中有错误的字符串信息
	function selectText(str, elem) {
		str = str.toString();
		//查找字符串并标示其选中的起始位置
		var text = $(elem).val().toString();
		index = text.indexOf(str);
		var startPos = index;
		var endPos = index + str.length;
		if (elem.createTextRange) {
		    var range = elem.createTextRange();
		    //range.moveEnd("character", -1 * value.length);
		    range.moveEnd("character", endPos);
		    range.moveStart("character", startPos);
		    range.select();    
		} else {
			elem.setSelectionRange(startPos, endPos);
			elem.focus();
		}
		return true;
	};
	//数据校验，将第一个错误的字符串返回
	var client_account_list = me.parseUserAccountToArray();
	if($.isEmptyObject(client_account_list)) {
		$.showError('请选填写学生账号信息!');
		return false;
	}
	
	var error_name = getFirstErrorAccount(client_account_list);
	if(error_name) {
		$('#prompt_div').trigger('openEvent', [{
			afterClose:function() {
				selectText(error_name, $('#student_accounts')[0]);
			}
		}]);
		return false;
	}
	
	return true;
};

//提取名字信息
student_import_account.prototype.parseUserAccountToArray=function() {
	//解析字符串，按照换行分离
	var account_list = $('#student_accounts').val().toString().split(/(\r\n)|(\n)/);
	//过滤掉字符串为空的情况
	var new_account_list = [];
	for(var i in account_list) {
		var account = $.trim(account_list[i]);
		if(!account) {
			continue;
		}
		new_account_list.push(account);
	}
	return new_account_list;
};

student_import_account.prototype.slideRemove=function(callback) {
	callback = (typeof callback == 'function') ? callback : $.noop;
	var arr = [];
	//收集要删除的元素
	$('#student_list_tab tr:gt(1)').each(function() {
		arr.push($(this));
	});
	//如果没有要变化的效果信息
	if($.isEmptyObject(arr)) {
		callback();
		return true;
	}
	var timeout = Math.ceil(2000 / arr.length);
	timeout = (timeout > 0 && timeout <= 200) ? timeout : 200;
	//渐出效果
	function slide() {
		setTimeout(function() {
			if($.isEmptyObject(arr)) {
				callback();
				return false;
			}
			var obj = arr.pop();
			if(typeof obj.slideUp == 'function') {
				obj.slideUp('slow').remove();
			}
			slide();
		}, timeout);
	};
	slide();
};

function prompt_div() {
	this.attachEventUserDefine();
};

//绑定用户的自定义事件用于不同层之间的通讯
prompt_div.prototype.attachEventUserDefine=function() {
	$('#prompt_div').bind({
		openEvent:function(evt, options) {
			options = options || {};
			var divObj = $(this);
			art.dialog({
				id:'prompt_dialog',
				title:'提示',
				content:divObj.get(0),
				close:function() {
					if(typeof options.afterClose == 'function') {
						options.afterClose();
					}
				}
			});
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['prompt_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

function waiting_div(){
	this.attachEventUserDefine();
}

waiting_div.prototype.attachEventUserDefine=function() {
	$('#waiting_div').bind({
		openEvent:function() {
			var divObj = $(this);
			art.dialog({
				id:'waiting_dialog',
				title:'正在导入账号',
				content:divObj.get(0)
			}).lock().time(30);
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['waiting_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

$(document).ready(function() {
	new student_import_account();
	new prompt_div();
	new waiting_div();
});