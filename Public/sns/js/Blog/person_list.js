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

function PersonList () {
	this.attachEvent();
	this.init();
	this.dynamicAttachEvent();
};

//页面初始化加载数据
PersonList.prototype.init = function() {
	//调用查询方法初始化数据
	var context = $('#search_h3');
	$('#search_btn_a', context).trigger('click');
};

PersonList.prototype.attachEvent=function() {
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
		var end_time = $('#end_time', context).val();
		var type_id = !$.trim($('#type_id').val()) ? -1 : $.trim($('#type_id').val()) ;
		
		var search_options = {
			start_time : start_time,
			end_time : end_time,
			type_id : type_id
		};
		
		//删除旧数据
		var contexDiv = $('#blog_list_div');
	    var oldDiv = $('.single_blog:gt(0)', contexDiv);
		oldDiv.remove();
		
		//查询加载数据
		$('#search_h3').data('search_options', search_options);
		$('#more_blog').data('page', 1);
		me.loadBlogDatas(1);
		
		return false;
	});
	
	
	//绑定加载更多按钮
	$("#more_blog").click(function(){
		var aObj = $(this);
		var has_next = aObj.data('has_next');
		if(has_next) {
			var page = aObj.data('page') || 1;
			aObj.data('page', page + 1);
	
			//获取下一页数据 并对数据进行处理
			me.loadBlogDatas(page + 1);
		}
		//阻止a事件冒泡
		return false;
	});
	
};
	
//动态绑定事件
PersonList.prototype.dynamicAttachEvent=function() {
	var me = this;
	
	//查看全文
	$('#blog_list_div').delegate('.view_a', 'click', function() {
		var ancestorObj = $(this).closest('.single_blog');
		var blog_data = ancestorObj.data('data') || {};
		var blog_id = blog_data.blog_id;
		var client_account = $('#client_account').val();
		
		window.location.href="/Sns/Blog/PersonContent/index/blog_id/" + blog_id + "/client_account/" + client_account;
		return false;
	});
	
	//绑定删除按钮
	$('#blog_list_div').delegate('.delete_a', 'click', function() {
		var context = $(this).closest('.single_blog');
		var blog_data = context.data('data') || {};
		var blog_id = blog_data.blog_id;
		var client_account = $.trim($('#client_account').val());
		
		art.dialog({
			title:'删除日志',
			content:"确认要删除这篇日志吗？",
			cancel:true,
			follow:this,
			icon :'question',
			drag  :false,
			ok:function() {
				$.ajax({
					type:'get',
					url:'/Sns/Blog/PersonPublish/deleteBlogAjax/blog_id/' + blog_id + '/client_account/' + client_account,
					dataType:'json',
					success:function(json) {
						//操作失败时的处理
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						//操作成功时的处理
						$.showSuccess(json.info);
						//删除当前的tr元素
						context.remove();
						
						//通知右侧刷新
						$('#blog_type_list_div').trigger('reflushEvent');
						
						return true;
					}
				});
			
			}

		});
		
		return false;
	});
	
	
};

//数据加载数据
PersonList.prototype.loadBlogDatas=function(page) {
	var me = this;
	//缓存搜索条件
	var options = $('#search_h3').data('search_options') || {};
	var client_account = $('#client_account').val();
	var page = page >= 1 ? page : 1;
	$.ajax({
		type:'post',
		url:'/Sns/Blog/PersonList/getBlogListAjax/client_account/' + client_account + "/page/" + page,
		data:options,
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			var data = json.data || {};
			
			//分页处理
			me.fillPage(data.page_list);
			//数据显示处理
			me.fillBlogList(data.blog_list || {});
			
		}
	});
};

//分页处理
PersonList.prototype.fillPage=function(page_list){
	page_list = page_list || {};
	
	if(!page_list.has_next_page) {
		//改变按钮
		$('#more_blog').data('has_next', false).html("没有更多了");
	} else {
		$('#more_blog').data('has_next', true).html("点击查看更多");
	}
};

//数据显示处理
PersonList.prototype.fillBlogList=function(blog_list){
	blog_list = blog_list || {};
	var contexDiv = $('#blog_list_div');
    var cloneObj = $('.clone', contexDiv);
    var page = $('#more_blog').data('page') || 1;
    $('#not_have_blog').hide();
    if ($.isEmptyObject(blog_list)) {
    	$('#more_blog').closest('p').hide();
    	
    	$('#not_have_blog').show();
    	return false;
    }
    
    //将实体替换成html标签
	function unescapeHTML(content) {
		if (typeof content == 'string') {
            return content.replace('&lt;', '<').replace('&gt;', '>').replace('&quot;', '"').replace('&#x27;', "'").replace('&amp;', '&');
        }
        return content;
	}
	
	for(var i in blog_list) {
		var blog = blog_list[i];
		var divObj = cloneObj.clone().removeClass('clone').appendTo(contexDiv).show();
		//数据绑定
		divObj.data('data', blog || {});
		//添加数据信息
		divObj.renderHtml(blog);
		//实体的转换
		divObj.html(unescapeHTML(divObj.html()));
	}
};

//页面右侧日志分类列表显示
function show_type() {
	this.init();
	this.attachEvent();
	this.attacheEventUserDefine();
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
	
	attacheEventUserDefine:function() {
		var me = this;
		$('#blog_type_list_div').bind({
			reflushEvent:function() {
				me.init();
			}
		});
	},
	
	loadTypeDatas:function() {
		var me = this;	
		var client_account = $('#client_account').val();
		var data = false;
		$.ajax({
			type:'get',
			url:'/Sns/Blog/PersonType/getBlogTypeListAjax/client_account/' + client_account,
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
		var client_account = $('#client_account').val();
		var n = 0;
		var show_type_num = 10;
		//清空已有数据
		$('#top_div').children(':gt(0)').remove();
		$('#down_div').children(':gt(0)').remove();
		
		//循环赋值
		for(var i in type_list) {
			var type_data = type_list[i];
			var url_str = '/Sns/Blog/PersonList/index/client_account/' + client_account + '/type_id/' + type_data.type_id;
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
	new PersonList();
	new show_type();
	
	
	var max_width = 450;
	var $doc = $('#blog_list_div');
    setInterval(function() {
    	$('img', $doc).each(function() {
    		var imgObj = $(this);
    		if(imgObj.width() > max_width) {
    			imgObj.width(max_width).removeAttr('height');
    		}
    	});
    }, 500);
});