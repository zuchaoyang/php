function main_first() {
	this.attachEvent();
}

main_first.prototype = {
	attachEvent:function() {
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

				$('#my_feed_list_div').prependChild(feed_info);
			}
		});
		
		//加载用户动态信息
		var vuid = $('#vuid').val();
		$('#my_feed_list_div').loadFeed({
			url:'/Sns/Feed/List/getUserMyFeedAjax/client_account/' + vuid,
			skin:'mini'
		});
	}
};

$(document).ready(function() {
	new main_first();
});