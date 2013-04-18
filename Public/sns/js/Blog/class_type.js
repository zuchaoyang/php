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
	
	//返回字符串的长度一个汉字算两个长度
	$.strLength=function(str) {
		 var bytesCount = 0;
		 for (var i = 0; i < str.length; i++) {
			 var c = str.charAt(i);
			 if (/^[\u0000-\u00ff]$/.test(c)) {
				 bytesCount += 1;
			 }
			 else {
				 bytesCount += 2;
			 }
		 }
		 return bytesCount;
	};
})(jQuery);

function typeList () {
	this.init();
	this.attachEvent();
	this.dynamicAttachEvent();
};

//页面初始化加载数据
typeList.prototype.init = function() {
	var me = this;
	//获取数据
	var datas = this.loadTypeDatas();
	
	//页面左侧动态展示
	me.fillLeftTypeListDatas(datas);
	
	//页面右侧动态展示
	me.fillRightTypeListDatas(datas);
};

//获取分类数据
typeList.prototype.loadTypeDatas = function() {
	var me = this;
	var class_code = $('#class_code').val();
	var data = false;
	$.ajax({
		type:'get',
		url:'/Sns/Blog/Type/getBlogTypeListAjax/class_code/' + class_code ,
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			
			data = json.data || {};
		}
	});
	
	return data;
};

//左侧数据展示
typeList.prototype.fillLeftTypeListDatas = function(type_list) {
	type_list = type_list || {} ;
	//循环赋值
	for(var i in type_list) {
		this.addTrObj(type_list[i]);
	}
};

//创建一个tr对象
typeList.prototype.addTrObj=function(type_datas) {
	type_datas = type_datas || {};
	if($.isEmptyObject(type_datas)) {
		return false;
	}
	
	var contextTab = $('#left_type_list_tab');
	var cloneObj =  $('.clone', contextTab);
	var cloneDefaultObj =  $('.clone_default', contextTab);
	var insertPosObj = $('.insert_pos_selector', contextTab);
	
	if(type_datas.type_id == 0) {
		var trObj = cloneDefaultObj.clone().removeClass('clone_default');
	} else {
		var trObj = cloneObj.clone().removeClass('clone');
	}
	//数据绑定
	trObj.data('data', type_datas || {}).renderHtml(type_datas);
	//添加数据信息
	$(trObj).show().insertBefore(insertPosObj);
};


//右侧数据展示
typeList.prototype.fillRightTypeListDatas = function(type_list) {
	type_list = type_list || {};
	if($.isEmptyObject(type_list)) {
		return false;
	}
	var me = this;
	var class_code = $('#class_code').val();
	var n = 0;
	var show_type_num = 10;
	
	//清空已有数据
	$('#top_div').children(':gt(0)').remove();
	$('#down_div').children(':gt(0)').remove();
	//循环赋值
	for(var i in type_list) {
		var type_data = type_list[i];
		var url_str = '/Sns/Blog/List/index/class_code/' + class_code + '/type_id/' + type_data.type_id;
		type_data.url = url_str;
		n ++;
		if(n <= show_type_num) {
			me.createTopType(type_data);
		} else {
			me.createDownType(type_data);
		}
	}
	
	var other_contex = $('#other_type_div');
	var other_type_nums = (n-show_type_num) > 0 ? n-show_type_num : 0;
	other_contex.hide();
	if (other_type_nums > 0) {
		other_contex.show();
		$('#other_type_nums', other_contex).html(n-show_type_num);
	};
	
	
};

//动态创建右侧typeList
typeList.prototype.createTopType=function(type_data) {
	type_data = type_data || {};
	var context = $('#top_div');
	var cloneObj = $('.clone', context).clone().removeClass('clone').show();
	
	cloneObj.renderHtml(type_data).appendTo(context);
};
typeList.prototype.createDownType=function(type_data) {
	type_data = type_data || {};
	var context = $('#down_div');
	var cloneObj = $('.clone', context).clone().removeClass('clone').show();
	
	cloneObj.renderHtml(type_data).appendTo(context);
};

typeList.prototype.attachEvent = function() {
	var me = this;
	//查询事件的绑定
	var context = $('#search_h3');
	//开始时间
	$('#start_time', context).click(function() {
		WdatePicker({el:'start_time'});
	});
	//结束时间
	$('#end_time', context).click(function() {
		WdatePicker({el:'end_time'});
	});
	$('#search_btn_a', context).click(function(){
		var start_time = $('#start_time', context).val();
		var end_time   = $('#end_time', context).val();
		var class_code = $('#class_code').val();
		var url_str = "/Sns/Blog/List/index/type_id/-1/class_code/" + class_code;
		
		if ($.trim(start_time)) {
			url_str = url_str + "/start_time/" + start_time;
		}
		if ($.trim(end_time)) {
			url_str = url_str + "/end_time/" + end_time;
		}
		
		window.location.href = url_str; 
		return false;
	});
	
	//绑定添加日志分类按钮
	$('#add_type').click(function() {
		$('.add_type_tr_selector').trigger('showEvent', [{
			callback:function(type_datas) {
				me.addTrObj(type_datas);
				// 更新右侧列表
				var datas = me.loadTypeDatas();
				me.fillRightTypeListDatas(datas);
			}
		}]);
	});
	
	$('#other_type').toggle(function() {
		$('#down_div').show();
	}, function() {
		$('#down_div').hide();
	});
};
	
