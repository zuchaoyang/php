(function($) {

//打开评语的编辑对话框
$.fn.openPyEditDialog=function() {
	var me = this;
	//关闭已经打开的对话框
	$('#py_input_div').trigger('openEvent', [{
		'py_content' : me.val(),
		'follow' : me.get(0),
		'callback' : function(py_content) {
			me.val(py_content);
		}
	}]);
};

})(jQuery);

//输入框对应的事件处理
function py_input() {
	this.max_length = 60;
	this.limitInterval = null;
	this.loadTemplate();
	this.attachEvent();
	this.attachEventUserDefine();
}

py_input.prototype.loadTemplate=function() {
	$.ajax({
		type:'get',
		url:'/Sns/ClassExam/Pymanage/getPyInputTemplateAjax',
		dataType:'html',
		async:false,
		success:function(html) {
			$('<div id="py_input_div"></div>').appendTo($('body')).html(html).hide();
		}
	});
};

py_input.prototype.attachEventUserDefine=function() {
	//注册py_input_div的初始事件
	$('#py_input_div').bind('openEvent', function(evt, options) {
		options = options || {};
		//将数据绑定到div上
		$(this).data('options', options);
		art.dialog({
			id:'edit_art_dialog',
			title:'评语编辑',
			content:$('#py_input_div').get(0),
			follow:options.follow || {},
			init:function() {
				$('#aInput').val(options.py_content || '').focus();
	    	}
		});
	}).bind('closeEvent', function() {
		var dialogObj = art.dialog.list['edit_art_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
};

py_input.prototype.attachEvent=function() {
	var me = this;
	var context = $('#py_input_div');
	$('#aInput', context).focus(function() {
		me.limitInterval = setInterval(function() {
			var len = $.trim($('#aInput').val()).toString().length;
			if(len > me.max_length){
				$(".pcountTxt").html("超出<b><font size=3 color=red>" + (len - me.max_length) + "</font></b>字无法进行保存!");
				return false;
			}
			$(".pcountTxt").html("还能输入" + (me.max_length - len) + "字");
			return true;
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
	
    //关闭弹出层
	$('#isure_btn', context).click(function() {
		var ancestorObj = $('#py_input_div');
		var options = ancestorObj.data('options') || {};
		var content = $('#aInput').val();
		//处理回调函数
		if(typeof options.callback == 'function') {
			options.callback(content);
		}
		//关闭当前的编辑对话框 
		ancestorObj.trigger('closeEvent');
		$('#aInput').val('');
	});
	
	//取消按钮
	$('#cancel_btn', context).click(function() {
		$('#py_input_div').trigger('closeEvent');
	});
};

$(document).ready(function() {
	new py_input();
});