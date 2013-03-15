(function($) {
	$.showError=function(msg) {
		art.dialog({
			title:'错误提示',
			content:msg,
			opacity:0.5,
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			title:'成功提示',
			content:msg,
			opacity:0.5,
			icon:'succeed'
		}).lock().time(3);
	};
	
	$.dump=function(obj) {
		if(typeof obj == 'String') {
			$.showSuccess(obj);
		}
		for(var i in obj) {
			alert(i + "=>" + obj[i]);
		}
	};
	
})(jQuery);

function exam_score_edit() {
	this.attachEvent();
	this.attachEventUserDefine();
}

//绑定对外的交互事件
exam_score_edit.prototype.attachEventUserDefine=function() {
	var me = this;
	$('#exam_score_edit_div').bind({
		//父级元素和弹出层之间依赖自定义事件通讯
		'openEvent':function(evt, options) {
			options = options || {};
			//将选项数据绑定到弹层上
			$(this).data('options', options);
			art.dialog({
				id : 'exam_score_edit_dialog',
				content:$('#exam_score_edit_div').get(0),
				init:function() {
					me.fillDatas(options.score_datas || {});
				}
			}).lock();
		},
		//绑定弹出层的关闭事件
		'closeEvent':function() {
			var dialogObj = art.dialog.list['exam_score_edit_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};
//绑定评语相关的事件
exam_score_edit.prototype.attachEvent=function() {
	var me = this;
	var context = $('#exam_score_edit_div');
	//绑定系统评语
	$('#syspy_img', context).unbind('click').live('click', function() {
		$('#score_py', context).openSysPyDialog();
	});
	//绑定个人评语
	$('#mypy_img', context).unbind('click').live('click', function() {
		$('#score_py', context).openMyPyDialog();
	});
	//绑定提交按钮事件
	$('#pub_a', context).click(function() {
		me.ajaxSubmit();
		//阻止a元素的事件冒泡和默认行为
		return false;
	});
	////绑定提交+短信按钮事件
	$('#pub_with_sms_a').click(function() {
		me.ajaxSubmit({
			'is_sms':1
		});
		//阻止a元素的事件冒泡和默认行为
		return false;
	});
};

//异步提交编辑后的数据
exam_score_edit.prototype.ajaxSubmit=function(extend_datas) {
	var me = this;
	//数据处理
	var datas = me.extractDatas() || {};
	datas = $.extend(datas, extend_datas || {});
	
	//如果用户没有做任何的修改
	if(!me.isDifferent()) {
		$.showError('您没有做任何的修改!');
		return false;
	} else if(datas.exam_score > me.exam_well) {
		$.showError('分数不能超过满分:' + me.exam_well);
		return false;
	}
	$.ajax({
		type:'post',
		url:'/Sns/ClassExam/View/updateExamScoreAjax',
		data:datas,
		dataType:'json',
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
			//成功后的回调函数
			var options = $('#exam_score_edit_div').data('options') || {};
			if(typeof options.callback == 'function') {
				//提取页面编辑后的数据
				var datas = me.extractDatas() || {};
				//回调触发页面的回调函数
				options.callback(datas);
			}
			//关闭对话框
			$('#exam_score_edit_div').trigger('closeEvent');
		}
	});
};

//判断用户是否修改了相应的数据
exam_score_edit.prototype.isDifferent=function() {
	var check_fields =['exam_score', 'score_py'];
	//提取页面设置的相关数据
	var options = $('#exam_score_edit_div').data('options') || {};
	var score_datas = options.score_datas || {};
	//获取页面编辑后的数据
	var datas = this.extractDatas();
	for(var i in check_fields) {
		var field = check_fields[i];
		if(datas[field] != score_datas[field]) {
			return true;
		}
	}
	return false;
};

//填充考试相关的数据信息
exam_score_edit.prototype.fillDatas=function(score_datas) {
	score_datas = score_datas || {};
	
	var context = $('#exam_score_edit_div');
	//填充table相关的数据
	$('#client_name', context).html(score_datas.client_name);
	$('#exam_score', context).val(score_datas.exam_score);
	$('#score_py', context).val(score_datas.score_py);
	//填充隐藏域相关的数据
	$('#score_id', context).val(score_datas.score_id);
	$('#secret_key', context).val(score_datas.secret_key);
};

//数据提取函数
exam_score_edit.prototype.extractDatas=function() {
	var context = $('#exam_score_edit_div');
	
	var score_id = $('#score_id', context).val();
	var exam_score = $('#exam_score', context).val();
	var score_py = $('#score_py', context).val();
	var secret_key = $('#secret_key', context).val();
	
	return {
		'score_id':$.trim(score_id),
		'exam_score':$.trim(exam_score),
		'score_py':$.trim(score_py),
		'secret_key':$.trim(secret_key)
	};
};

$(document).ready(function() {
	new exam_score_edit();
});