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
})(jQuery);

function draftList () {
	this.attachEvent();
	this.dynamicAttachEvent();
	this.attacheEventUserDefine();
};

draftList.prototype.attachEvent=function() {
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
		var url_str = "/Sns/Blog/PersonList/index/type_id/-1";
		
		if ($.trim(start_time)) {
			url_str = url_str + "/start_time/" + start_time;
		}
		if ($.trim(end_time)) {
			url_str = url_str + "/end_time/" + end_time;
		}
		
		window.location.href = url_str; 
		return false;
	});
	
};

draftList.prototype.attacheEventUserDefine=function() {
	$('#count_num').bind({
		reflushEvent:function() {
			var count_num = $(this).html();
			if (count_num > 0) {
				$(this).html(count_num - 1);
			}
		}
	});
};
	
//动态绑定事件
draftList.prototype.dynamicAttachEvent=function() {
	var me = this;
	
	//绑定删除按钮
	$('#draft_list_div').delegate('.delete_a', 'click', function() {
		var context = $(this).closest('tr');
		var blog_id = $(this).attr('id');
		var class_code = $.trim($('#class_code').val());
		
		art.dialog({
			title:'删除日志',
			content:"确认要删除此草稿吗？",
			cancel:true,
			follow:this,
			icon :'question',
			drag  :false,
			ok:function() {
				$.ajax({
					type:'get',
					url:'/Sns/Blog/PersonPublish/deleteDraftAjax/blog_id/' + blog_id,
					dataType:'json',
					success:function(json) {
						//操作失败时的处理
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						
						//操作成功删除当前的tr元素
						context.remove();
						$('#count_num').trigger('reflushEvent');
						return true;
					}
				});
			}
		});
		
		return false;
	});
	
};




//页面右侧日志分类列表显示
function show_type() {
	this.init();
	this.attachEvent();
}
show_type.prototype  = {
	init:function() {
		var me = this;
		var data = me.loadTypeDatas();
		
		me.fillRightTypeListDatas(data);
	},
	attachEvent:function() {
		$('#other_type').toggle(function() {
			$('#down_div').show();
		}, function() {
			$('#down_div').hide();
		});
	},
	loadTypeDatas:function() {
		var me = this;	
		var class_code = $('#class_code').val();
		var data = false;
		$.ajax({
			type:'get',
			url:'/Sns/Blog/Type/getBlogTypeListAjax/class_code/' + class_code,
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
	},
	fillRightTypeListDatas:function(type_list) {
		typelist = type_list || {};
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
			var url_str = '/Sns/Blog/PersonList/index/class_code/' + class_code + '/type_id/' + type_data.type_id;
			type_data.url = url_str;
			n ++ ;
			if (n <= show_type_num) {
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
		
	},
	createTopType:function(type_data) {
		type_data = type_data || {};
		var context = $('#top_div');
		var cloneObj = $('.clone', context).clone().removeClass('clone').show();
		
		cloneObj.renderHtml(type_data).appendTo(context);
	},
	createDownType:function(type_data) {
		type_data = type_data || {};
		var context = $('#down_div');
		var cloneObj = $('.clone', context).clone().removeClass('clone').show();
		
		cloneObj.renderHtml(type_data).appendTo(context);
	}
	
};


$(document).ready(function(){
	new draftList();
	new show_type();
});