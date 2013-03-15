function exam_view() {
	this.attachEvent();
}

exam_view.prototype.attachEvent=function() {
	var me = this;
	var context = $('#student_list_tab');
	//绑定编辑的点击事件
	$('.edit_a_selector', context).unbind('click').click(function() {
		//利用祖先元素定位操作的域，避免通过id建立元素之间的映射关系
		var trObj = $(this).parents('tr:first');
		//数据收集
		var score_id = (trObj.attr('id').toString().match(/(\d+)/) || [])[1];
		var client_name = $('.client_name_selector', trObj).html();
		var exam_score = $('.exam_score_selector', trObj).html();
		var score_py = $('.score_py_selector', trObj).html();
		var secret_key = $('.secret_key_selector', trObj).val();
		var exam_well = parseInt($('#exam_well').val());
		if(isNaN(exam_well) || exam_well <= 0) {
			exam_well = 100;
		}
		//触发弹层的打开事件
		$('#exam_score_edit_div').trigger('openEvent', [{
			'score_datas' : {
				'score_id':$.trim(score_id),
				'client_name':$.trim(client_name),
				'exam_score':$.trim(exam_score),
				'score_py':$.trim(score_py),
				'secret_key':$.trim(secret_key)
			},
			'exam_well':exam_well,
			'callback':function(edited_datas) {
				edited_datas = edited_datas || {};
				$('.client_name_selector', trObj).html(edited_datas.client_name);
				$('.exam_score_selector', trObj).html(edited_datas.exam_score);
				$('.score_py_selector', trObj).html(edited_datas.score_py);
				//更新统计信息
				me.refreshStat();
			}
		}]);
	});
};

//刷新考试的统计信息
exam_view.prototype.refreshStat=function() {
	var exam_id = $('#exam_id').val();
	var stat_datas = {};
	$.ajax({
		type:'get',
		url:'/Sns/ClassExam/View/getExamStatAjax/exam_id/' + exam_id,
		dataType:'json',
		async:false,
		success:function(json) {
			stat_datas = json.data || {};
		}
	});
	var trObj = $('#show_stat_tr', $('#show_stat_tab'));
	$('td:eq(0)', trObj).html(stat_datas.join_nums);
	$('td:eq(1)', trObj).html(stat_datas.unjoin_nums);
	$('td:eq(2)', trObj).html(stat_datas.exam_well);
	$('td:eq(3)', trObj).html(stat_datas.avg_score);
	$('td:eq(4)', trObj).html(stat_datas.top_score);
	$('td:eq(5)', trObj).html(stat_datas.lower_score);
	$('td:eq(6)', trObj).html(stat_datas.excellent_percent);
	$('td:eq(7)', trObj).html(stat_datas.pass_percent);
};

$(document).ready(function() {
	new exam_view();
});