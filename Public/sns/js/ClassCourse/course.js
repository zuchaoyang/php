/**
 * 交互机制：
 *    1. 显示页面和弹出层之间的通讯使用的回调函数机制解耦；
 *    2. li元素中的孩子结点的事件响应，使用的父结点代理的方式，这样就可以避免是有关联id建立同辈元素之间的对应关系；
 */
(function($) {
	$.showError=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg || '操作失败!',
			icon:'error',
			fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
		}).time(2);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_succeed_dialog',
			title:'成功提示',
			content:msg || '操作成功!',
			icon:'succeed',
			cancel:false,  //是否关闭 （不显示关闭按钮）
			fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
		}).time(2);
	};
	$.closeDialog=function() {
		var dialogObj = art.dialog.list['edit_course_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	};
})(jQuery);

function courseCls() {
	this.default_course_name = '--';
	
	this.attachEvent();
	this.attachEventForEditDiv();
	this.attachEventUserDefine();
	
	//检测按钮信息是否正确
	this.checkDeleteBtn();
}

//绑定自定义事件
courseCls.prototype.attachEventUserDefine=function() {
	var me = this;
	$('li', $('#course_main_div')).bind({
		//远程保存数据，因为只有li元素知道如何收集数据
		'saveEvent':function() {
			var contextLi = $(this);
			var course_name = $('.course_name_a', contextLi).html();
			var arr = $('.course_keys', contextLi).val().toString().split(':') || [];
			var weekday = arr[0];
			var num_th 	= arr[1];
			var class_code = $('#class_code').val();
			$.ajax({
				type:'post',
				url:'/Sns/ClassCourse/Course/saveCourseAjax',
				data:{
					'course_name': course_name,
					'weekday'	 : weekday,
					'num_th' 	 : num_th,
					'class_code' : class_code
				},
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
					} else {
						$.showSuccess(json.info);  //成功不在提示
						//需要修改course_id的值
						var data = json.data;
						$('#course_id', contextLi).val(data.course_id || 0);
					}
				}
			});
		},
		//远程保存数据，因为只有li元素知道如何处理数据
		'deleteEvent' : function() {
			var contextLi = $(this);
			var course_id = $('#course_id', contextLi).val();
			var class_code = $('#class_code').val();
			$.ajax({
				type:'post',
				url:'/Sns/ClassCourse/Course/delCourseAjax',
				data:{
					'course_id': course_id,
					'class_code' : class_code
				},
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					} 
					$.showSuccess(json.info);
					$('.course_name_a', contextLi).html(me.default_course_name);
				}
			});
		},
		//移除删除按钮
		'dropDeleteBtnEvent':function() {
			var deleteBtnObj = $('.icoo_tcc ', $(this));
			var prevObj = deleteBtnObj.prev();
			var nextObj = deleteBtnObj.next();
			var parentObj = deleteBtnObj.parent();
			//记录删除按钮恢复现场
			$(this).data('options', {
				'elem':deleteBtnObj,
				//查找并记录回写的位置
				'callback':function() {
					if(prevObj.length == 1) {
						deleteBtnObj.insertAfter(prevObj);
						return true;
					}
					if(nextObj.length == 1) {
						deleteBtnObj.insertBefore(nextObj);
						return true;
					}
					deleteBtnObj.appendTo(parentObj);
				}
			});
			deleteBtnObj.detach();
		},
		//恢复删除按钮
		'recoverDeleteBtnEvent':function() {
			var course_name = $('.course_name_a', $(this)).html();
			if(!course_name || course_name == me.default_course_name) {
				return false;
			}
			var options = $(this).data('options') || {};
			if(typeof options.callback == 'function') {
				options.callback();
			}
		}
	});
};

courseCls.prototype.attachEvent=function(){
	var me = this;
	var context = $('.course_main_right');
	//绑定课程名称的点击事件
	$('.course_name_a', context).click(function() {
		var spanObj = $(this);
		var course_name = $(this).html();
		//使用了回调机制解耦弹出层处理完后的处理方法
		me.editCourse({
			'course_name' : course_name,
			'callback' : function(new_name) {
				var old_name = spanObj.html();
				spanObj.html(new_name);
				if(new_name != old_name && new_name != me.default_course_name) {
					spanObj.parents('li:first').trigger('saveEvent').trigger('recoverDeleteBtnEvent');
				}
			}
		});
	});
	//绑定删除按钮对应的事件
	$('.icoo_tcc ', context).click(function() {
		$(this).parents('li:first').trigger('deleteEvent').trigger('dropDeleteBtnEvent');
	});
	//绑定效果
	$('li', $('#course_main_div')).hover(function() {
		$('.icoo_tcc ', $(this)).css('display', 'inline');
	}, function() {
		$('.icoo_tcc ', $(this)).hide();
	});
};
//绑定弹出层中的相应事件
courseCls.prototype.attachEventForEditDiv=function() {
	var me = this;
	var context = $('#edit_course');
	$("#course_table tr", context).children('td').each(function() {
		$("a", $(this)).bind("click", function() {
			var options = $('#edit_course').data('options') || {};
			//负责新数据的传回
			if(typeof options.callback == 'function') {
				options.callback($(this).html());
			}
			$.closeDialog();
		});
	});
	//自定义课程名称添加
	$('#course_name_btn', context).bind("click", function(){
		var course_name = $.trim($('#course_name', context).val().toString());
		$('#span_course_name').html('');
		if(!course_name) {
			$('#span_course_name').html('自定义课程名称不能为空');
			return false;
		}
		var options = $('#edit_course').data('options') || {};
		//负责新数据的传回
		if(typeof options.callback == 'function') {
			options.callback(course_name);
		}
		$.closeDialog();
	});
	
	$('#course_name', context).keydown(function(evt) {
		var keyCode = evt.keyCode || evt.which;
		if(keyCode == 13) {
			$('#course_name_btn', context).trigger('click');
		}
	});
	
};

//弹层
courseCls.prototype.editCourse=function(options) {
	var me = this;
	options = options || {};
	//将设置信息保存到div上
	$('#edit_course').data('options', options);
	//弹出层
	var dialogObj = art.dialog.list['edit_course_dialog'];
	if(!$.isEmptyObject(dialogObj)) {
		dialogObj.close();
	}
	art.dialog({
		id:'edit_course_dialog',
		titile:'选择您要设置的课程',
		content:$('#edit_course').get(0),

		fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
	});
	
	var context = $('#edit_course');
	if(options.course_name != me.default_course_name) {
		$('#course_name', context).val(options.course_name);
	} else {
		$('#course_name', context).val('');
	}
	$('#course_name_old', context).val(options.course_name);
	$('#span_course_name', context).html('');
};
//批量清理课程数据
courseCls.prototype.checkDeleteBtn=function() {
	var me = this;
	$('.course_name_a', $('#course_main_div li')).each(function() {
		var course_name = $.trim($(this).html());
		if(!course_name || course_name == me.default_course_name) {
			$(this).parents('li:first').trigger('dropDeleteBtnEvent');
		}
		//将名字设置成系统默认显示的名字
		if(!course_name) {
			$(this).html(me.default_course_name);
		}
	});
};

$(document).ready(function(){
	new courseCls();
});
