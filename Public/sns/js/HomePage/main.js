function main() {
	this.attachEvent();
	this.init();
	
}

main.prototype = {
	init:function() {
		//完成对我的全部动态的初始化
		$('#user_all_feed_a').trigger('click');
	}		
	
	//动态相关的事件处理
	,attachEvent:function() {
		var me = this;
		
		//发布个人说说的相关事件的绑定
		$('.say_textarea', $('#send_mood_div')).sendBox({
			panels:'emote,upload',
			chars:140,
			type:'post',
			url:'/Sns/Mood/PersonMood/publishAjax',
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				
				var data = json.data;
				var feed_info = data.feed_info;

				$('#user_all_feed_div').prependChild(feed_info);

				if ($('#user_my_feed_a').data('inited')) {
					$('#user_my_feed_div').prependChild(feed_info);
				}
			}
		});		
		var client_account = $('#client_account').val();
		//获取用户的全部动态
		$('#user_all_feed_a').click(function() {
			var aObj = $(this);
			me.selectFeed({
				a_id:'user_all_feed_a', 
				div_id:'user_all_feed_div',
				feed_url:'/Sns/Feed/List/getUserAllFeedAjax/client_account/'+ client_account
			});
			
			return false;
		});
		
		//获取班级的全部动态
		$('#class_feed_a').click(function() {
			var class_code = $('#class_code').val();
			me.selectFeed({
				a_id:'class_feed_a', 
				div_id:'class_feed_div',
				feed_url:'/Sns/Feed/List/getClassAllFeedAjax/class_code/' + class_code
			});
			
			return false;
		});
		
		//获取好友的动态
		$('#user_friend_feed_a').click(function() {
			me.selectFeed({
				a_id:'user_friend_feed_a', 
				div_id:'user_friend_feed_div',
				feed_url:'/Sns/Feed/List/getUserFriendFeedAjax/client_account/'+ client_account
			});
			return false;
		});
		
		//获取与我相关
		$('#user_my_feed_a').click(function() {
			me.selectFeed({
				a_id:'user_my_feed_a', 
				div_id:'user_my_feed_div',
				feed_url:'/Sns/Feed/List/getUserMyFeedAjax/client_account/'+ client_account
			});
			
			return false;
		});
		
		new allphoto($('.xiangce'), $('#photo_img')[0], $('#next_photo_btn')[0]);
		
	}

	//选择当前的动态类型
    ,selectFeed:function(options) {
    	var aObj = $('#' + options.a_id);
    	
    	aObj.siblings('.sec_nav_selected').removeClass('sec_nav_selected').addClass('sec_nav_noselected');
    	aObj.removeClass('sec_nav_noselected').addClass('sec_nav_selected');

		if(!aObj.data('inited')) {
			$('#' + options.div_id).loadFeed({
				url:options.feed_url,
				skin:'mini'
			});

			aObj.data('inited', true);
		}
		
		$('#user_all_feed_div,#class_feed_div,#user_friend_feed_div,#user_my_feed_div').hide();
		$('#' + options.div_id).show();
	}	
};

/**
 * 图片相关的动态部分管理
 * @param class_code
 * @param elem
 * @return
 */
function allphoto(parentObj, elem, next_btn) {
	this.parentObj = parentObj;
	this.elem = elem;
	this.$elem = $(elem);
	this.$next_btn = $(next_btn);
	
	//当前游标的位置
	this.pointer = 0;
	//图片对象列表
	this.img_list = [];
	
	//初始化
	this.preload();

	
	var img_url = this.img_list[0] || "";
	this.$elem.attr('src', img_url);
	
	this.attachEvent();
}

allphoto.prototype = {
	//绑定相关的事件
	attachEvent:function() {
		var me = this;

		me.$next_btn.click(function() {
			me.pointer++;
	    	var img_url = "",
	    	    len = me.img_list.length;

	    	if (me.pointer >= len) {
	    		me.pointer = 0;
	    		img_url = me.img_list[0];
	    	} else {
	    		img_url = me.img_list[me.pointer];
	    	}
	    	
	    	me.$elem.attr('src', img_url);
	    	
		});
	}

    //预加载图片的相关信息
    ,preload:function() {
    	var me = this;
    	
    	$.ajax({
			type:'get',
			url:'/Sns/Feed/List/getAblumAllFeedAjax/last_id/0',
			dataType:'json',
			async:false,
			success:function(json) {
				if(json.status < 0) {
					
					return false;
				}
				me.parentObj.css('display', 'block');
				var data = json.data || {};
				var feed_list = data.feed_list || {};
				var last_id = data.last_id || {};
				
				//将图片信息加入到队列中
				for(var i in feed_list) {
					var img_url = feed_list[i].img_url || "";
					if(img_url) {
						me.img_list.push(img_url);
					}
				}
			}
		});
    }
};

$(document).ready(function() {
	new main();
});