//动态绑定事件
typeList.prototype.dynamicAttachEvent=function() {
	var me = this;
	
	//绑定删除按钮
	$('#left_type_list_tab').delegate('.delete_a', 'click', function() {
		var context = $(this).closest('.single_type');
		var type_data = context.data('data') || {};
		var type_id = type_data.type_id;
		
		$('#delete_msg').trigger('openEvent', [{
			data:{
				type_id:type_id
			},
			callback:function() {
				context.remove();
				// 更新右侧列表
				var datas = me.loadTypeDatas();
				me.fillRightTypeListDatas(datas);
			}
		}]);
		
		return false;
	});
	
	//绑定编辑按钮
	$('#left_type_list_tab').delegate('.modify_a', 'click', function() {
		var context = $(this).closest('.single_type');
		var type_data = context.data('data') || {};
		$('#modify_tr').trigger('showEvent', [{
			data:type_data,
			target_elem:context,
			callback:function(new_name) {
				$('td:eq(0)', context).html(new_name);
				//数据保存
				type_data.name = new_name;
				context.data('data', type_data);
				// 更新右侧列表
				var datas = me.loadTypeDatas();
				me.fillRightTypeListDatas(datas);
			}
		}]);
		
		return false;
	});
	
};

function delete_type() {
	this.attachEvent();
	this.attachEventUserDefine();
}

delete_type.prototype = {
	attachEventUserDefine:function() {
		$('#delete_msg').bind({
			//打开预览弹层
			openEvent:function(evt, options) {
				options = options || {};
				var datas = options.datas || {};
				var divObj = $(this);
				divObj.data('options', options);
				art.dialog({
					id:'delete_type_dialog',
					title:'删除分类',
					padding: '0px 0px',
					drag  :false,
					content:divObj.get(0)
				});
			},
			//关闭预览弹层
			closeEvent:function() {
				var dialogObj = art.dialog.list['delete_type_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
		});
	},
	
	//事件绑定
	attachEvent:function() {
		var context = $('#delete_msg');
		$('#sure_btn', context).click(function() {
			var options = $(this).closest('#delete_msg').data('options') || {};
			var type_id = options.data.type_id;
			var class_code = $('#class_code').val();
			$('#delete_msg').trigger('closeEvent');
			$.ajax({
				type:'get',
				url:'/Sns/Blog/Type/deleteTypeAjax/type_id/' + type_id + '/class_code/' + class_code,
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					$.showSuccess(json.info);
					if(typeof options.callback == 'function') {
						options.callback.call();
					}
				}
			});
		});
		
		$('#cancel_btn', context).click(function() {
			context.trigger('closeEvent');
		});
	}

};

function modify_type() {
	this.attachEvent();
	this.attachEventUserDefine();
}

modify_type.prototype = {
	attachEventUserDefine:function() {
		$('#modify_tr').bind({
			//打开预览弹层
			showEvent:function(evt, options) {
				var trObj = $(this);
				//如果存在实例，则关闭
				$('#modify_tr').trigger('closeEvent');
				
				var target_elem = options.target_elem;
				var data = options.data || {};
				
				trObj.insertAfter(target_elem);
				target_elem.hide();
				trObj.data('options', {});
				
				trObj.data('options', options).show();

				$('#name', trObj).val(data.name);
			},
			
			//关闭预览弹层
			closeEvent:function() {
				var trObj = $(this).hide();
				var options = trObj.data('options') || {};
				
				var target_elem = options.target_elem;
				if(!$.isEmptyObject(target_elem)) {
					$(target_elem).show();
				}
			}
		});
	},

	//事件绑定
	attachEvent:function() {
		var context = $('#modify_tr');
		$('#modify_sure_btn', context).click(function() {
			var options = $(this).closest('#modify_tr').data('options') || {};

			var type_id = options.data.type_id;
			var old_name = options.data.name;
			var class_code = $('#class_code').val();
			var new_name = $('#name', context).val();
			
			if(!$.trim(new_name)) {
				$.showError('分类名字不能为空!');
				return false;
			} else if (old_name == new_name) {
				$('#modify_tr').trigger('closeEvent');
				return false;
			}
			
			$.ajax({
				type:'post',
				url:'/Sns/Blog/Type/modlfyTypeAjax/class_code/' + class_code ,
				data:{
					name:new_name,
					type_id:type_id
				},
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					
					$('#modify_tr').trigger('closeEvent');
					if(typeof options.callback == 'function') {
						options.callback(new_name);
					}
				}
			});
		});
		
		$('#cancel_btn', context).click(function() {
			context.trigger('closeEvent');
		});
		
	}
};


function add_type() {
	this.attachEvent();
	this.attachEventUserDefine();
}
add_type.prototype = {
	attachEventUserDefine:function() {
		$('.add_type_tr_selector').bind({
			showEvent:function(evt, options) {
				$(this).data('options', options || {}).show();
				$('#name', $(this)).val('').focus();
			},
			hideEvent:function() {
				$(this).hide();
			}
		});
	},
		
	attachEvent:function() {
		var context = $('.add_type_tr_selector');
		$('#sure_btn', context).click(function() {
			var name = $('#name', context).val();
			var class_code = $('#class_code').val();
			if (!$.trim(name)) {
				$.showError('请输入分类名称');
				return false;
			}
			if($.strLength(name) > 12) {
				$.showError('对不起,分类名称长度不能超过12个字母/6个汉字');
				return false;
			}
			
			$.ajax({
				type:'post',
				url:'/Sns/Blog/Type/publishAjax',
				data:{name:name,class_code:class_code},
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					var options = context.data('options') || {};
					if(typeof options.callback == 'function') {
						context.trigger('hideEvent');
						options.callback(json.data);
					}
				}
			});
		});
		
		$('#cancel_btn', context).click(function() {
			$('.add_type_tr_selector').trigger('hideEvent');
		});
	}	
	
};

$(document).ready(function(){
	new typeList();
	new delete_type();
	new add_type();
	new modify_type();
});