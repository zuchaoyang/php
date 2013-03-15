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

//去掉页面标签
function tripTag(str) {
	str = $.trim(str.toString() || '');
	return str.replace(/<(.+?)>/gm, '');
};

function Publish() {
	this.editor = {};
	
	this.init();
	this.attachEvent();
	this.attachEventForPreviewDiv();
	this.attachEventUserDefine();
};

Publish.prototype.getEditor=function() {
	return this.editor;
};

Publish.prototype.attachEventUserDefine=function() {
	var me = this;
	//将弹出层和基本页面在js实现上分离
	$('body').bind('openPreviewDivEvent', function(evt, datas) {
		datas = datas || {};
		var divContext = $('#preview_div');
		//打开弹出
		art.dialog({
			id:'notice_preview_dialog',
			title:'班级公告  > 预览公告',
			content:divContext.get(0),
			init:function() {
				$('#notice_title', divContext).html(datas.notice_title);
				$('#content', divContext).html(datas.content);
			}
		}).lock();
	});
};

Publish.prototype.init=function() {
	var formContext = $('form:first');
	//加载编辑框
	var bg = $('#ContentBg').val();
	if(bg) $('#context', formContext).css('url', bg);
	this.editor = $('#content', formContext).xheditor({
		skin:'vista',
		tools:'Separator,BtnBr,Blocktag,Fontface,FontSize,Bold,Italic,Underline,FontColor,BackColor,SelectAll',
		upImgUrl:'/Sns/ClassNotice/Publish/uploadPath'
	});
	//初始化字符计数器的值
	var content = tripTag(this.editor.getSource());
	$('#content_counter').html(180 - content.length);
};

Publish.prototype.attachEvent=function() {
	var me = this;
	//预览发布按钮
	$('#preview_btn').click(function() {
		//没有通过验证正不显示预览发布页面
		if(!me.validator()) {
			return false;
		}
		//初始化弹出层的相关数据
		var formContext = $('form:first');
		var notice_title = $('#notice_title', formContext).val();
		var content = me.editor.getSource();
		$('body').trigger('openPreviewDivEvent', [{
			'notice_title':notice_title,
			'content':content
		}]);
	});
	//表单提交事件
	$('form:first').submit(function() {
		return me.validator();
	});
};

Publish.prototype.attachEventForPreviewDiv=function() {
	var divContext = $('#preview_div');
	//发布公告按钮
	$('#pub_a', divContext).click(function() {
		var formObj = $('form:first');
		$('#is_sms', formObj).remove();
		formObj.submit();
		return false;
	});
	//发布公告+短信按钮
	$('#pub_with_msg_a', divContext).click(function() {
		var formObj = $('form:first');
		$('<input type="hidden" id="is_sms" name="is_sms" value="1"/>').appendTo(formObj);
		formObj.submit();
		return false;
	});
};

Publish.prototype.validator=function() {
	var formContext = $('form:first');
	var notice_title = $('#notice_title', formContext).val();
	if(!$.trim(notice_title)) {
		$.showError('请您输入公告标题!');
		return false;
	}
	var content = tripTag(this.editor.getSource());
	if(!$.trim(content)) {
		$.showError('请您输入公告内容!');
		return false;
	} else if(content.length > 180) {
		$.showError('公告内容不能超过180字!');
		return false;
	}
	
	return true;
};

$(document).ready(function() {
	var pub = new Publish();
	var editor = pub.getEditor();
	
	var ifr = $('#xhe0_iframe')[0];
	$(ifr.contentWindow.document).ready(function() {
		//键盘的press事件负责内容超过时的提示
		$(ifr.contentWindow.document).keypress(function(evt) {
			var content = tripTag(editor.getSource());
			if(content.length >= 180) {
				var keyCode = evt.keyCode || evt.which;
				//字符超过限制后只有Backspace键能够按
				if(keyCode != 8) {
					$.showError('公告内容不能超过180字!');
					return false;
				}
			} else {
				var content = tripTag(editor.getSource());
				$('#content_counter').html(180 - content.length);
				return true;
			}
		});
		
		//实时更新字符数统计
		setInterval(function() {
			var content = tripTag(editor.getSource());
			$('#content_counter').html(180 - content.length);
		}, 200);
	});
});