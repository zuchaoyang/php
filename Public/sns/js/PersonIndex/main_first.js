
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
			}
		});
	}
};

$(document).ready(function() {
	new main_first();
});