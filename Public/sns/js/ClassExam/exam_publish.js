(function($) {
	$.showError=function(msg){
		art.dialog({
			title:'错误提示',
			content:msg
			//icon:'error'
		}).time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			title:'成功提示',
			content:msg,
			icon:'succeed'
		}).time(3);
	};
	$.fn.sprintfHtml=function(str) {
		var html = this.html().toString().replace('%s', str);
		this.html(html);
	};
	
})(jQuery);

/**
 * 一、优先级的问题
 * 1. 成绩部分
 * 2. 预览发布部分
 * 3. 草稿部分
 * 4. 成绩导入部分
 */
function Pub() {
	this.attachEvent();
};
//事件绑定
Pub.prototype.attachEvent=function() {
	this.attachEventForBase();
	this.attachEventForExam();
	this.attachEventForExamScore();
	this.attachEventForUserDefine();
	this.attachEventForDraft();
	this.attachEventForImport();
};

//绑定用户自定义的事件信息
Pub.prototype.attachEventForUserDefine=function() {
	var self = this;
	$('form:first').bind('submitDraftEvent', function() {
		if($('#is_draft').length == 0) {
			$('<input type="hidden" name="is_draft" id="is_draft" value="1"/>').appendTo($(this));
		} else {
			$('#is_draft').val(1);
		}
		if(!self.validatorExamInfo()) {
			return false;
		}
		//修改表单提交的位置
		$(this).attr('action', '/Sns/ClassExam/Publish/publishDraft');
		self.packExamScoreDatas();
		$(this).submit();
	}).bind('submitEvent', function() {
		$('#is_draft').remove();
		//修改表单提交的位置
		$(this).attr('action', "/Sns/ClassExam/Publish/Publish");
		self.packExamScoreDatas();
		$(this).submit();
	});
};

//绑定系统基础事件
Pub.prototype.attachEventForBase=function() {
	var self = this;
	//绑定文件的选择事件
	$('#select_file_text, #select_file_btn').click(function() {
		$('#excel_template_file').trigger('click');
	});
	
	//绑定发布草稿按钮
	$('#pub_draft_btn').click(function() {
		$('form:first').trigger('submitDraftEvent');
	});
	//绑定预览发布按钮
	$('#pub_preview_btn').click(function() {
		if(!self.validator()) {
			return false;
		}
		var dialogObj = art.dialog.list['exam_preview_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		var target_elem = $('#exam_preview_div');
		art.dialog({
			id:'exam_preview_dialog',
			title:'发布预览',
			content:target_elem.get(0),
			init:function() {
				//触发form表单的数据提取事件
				var exam_info = self.extractExamInfo() || {};
				var exam_score_list= self.extractExamScore() || {};

				//触发预览层的初始化事件
				target_elem.trigger('initEvent', [exam_info, exam_score_list]);
			}
		}).lock();
	});
	
	//form表单的提交事件要去掉相应的file域
	$('form:first').submit(function() {
		$(':input[type="file"]', $(this)).remove();
		return true;
	});
};

//绑定考试相关事件
Pub.prototype.attachEventForExam=function() {
	//暂时没有
};

//绑定草稿部分相关事件
Pub.prototype.attachEventForDraft=function() {
	//点击提取草稿
	$('#extract_draft_a').click(function() {
		var dialogObj = art.dialog.list['exam_draft_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		art.dialog({
			id:'exam_draft_dialog',
			titile:'读取草稿',
			content:$('#exam_draft_div').get(0),
			init:function() {
				$('#exam_draft_div').trigger('loadEvent', [1]);
			}
		});
	});
};

