(function($){
	//提示框
	$.showError=function(msg){
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg || '操作失败!',
			icon:'warning',
			cancel:false,
			fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
		}).time(2);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_succeed_dialog',
			title:'成功提示',
			content:msg || '操作失败!',
			icon:'succeed',
			cancel:false,
			fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
		}).time(2);
	};
	
	//简单的数据渲染,支持反复渲染
	$.fn.renderHtml=function(datas) {
		datas = datas || {};
		//判断页面是否渲染过
		if(!this.data('is_rendered')) {
			this.data('tpl_html', this.html().toString());
			this.data('is_rendered', true);
		}
		var tpl_html = this.data('tpl_html');
		var html = tpl_html.toString().replace(/\{([^\}]+?)\}/ig, function(a, b) {
			return datas[b] || "";
		});
		this.html(html);
		return this;
	};

})(jQuery);

function Publish() {
	this.editor = {};
	this.default_title = "请输入日志标题";
	
	this.init();
	this.attachEventUserDefine(); //绑定自定义事件
	this.attachEvent();           //绑定系统事件
};

Publish.prototype.init=function() {
	var context = $('.edit_device');
	this.editor = $('#content', context).xheditor({
		width:'100%',
		height:460,
		skin:'default',
		loadcss:'  border: 1px solid #CCCCCC;',
		upImgUrl:'/Sns/Blog/Publish/uploadPath'
	});
};

//绑定用户自定义的事件信息
Publish.prototype.attachEventUserDefine=function() {
	var self = this;
	$('form:first').bind('editEvent', function() { //修改日志
		if(!self.validatorBlogInfo()) { 
			return false;
		}
		
		$(this).attr('action', "/Sns/Blog/Publish/editBlog");
		$(this).submit();
	});
};

//绑定系统基础事件
Publish.prototype.attachEvent=function() {
	var self = this;
	var context = $('#blog_info_div');
	//日志标题文本框绑定获取、失去光标事件
	$('#title', context).focus(function() {  //获取光标
		if($(this).val().toString() == self.default_title) {
			$(this).val("");
		}
	}).blur(function() {  //失去光标
		if($(this).val().toString() == "") {
			$(this).val(self.default_title);
		}
	});
	
	//绑定日志发布按钮
	$('#publish_btn', context).click(function() {
		if(!self.validatorBlogInfo()) { 
			return false;
		}
		
		var data = self.extractData() || {};
		var blog_id = $('#blog_id').val();
		var class_code = $('#class_code').val();
		//判断是修改还是 添加
		if (! $.trim(blog_id)) {
			data.is_published = 1;
			url = '/Sns/Blog/Publish/publishBlogAjax';
		} else {
			data.blog_id = blog_id;
			url = '/Sns/Blog/Publish/editBlogAjax';
		}
		
		// ajax 提交表单
		$.ajax({
			type:'post',
			url:url,
			data:data,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				
				//成功跳转到日志详情页
				window.location.href = "/Sns/Blog/Content/index/blog_id/" + json.data + "/class_code/" + class_code;
			}
		});
	});
	
	//保存草稿按钮
	$('#draft_btn', context).click(function() {
		if(!self.validatorBlogInfo()) { 
			return false;
		}
		
		var data = self.extractData() || {};
		var draft_id = $('#draft_id').val();
		
		//判断是修改还是 添加
		if (! $.trim(draft_id)) {
			data.is_published = 0;
			url = '/Sns/Blog/Publish/publishBlogAjax';
		} else {
			data.blog_id = draft_id;
			url = '/Sns/Blog/Publish/editBlogAjax';
		}
		
		// ajax 提交表单
		$.ajax({
			type:'post',
			url:url,
			data:data,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				
				$.showSuccess(json.info);

				//成功跳转到日志详情页 todo草稿保存成功后添加草稿id 保证只保存一次草稿
				$('#draft_id').val(json.data);
			}
		});
	});
	
	//绑定预览发布按钮
	$('#preview_btn', context).click(function() {
		if(!self.validatorBlogInfo()) {
			return false;
		}
		
		var blog_data = self.extractData();
		blog_data.type_name = $('option:selected', $('#type_id')).text();
		blog_data.grant_name = $('option:selected', $('#grant')).text();
		$('#preview_div').trigger('openEvent', [{
			datas : blog_data || {},
			callback:function() {
				
				$('#publish_btn', context).trigger('click');
			}
		}]);
	});
	
	//绑定 添加日志分类按钮
	$('#add_type_a', context).click(function() {
		var blog_data = self.extractData();
		var datas = {
				'class_code' : blog_data.class_code
		};

		$('#add_type_div').trigger('openEvent', [{
			datas : datas || {},
			callback:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				
				$('#add_type_div').trigger('closeEvent');  //关闭弹层
				//日志分类添加成功后处理
				var data = json.data || {};
				$("<option value='" + data.type_id + "'>" + data.name + "</option>").attr('selected', true).appendTo($('#type_id'));
				var rm_obj = $("#type_id option[value=" + -1 + "]");
				if(!$.isEmptyObject(rm_obj)) {
					$("#type_id option[value=" + -1 + "]").remove();
				}
				
				$.showSuccess('添加成功');
				return false;
			}
		}]);
	});
	
	//点击提取草稿
	$('#draft_a').click(function() {
		$('#draft_div').trigger('openEvent');
	});
	
};

//提取数据
Publish.prototype.extractData=function() {
	var parent = $('#blog_info_div');
	//当前最新数据的获取
	var blog_arr = {};
	var title   = $.trim($('#title', parent).val());
	var content = $.trim($('#content', parent).val());
	var grant   = $.trim($('#grant', parent).val());
	var type_id = $.trim($('#type_id', parent).val());
	var class_code   = $.trim($('#class_code', parent).val());
	var contentbg    = $.trim($('#contentbg', parent).val());

	blog_arr = {
		'title'         : title,
		'content'       : content,
		'grant'         : grant,
		'type_id'       : type_id,
		'class_code'    : class_code,
		'contentbg'     : contentbg
	};
	
	return blog_arr;
};

//验证表单数据
Publish.prototype.validatorBlogInfo=function() {
	var self = this;

	if($.trim($('#title').val()) == self.default_title) {
		$.showError('请填写日志标题!');
		return false;
	}
	if(!$.trim($('#content').val())) {
		$.showError('请填写日志内容!');
		return false;
	}

	if(parseInt($('#type_id').val()) < 0) {
		$.showError('请选择日志分类!');
		return false;
	}

	return true;
};

$(document).ready(function(){
	var pubObj = new Publish();
	window['$editor'] = pubObj.editor;
	
	var max_width = 800;
	var ifr = $('#xhe0_iframe')[0];
	var $doc = $(ifr.contentWindow.document);
    setInterval(function() {
    	$('img', $doc).each(function() {
    		var imgObj = $(this);
    		if(imgObj.width() > max_width) {
    			imgObj.width(max_width).removeAttr('height');
    		}
    	});
    }, 500);
    
	
});