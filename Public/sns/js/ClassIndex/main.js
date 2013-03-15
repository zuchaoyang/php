function main() {
	this.attachEventForSayBox();
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
			}
		});
	}

};


$(document).ready(function() {
	new main();
});