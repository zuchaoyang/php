function userActivationCls() {
	this.validator();
	this.init();
	this.attachEvent();
}
userActivationCls.prototype = new toolCls();
userActivationCls.prototype.init=function() {
	$('#password,#password_sure').val('');
	$('#password').focus();
};
userActivationCls.prototype.attachEvent = function() {
	var self = this;
	$('#password').keyup(function() {
		self.rotateStyle();
		if(this.value.toString().length > 20) {
			alert('密码长度不能超过20位!');
		}
		return false;
	});
	$('#submit_btn').bind('click', function() {
		$('form:first').submit();
	});
};
userActivationCls.prototype.rotateStyle = function() {
	var password = $('#password').val().toString();
	var level = this.checkPasswordLevel(password);
	//样式轮换
	var div_mark = "level_";
	$('p[id^="' + div_mark + '"]', $('form:first')).each(function() {
		var num = $(this).attr('id').toString().substring(div_mark.length);
		if(num == level) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
};
userActivationCls.prototype.validator=function() {
	$.formValidator.initConfig({
		formid:'activate_form',
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist){
			$(obj).focus();
		}
	});
	$("#password").formValidator({
		onshow:"请输入新密码",
		onfocus:"密码长度为6~20个字符",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,
		max:20,
		onerror:"密码长度为6~20个字符"
	});
	$("#password_sure").formValidator({
		onshow:"请再次输入新密码!",
		onfocus:"密码长度为6~20个字符",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,
		max:20,
		onerror:"密码长度为6~20个字符"
	}).compareValidator({
	    desid:"password",
	    operateor:"=",  
	    onerror:"2次密码不一致,请确认"
	});
};
$(document).ready( function() {
	new userActivationCls();
});