//绑定导入部分相关事件
Pub.prototype.attachEventForImport=function() {
	var me = this;
	//点击导入成绩
	$('#import_btn').click(function() {
		$('#upload_excel_tpl_form').submit();
	});
	//提示层设置
	var dialog_settings = {
		openDialog:function() {
			art.dialog({
				id : 'importing_dialog',
				title:'提示',
				content:'正在导入，请稍后...'
			}).lock().time(30);
		},
		closeDialog:function() {
			var dialogObj = art.dialog.list['importing_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	};
	//表单提交的事件
	$('#upload_excel_tpl_form').submit(function() {
		$(this).ajaxSubmit({
			type:'post',
			url:'/Sns/ClassExam/Publish/uploadExcelTemplateAjax',
			dataType:'json',
			beforeSubmit:function() {
				//检测excel文件是否正确
				var file_name = ($('#excel_template_file').val().toString().split('/') || []).pop();
				var suffix = (file_name.toString().split('.') || []).pop();
				if(!file_name || $.inArray(suffix, ['xls', 'xlsx']) == -1) {
					$.showError('请选择要上传的Excel文件,只支持后缀名为:xls,xlsx的文件!');
					return false;
				}
				
				//弹出提示层
				dialog_settings.openDialog();
				return true;
			},
			success:function(json) {
				//关闭提示层
				dialog_settings.closeDialog();
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				} 
				$.showSuccess(json.info);
				//数据填充
				me.fillImportDatas(json.data || {});
			}
		});
		//防止页面跳转
		return false;
	});
};

Pub.prototype.fillImportDatas=function(score_datas) {
	var parentObj = $('#student_list_tab');
	//清空已有的数据
	$('tr:gt(1)', parentObj).remove();
	var cloneObj = $('.clone', parentObj);

	for(var uid in score_datas) {
		var data = score_datas[uid];
		//添加数据信息
		var trObj = cloneObj.clone().removeClass('clone').appendTo(parentObj).show();
		var child_set = trObj.children('td');
		
		child_set.eq(0).html(data.num_id);
		child_set.eq(1).html(data.client_name);
		child_set.eq(2).children(':input:first').val(data.exam_score || '');
		
		$(':input:first', child_set.eq(3)).attr('id', 'py_id_' + data.client_account).val(data.score_py || "");
		$('a:eq(0)', child_set.eq(3)).attr('id', 'syspy_img_' + data.client_account);
		$('a:eq(1)', child_set.eq(3)).attr('id', 'mypy_img_' + data.client_account);
		trObj.attr('id', 'tr_' + data.client_account);
	}
};

//绑定考试成绩相关事件
Pub.prototype.attachEventForExamScore=function() {
	//绑定输入框的点击事件
	$(':input[id^="py_id_"]').unbind('click').live('click', function(evt) {
		$(this).openPyEditDialog();
	});
	//我的评语
	$('*[id^="mypy_img_"]').unbind('click').live('click', function() {
		var py_id = ($(this).attr('id').toString().match(/(\d+)/) || [])[1];
		$('#py_id_' + py_id).openMyPyDialog();
	});
	//系统评语
	$('*[id^="syspy_img_"]').unbind('click').live('click', function() {
		var py_id = ($(this).attr('id').toString().match(/(\d+)/) || [])[1];
		$('#py_id_' + py_id).openSysPyDialog();
	});
};

//成绩相关的数据收集函数,将表单的数据填充到第一个表单中去
Pub.prototype.packExamScoreDatas=function() {
	var parentObj = $('form:first');
	//清理已经存在的数据
	$('.score_set', parentObj).remove();
	
	var exam_score_list = this.extractExamScore();
	//当前最新数据的获取
	for(var uid in exam_score_list) {
		var data = exam_score_list[uid];
		$('<input class="score_set" type="hidden" name="exam_score_list[' + uid + '][client_account]" value="' + data.client_account + '"/>').appendTo(parentObj);
		$('<input class="score_set" type="hidden" name="exam_score_list[' + uid + '][exam_score]" value="' + data.exam_score + '"/>').appendTo(parentObj);
		$('<input class="score_set" type="hidden" name="exam_score_list[' + uid + '][score_py]" value="' + data.py_content + '"/>').appendTo(parentObj);
	};
};

//提取考试成绩列表信息
Pub.prototype.extractExamScore=function() {
	var context = $('#student_list_tab');
	//当前最新数据的获取
	var exam_score_list = {};
	$('tr:gt(1)', context).each(function() {
		var trObj = $(this);
		var client_account = $(this).attr('id').toString().match(/(\d+)/)[1];
		var num_id      = $('td:eq(0)', trObj).html();
		var client_name = $('td:eq(1)', trObj).html();
		var exam_score  = $(':input:first', $('td:eq(2)', trObj)).val();
		var py_content  = $(':input:first', $('td:eq(3)', trObj)).val();
		
		exam_score_list[client_account] = {
			'num_id'         : num_id,
			'client_account' : client_account,
			'client_name'    : client_name,
			'exam_score'     : exam_score,
			'py_content'     : py_content
		};
	});
	
	return exam_score_list;
};

//提取考试的基本信息
Pub.prototype.extractExamInfo=function() {
	var context = $('#show_exam_div');
	
	var class_name = $('#class_name', context).html();
	var subject_name = $('#subject_id option:selected', context).html();
	var exam_name = $('#exam_name', context).val();
	var exam_time = $('#exam_time', context).val();
	var exam_well = $('#exam_well', context).val();
	var exam_good = $('#exam_good', context).val();
	var exam_bad  = $('#exam_bad', context).val();

	return {
		'class_name' : class_name,
		'subject_name' : subject_name,
		'exam_name' : exam_name,
		'exam_time' : exam_time,
		'exam_well' : exam_well,
		'exam_good' : exam_good,
		'exam_bad' : exam_bad
	};
};

Pub.prototype.validator=function() {
	var self = this;
	if(!self.validatorExamInfo()) {
		return false;
	}
	if(!self.validatorExamScore()) {
		return false;
	}
	return true;
};

Pub.prototype.validatorExamInfo=function() {
	var self = this;
	if(!$('#subject_id').val()) {
		$.showError('请选择科目信息!');
		return false;
	}
	if(!$.trim($('#exam_name').val())) {
		$.showError('请填写考试名称!');
		return false;
	}
	if(!$.trim($('#exam_time').val())) {
		$.showError('请填写考试时间!');
		return false;
	}
	var exam_well = $.trim($('#exam_well').val());
	var exam_good = $.trim($('#exam_good').val());
	var exam_bad = $.trim($('#exam_bad').val());
	if(!exam_well) {
		$.showError('请填写满分分数!');
		return false;
	}
	if(!exam_good) {
		$.showError('请填写优秀分数!');
		return false;
	}
	if(!exam_bad) {
		$.showError('请填写及格分数!');
		return false;
	}
	var pattern = /^\d{1,4}(\.\d{1})?$/;
	if(!exam_well.match(pattern)) {
		$.showError('满分必须是有效数字');
		return false;
	}
	if(!exam_good.match(pattern)) {
		$.showError('优秀必须是有效数字');
		return false;
	}
	if(!exam_bad.match(pattern)) {
		$.showError('及格必须是有效数字');
		return false;
	}
	
	return true;
};

//检测分数信息
Pub.prototype.validatorExamScore=function() {
	var exam_score_list = this.extractExamScore();
	
	var exam_well = $.trim($('#exam_well').val());
	
	//检测分数是否是正确的，不正确将背景颜色设为红色
	var context = $('#student_list_tab');
	var is_passed = true;
	for(var uid in exam_score_list) {
		var exam_score = exam_score_list[uid].exam_score.toString();
		var inpObj = $('#tr_' + uid, context).children('td:eq(2)').children(':input:first');
		if(exam_score) {
			if (!exam_score.match(/^\d{1,4}(\.\d{1})?$/) || parseFloat(exam_score) > parseFloat(exam_well)) {
				inpObj.css('border', '1px solid red');
				is_passed = false;
			}
		} else {
			inpObj.css('border', '1px solid #DCDCDC');
		}
	}
	if(!is_passed) {
		$.showError('成绩列表数据有错误!');
		return false;
	}
	
	return true;
};

//加载考试草稿信息
Pub.prototype.loadExamDraft=function() {
	
};

$(document).ready(function(){
	new Pub();
});