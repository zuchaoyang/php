(function($) {
	$.fn.sprintfHtml=function(str) {
		if(!str) return false;
		var html = this.html().toString().replace('%s', str);
		this.html(html);
	};
	
//	$.dump=function(arr) {
//		if(typeof arr == 'string') {
//			document.write(arr + "<br />");
//		}
//		for(var i in arr) {
//			if(typeof arr[i] == 'object') {
//				document.write(i + "=>");
//				document.write("<pre>");
//				$.dump(arr[i]);
//				document.write("</pre>");
//			}
//			document.write(i + "=>" +  arr[i] + "<br />");
//		}
//	};
	
})(jQuery);

function studentExamCls() {
	this.attachEvent();	
}

studentExamCls.prototype.attachEvent=function(){
	var me = this;
    //下一页的点击事件
	$('#next_page').click(function() {
		var page = $(this).data('page') || 1;
		$(this).data('page', page + 1);

		//获取下一页数据 并对数据进行处理
		me.loadDatas(page + 1);
	    
		//阻止a事件冒泡
		return false;
	});
	
	$('#search_btn').click(function() {
		$('#search_form').submit();
		
		//阻止a事件冒泡
		return false;
	});
};

//ajax加载远程数据信息
studentExamCls.prototype.loadDatas=function(page) {
	var me = this;
	page = page || 1;

	$.ajax({
		type:'post',
		url:'/Sns/ClassExam/Exam/moreStudentExamAjax',
		data:{
		    page:page,
			class_code:$('#class_code').val(),
			subject_id:$('#subject_id').val(),
			exam_name :$('#exam_name').val(),
			start_time:$('#start_time').val(),
			end_time  :$('#end_time').val()
		},
		dataType:'json',
		success:function(json) {
			if(json.status < 0) {
				return false;
			}
			me.fillDatas(json.data || {});
		}
	});
};

studentExamCls.prototype.fillDatas=function(json_datas) {
	json_datas = json_datas || {};
	var is_nextpage = json_datas.is_nextpage || 0;
	var exam_list = json_datas.exam_list || {};
	
	//将是否有下一页的数据进行绑定
	if(!is_nextpage) {
		parentObj = $('#next_page').parent();
		$('#next_page').remove();
		parentObj.append('<a href="javascript:;" style="cursor:default;">没有更多了</a>').show();
	}
	
	var parentObj = $('#exam_list_div');
	var divClone  = $('.clone', parentObj);
	
	for(var i in exam_list) {
		var exam = exam_list[i] || {};
		var stat = exam['stat'] || {};
		var score = exam['score'] || {};

		exam_score = score.exam_score ? (score.exam_score + '分') : '未参加';
		score_py = (score.score_py ? score.score_py : '暂无') ;
		
		//添加数据信息
		var divObj = divClone.clone().removeClass('clone').appendTo(parentObj).show();
		var trObj = $('tr:eq(1)', divObj);
		var exam_span = $('p span', divObj);
		var pyObj = $('.line_top span', divObj);

		exam_span.eq(0).sprintfHtml(exam.exam_name);
		exam_span.eq(1).sprintfHtml(exam.subject_name);
		exam_span.eq(2).sprintfHtml(exam.exam_time);
		exam_span.eq(3).sprintfHtml(exam_score);

		$('td:eq(0)', trObj).html(stat.join_nums);
		$('td:eq(1)', trObj).html(stat.unjoin_nums);
		
		$('td:eq(2)', trObj).html(exam.exam_well);

		$('td:eq(3)', trObj).html(stat.avg_score);
		$('td:eq(4)', trObj).html(stat.top_score);
		$('td:eq(5)', trObj).html(stat.lower_score);
		$('td:eq(6)', trObj).html(stat.excellent_percent);
		$('td:eq(7)', trObj).html(stat.pass_percent);
		
		pyObj.html(score_py);
	}
	
};

$(document).ready(function(){
	
	var obj = new studentExamCls();
});
