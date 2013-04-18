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
	$.fn.openSysPyDialog=function() {
		var me = this;
		$('#sys_py_div').trigger('openSysPyDialogEvent', [{
			'callback':function(py_content) {
				me.val(py_content);
			}
		}]);
	};
})(jQuery);

function sys_py() {
	this.data_cache = {};
	this.loadTemplate();
	
	this.attachEventUserDefine();
	this.attachEventForNav();
	//加载数据
	this.loadDatas();
}

//加载对应的数据
sys_py.prototype.loadTemplate=function() {
	$.ajax({
		type:'get',
		url:'/Sns/ClassExam/Pymanage/getSysPyInfoTemplate',
		async:false,
		dataType:'html',
		success:function(html) {
			$('<div id="sys_py_div"></div>').appendTo($('body')).html(html).hide();
		}
	});
};

sys_py.prototype.attachEventUserDefine=function() {
	var me = this;
	//绑定数据获取事件
	$('#sys_py_div').bind({
		'openSysPyDialogEvent':function(evt, options) {
			//将回调函数绑定到对应的弹层上
			$(this).data('options', options || {});
			//打开对话框
			var dialogObj = art.dialog.list['sys_py_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
			art.dialog({
				id : 'sys_py_dialog',
				title : '系统评语库',
				content : $('#sys_py_div').get(0)
			});
		},
		'closeSysPyDialogEvent':function() {
			var dialogObj = art.dialog.list['sys_py_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

//绑定导航相关的事件
sys_py.prototype.attachEventForNav=function() {
	var me = this;
	var context = $('#sys_py_div');
	//绑定竖向属性按钮事件
	$('.py_type', context).click(function() {
		
		$('#sys_py_div').data({
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
		$('#sys_py_div').data('py_att', $(this).attr('id'));
		//加载系统数据
		me.loadDatas();
		$('.py_att', context).removeClass('a_link');
		$(this).addClass('a_link');
	});
};

//加载实际的数据
sys_py.prototype.loadDatas=function() {
	var me = this;
	
	var py_type = $('#sys_py_div').data('py_type') || 0;
	var py_att = $('#sys_py_div').data('py_att') || 0;
	
	var cache_key = py_type + ":" + py_att;
	var data_json = me.data_cache[cache_key];
	if($.isEmptyObject(data_json)) {
		$.ajax({
			type:'get',
			url:'/Sns/ClassExam/Pymanage/getSysPyinfoAjax',
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
sys_py.prototype.fillDatas=function(datas) {
	var me = this;
	var max_len = 38;
	//清楚已有的数据
	var parent = $('#sys_py_list_ul');
	$('li:gt(0)', parent).remove();
	
	var liClone = $('.clone', parent);
	for(var i in datas) {
		var py_content = datas[i].py_content;
		var liObj = liClone.clone().removeClass('clone').appendTo(parent).show();
		//将数据绑定到li元素上
		liObj.data('data', datas[i] || {});
		
		//显示的时候处理长度
		py_content = py_content.length > max_len ? py_content.substring(0, max_len) + "... " : py_content;
		$('.py_content', liObj).html(py_content);
	}
};

sys_py.prototype.attachEventForPy=function() {
	var context = $('#sys_py_list_ul');
	//绑定收藏按钮
	$('.collect_py', context).unbind('click').click(function() {
		var parent = $(this).parents('li:first');
		var data = parent.data('data') || {};
		var py_id = data.py_id;

		art.dialog({
			title: '收藏我的评语',
		    content: '您确认要收藏此评语吗？',
		    icon: 'succeed',
		    follow:parent.get(0),
		    ok:function() {
				$.ajax({
					type: "get",
					url: '/Sns/ClassExam/Pymanage/collectPyinfoAjax/py_id/' + py_id,
					dataType:'json',
					success:function(json){
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						$.showSuccess(json.info);
				    }
				});
			}
		});
	});
	//选择按钮
	$('.select_py', context).unbind('click').click(function() {
		//获取当前对应的评语内容
		var options = $('#sys_py_div').data('options') || {};
		if(typeof options.callback == 'function') {
			var data = $(this).closest('li').data('data');
			var py_content =  data.py_content;
			options.callback(py_content);
		}
		$('#sys_py_div').trigger('closeSysPyDialogEvent');
	});
};

$(document).ready(function() {
	new sys_py();
});