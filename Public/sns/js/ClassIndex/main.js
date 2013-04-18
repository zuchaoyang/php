function main() {
	this.attachEventForSayBox();
	this.attachEvent();
	
	this.attachEventForLeft();
	
	//加载班级的动态信息
	var class_code = $('#class_code').val();
	$('#show_class_feed').loadFeed({
		url:'/Sns/Feed/List/getClassAllFeedAjax/class_code/' + class_code
	});
}

main.prototype = {
	//绑定事件
	attachEventForSayBox:function() {
		var class_code = $('#class_code').val();
		$('.say_text').sendBox({
			panels:'emote,upload',
			chars:140,
			type:'post',
			url:'/Sns/Mood/ClassMood/publishAjax/class_code/' + class_code,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				
				var feed_info = json.data;
				$('#show_class_feed').prependChild(feed_info);
			}
		});
	}

	//绑定主页的相关事件
	,attachEvent:function() {
		var me = this;
		
		//查看班级动态信息
		$('#show_class_feed_a').click(function() {
			$('#show_class_feed_a').removeClass('main_nav_a2').addClass('main_nav_a1');
			$('#show_children_feed_a').removeClass('main_nav_a1').addClass('main_nav_a2');
			
			$('#class_feed_list_div').show();
			$('#child_feed_list_div').hide();
			
			return false;
		});
		
		//查看孩子动态
		$('#show_children_feed_a').click(function() {
			$('#show_children_feed_a').removeClass('main_nav_a2').addClass('main_nav_a1');
			$('#show_class_feed_a').removeClass('main_nav_a1').addClass('main_nav_a2');
			
			//第一次点击需要加载孩子动态列表
			var inited = $(this).data('inited');
			if(!inited) {
				$('#show_children_feed').loadFeed({
					url:'/Sns/Feed/List/getUserChildrenFeedAjax'
				});
				$(this).data('inited', true);
			}
			
			$('#child_feed_list_div').show();
			$('#class_feed_list_div').hide();
			
			return false;
		});
	}

	,attachEventForLeft:function() {
		var class_code = $('#class_code').val();
		new photo($('.photo_index'), class_code, $('#photo_img')[0], $('#next_photo_btn')[0]);
	}
};

/**
 * 图片相关的动态部分管理
 * @param class_code
 * @param elem
 * @return
 */
function photo(parentObj, class_code, elem, next_btn) {
	this.parentObj = parentObj;
	this.class_code = class_code;
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

photo.prototype = {
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
			url:'/Sns/Feed/List/getClassAlbumFeedAjax/class_code/' + me.class_code + '/last_id/0',
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