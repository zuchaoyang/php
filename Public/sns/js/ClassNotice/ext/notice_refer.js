function notice_refer() {
	this.accepters_cache = {};
	this.attachEventUserDefine();
}

notice_refer.prototype.attachEventUserDefine=function() {
	var me = this;
	$('#notice_refer_div').bind({
		//打开事件
		'openEvent':function(evt, options) {
			options = options || {};
			var divObj = $(this);
			divObj.data('options', options);
			var notice_datas = options.notice_datas || {};
			art.dialog({
				id:'notice_refer_div_dialog',
				title:'回执名单',
				follow:options.follow || {},
				content:divObj.get(0),
				init:function() {
					me.loadNoticeAccepters(notice_datas.notice_id || 0);
				}
			});
		},
		//关闭事件
		'closeEvent':function() {
			var dialogObj = art.dialog.list['notice_refer_div_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};
//加载班级的接受对象相关的数据
notice_refer.prototype.loadNoticeAccepters=function(notice_id) {
	var me = this;
	var cache_datas = me.accepters_cache[notice_id] || {};
	if($.isEmptyObject(cache_datas) && notice_id) {
		$.ajax({
			type:'post',
			url:'/Sns/ClassNotice/Published/getNoticeAcceptersAjax',
			data:{'notice_id':notice_id},
			dataType:'json',
			async:false,
			success:function(json) {
				cache_datas = me.accepters_cache[notice_id] = json.data || {};
			}
		});
	}
	me.fillDatas(cache_datas || {});
};
//填充相关的数据
notice_refer.prototype.fillDatas=function(datas) {
	var me = this;
	datas = datas || {};
	//未查看的相关信息
	var no_view_list = datas.no_view_list || {};
	var no_view_num = datas.no_view_num || 0;
	//已经查看的相关信息
	var viewed_list = datas.viewed_list || {};
	var viewed_num  = datas.viewed_num || 0;
	
	var contextDiv = $('#notice_refer_div');
	//填充已回执
	var viewed_html_str = me.createTrHtml(viewed_list);
	$('table:eq(0)', contextDiv).html(viewed_html_str);
	$("#viewed_num", contextDiv).html(viewed_num);
	//填充未回执
	var noview_html_str = this.createTrHtml(no_view_list);
	$('table:eq(1)', contextDiv).html(noview_html_str);
	$("#no_view_num", contextDiv).html(no_view_num);
};

//创建tr相关的html
notice_refer.prototype.createTrHtml=function(user_list) {
	user_list = user_list || {};
	
	var num_id = 1;
	var html_str = "<tr>";
	for(var i in user_list) {
		html_str += "<td>" + user_list[i].client_name + "</td>";
		if(num_id++ % 8 == 0) {
			html_str += "</tr><tr>";
		}
	}
	var append_nums = 8 - (num_id - 1) % 8;
	for(var i=1; i<=append_nums; i++) {
		html_str += "<td>&nbsp;</td>";
	}
	html_str += "</tr>";
	
	return html_str;
};

$(document).ready(function() {
	new notice_refer();
});