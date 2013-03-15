function class_mood_show() {
	this.attachEvent();
	this.init();
}

//刷新说说的评论数
class_mood_show.reflushCommentNum=function(num) {
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

class_mood_show.prototype = {
	//是否还有下一页
	hasNextPage : true,
	
	//返回mood_id的最大值
	maxCommentId : 0,
	
	attachEvent:function() {
		var me = this;
		//删除
		$('#delete_mood_a').click(function() {
			var mood_id = $('#mood_id').val();
			var class_code = $('#class_code').val();
			$.showDeleteMood({
				url:'/Sns/Mood/ClassMood/deleteClassMoodAjax/class_code/' + class_code + "/mood_id/" + mood_id,
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
		$('.pl_textarea', $('#send_1st_mood_div')).publishBySendBox(mood_id, {
			callback:function(divObj) {
				$('#comment_list_div').prepend(divObj);
				class_mood_show.reflushCommentNum(1);
			}
		});
		
		//加载评论信息
		$('#comment_list_div').loadMoodComments(mood_id, {
			callback:function(num) {
				class_mood_show.reflushCommentNum(num);
			}
		});
	}
};

$(document).ready(function() {
	new class_mood_show();
});