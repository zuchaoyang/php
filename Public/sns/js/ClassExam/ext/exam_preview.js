function exam_preview() {
	this.attachEvent();
	this.attachUserDefineEvent();
}
//绑定自定义事件
exam_preview.prototype.attachUserDefineEvent=function() {
	var self = this;
	$('#exam_preview_div').bind('initEvent', function(evt, exam_info, exam_score_list) {
		self.fillExamInfo(exam_info);
		self.fillExamScoreInfo(exam_score_list);
	});
};
//预览发布相关的事件
exam_preview.prototype.attachEvent=function() {
	var context = $('#exam_preview_div');
	
	$('#pub_exam', context).click(function() {
		$('#is_sms').remove();
		$('form:first').trigger('submitEvent');
	});
	
	$('#pub_exam_with_msg', context).click(function() {
		if($('#is_sms').length == 0) {
			$('<input name="is_sms" id="is_sms" type="hidden" value="1" />').appendTo($('form:first'));
		} else {
			$('#is_sms').val(1);
		}
		$('form:first').trigger('submitEvent');
	});
};
//填充考试的基本信息
exam_preview.prototype.fillExamInfo=function(exam_info) {
	exam_info = exam_info || {};
	var context = $('#exam_preview_tab');

	$('#class_name',   context).html(exam_info.class_name);
	$('#subject_name', context).html(exam_info.subject_name);
	$('#exam_name', context).html(exam_info.exam_name);
	$('#exam_time', context).html(exam_info.exam_time);
	$('#exam_well', context).html(exam_info.exam_well);
	$('#exam_good', context).html(exam_info.exam_good);
	$('#exam_bad',  context).html(exam_info.exam_bad);
};
//填充考试成绩的基本信息
exam_preview.prototype.fillExamScoreInfo=function(exam_score_list) {
	exam_score_list = exam_score_list || {};
	$('#score_preview_tab tr:gt(1)').remove();
	var parentTab = $('#score_preview_tab');
	var cloneObj = $('.clone', parentTab);
	for(var uid in exam_score_list) {
		var data = exam_score_list[uid];
		//填充数据到弹出层中去
		var trObj = cloneObj.clone().appendTo(parentTab).show();
		var tr_child = trObj.children('td');
		tr_child.eq(0).html(data.num_id);
		tr_child.eq(1).html(data.client_name);
		tr_child.eq(2).html(data.exam_score ? data.exam_score : '未参加');
		tr_child.eq(3).html(data.py_content);
	};
};
$(document).ready(function() {
	new exam_preview();
});