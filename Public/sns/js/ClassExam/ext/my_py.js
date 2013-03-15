(function($){
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
	//打开系统的评语库
	$.fn.openMyPyDialog=function() {
		var me = this;
		$('#my_py_div').trigger('openMyPyDialogEvent', [{
			'callback':function(py_content) {
				me.val(py_content);
			}
		}]);
	};
})(jQuery);

function my_py() {
	this.data_cache = {};
	this.loadTemplate();
	
	this.attachEventUserDefine();
	this.attachEventForNav();
	//加载数据
	this.loadDatas();
}

//加载对应的数据
my_py.prototype.loadTemplate=function() {
	$.ajax({
		type:'get',
		url:'/Sns/ClassExam/Pymanage/getMyPyinfoTemplateAjax',
		async:false,
		dataType:'html',
		success:function(html) {
			$('<div id="my_py_div"></div>').appendTo($('body')).html(html).hide();
		}
	});
};

my_py.prototype.attachEventUserDefine=function() {
	var me = this;
	//绑定数据获取事件
	$('#my_py_div').bind({
		'openMyPyDialogEvent':function(evt, options) {
			//将回调函数绑定到对应的弹层上
			$(this).data('options', options || {});
			//打开对话框
			art.dialog({
				id : 'my_py_dialog',
				title : '我的评语库',
				content : $('#my_py_div').get(0)
			});
		},
		'closeMyPyDialogEvent':function() {
			var dialogObj = art.dialog.list['my_py_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

//绑定导航相关的事件
my_py.prototype.attachEventForNav=function() {
	var me = this;
	var context = $('#my_py_div');
	//绑定竖向属性按钮事件
	$('.py_type', context).click(function() {
		$('#my_py_div').data({
			'py_type':$(this).attr('id'),
			'py_att':0
		});
		//加载系统数据
		me.loadDatas();
		$('.py_type', context).removeClass('library_a');
		$('.py_att', context).removeClass('a_link');
		$(this).addClass('library_a');
	});
	//绑定横行属性按钮事件
	$('.py_att', context).click(function() {
		$('#my_py_div').data('py_att', $(this).attr('id'));
		//加载系统数据
		me.loadDatas();
		$('.py_att', context).removeClass('a_link');
		$(this).addClass('a_link');
	});
};

//加载实际的数据
my_py.prototype.loadDatas=function() {
	var me = this;
	var py_type = $('#my_py_div').data('py_type') || 0;
	var py_att = $('#my_py_div').data('py_att') || 0;
	var cache_key = py_type + ":" + py_att;
	var data_json = me.data_cache[cache_key];
	if($.isEmptyObject(data_json)) {
		$.ajax({
			type:'get',
			url:'/Sns/ClassExam/Pymanage/getMyPyinfoAjax',
			async:false,
			data:{
				'py_type' : py_type,
				'py_att' : py_att
			},
			dataType:'json',
			success:function(json) {
				data_json = me.data_cache[cache_key] = json.data || {};
			}
		});
	}
	//数据填充
	me.fillDatas(data_json);
	//事件绑定
	me.attachEventForPy();
};

//数据填充
my_py.prototype.fillDatas=function(datas) {
	var me = this;
	var context = $('#my_py_div');
	//清楚已有的数据
	var parent = $('#my_py_list_ul', context);
	$('li:gt(0)', parent).remove();
	var liClone = $('.clone', parent);
	for(var i in datas) {
		var py_content = datas[i].py_content;
		var liObj = liClone.clone().removeClass('clone').appendTo(parent).show();
		//将数据绑定到li元素上
		liObj.data('data', datas[i] || {});
		$('.py_content', liObj).html(py_content);
	}
};

my_py.prototype.attachEventForPy=function() {
	var context = $('#my_py_list_ul');
	//选择按钮
	$('.select_py', context).unbind('click').click(function() {
		//获取当前对应的评语内容
		var options = $('#my_py_div').data('options') || {};
		if(typeof options.callback == 'function') {
			var py_content = $('.py_content', $(this).parents('li:first')).html();
			options.callback(py_content);
		}
		$('#my_py_div').trigger('closeMyPyDialogEvent');
	});
	
	//删除按钮
	$('.delete_py', context).unbind('click').click(function() {
		var parent = $(this).parents('li:first');
		var data = parent.data('data') || {};
		var collect_id = data.collect_id;
		art.dialog({
			title: '删除我收藏的评语',
		    content: '您确认要删除收藏的评语吗？',
		    icon: 'succeed',
		    follow:parent.get(0),
		    ok:function() {
				$.ajax({
					type: "get",
					url: '/Sns/ClassExam/Pymanage/delMyPyAjax/collect_id/' + collect_id,
					dataType:'json',
					success:function(json){
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						$.showSuccess(json.info);
						//删除评语（页面）
						parent.remove();
				    }
				});
			}
		});
	});
};

$(document).ready(function() {
	new my_py();
});