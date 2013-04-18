function person_mood_show() {
	this.attachEvent();
	this.init();
}

//刷新说说的评论数
person_mood_show.reflushCommentNum=function(num) {
	var html = $('#comment_mood_a').html() || "";
	//如果有评论数
	var pattern = /((\d+))/;
	if(html.match(pattern)) {
		html = html.replace(pattern, function(a, b) {
			return parseInt(b) + num;
		});
	} else {
		html = html + "(" + num + ")";
	}
	$('#comment_mood_a').html(html);
};

person_mood_show.prototype = {
	//是否还有下一页
	hasNextPage : true,
	
	//返回mood_id的最大值
	maxCommentId : 0,
	
	attachEvent:function() {
		var me = this;
		//删除
		$('#delete_mood_a').click(function() {
			var mood_id = $('#mood_id').val();
			$.showDeleteMood({
				url:"/Sns/Mood/PersonMood/deletePersonMoodAjax/mood_id/" + mood_id,
				lock:true,
				callback:function(data) {
					if(data.redirect_url) {
						window.location.href = data.redirect_url;
					}
				}
			});
		});
		
		//评论
		$('#comment_mood_a').click(function() {
			var sendDiv = $('#send_1st_mood_div');
			if(sendDiv.is(':visible')) {
				$('#send_1st_mood_div').hide();
			} else {
				$('#send_1st_mood_div').show();
			}
		});
	},
	
	init:function() {
		var me = this;
		//初始化一级评论发送框
		var mood_id = $('#mood_id').val();
		$('.pl_textarea', $('#send_1st_mood_div')).sendBox({
			panels:'emote',
			type:'post',
			url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
			dataType:'json',
			data:{
				mood_id:mood_id,
				up_id:0
			},
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//处理成功后的回调函数
				var divObj = comment_1st_unit.create(json.data || {});
				$('#comment_list_div').prepend(divObj);
				person_mood_show.reflushCommentNum(1);
			}
		});
		
		//加载图片信息
		$('#mood_img').each(function() {
			var data_original = $(this).attr('data-original');
			if(data_original) {
				var imgObj = $(this);
				var img = new Image();
				img.src = data_original;
				img.onload = function() {
					var height = img.height;
					var width = img.width;
					if(width > 620) {
						img.width = 620;
						img.height = (620 / width) * height;
					}
					imgObj.replaceWith($(img));
				};
			}
		});
		
		//加载评论信息
		var options = {
			callback:function(num) {
				person_mood_show.reflushCommentNum(num);
			}
		};
		$('#comment_list_div').loadMoodComments(mood_id, options);
	}
};

$(document).ready(function() {
	new person_mood_show();
});