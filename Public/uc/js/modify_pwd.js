function modify_password(){
	this.change_pwd();
	this.checkpwd_level();
};

modify_password.prototype = new toolCls();

modify_password.prototype.checkpwd_level = function() {
	var self = this;
	$("#new_client_pwd").keyup(function(){
		var new_client_pwd = $("#new_client_pwd").val().toString();
		var level = self.checkPasswordLevel(new_client_pwd);
		var passwd = $("#new_client_pwd").val();
		if(!passwd) {
			level = 0;
		}
		$("#level_" + level).show().siblings("p").hide();
	});
};

modify_password.prototype.checkInput = function() {
	$.formValidator.initConfig({
		formid:"change_password",
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist){
//		$.map(errorlist,function(msg1){alert(msg1)});
//		alert(msg);

		}
	});
	
	$("#old_client_pwd").formValidator({
		onshow:"请输入旧密码",
		onfocus:"旧密码不能为空！",
		oncorrect:"&nbsp;"
	}).regexValidator({
		regexp:"notempty",
		datatype:"enum",
		onerror:"旧密码不能为空"
	});
	$("#new_client_pwd").formValidator({
		onshow:"请输入新密码",
		onfocus:"密码长度为6~20个字符",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,max:20,
		onerror:"密码长度为6~20个字符"
	});
	
	$("#re_client_pwd").formValidator({
		onshow:"确认新密码",
		onfocus:"两次密码必须一致",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:1,
		onerror:"密码不能为空,请确认"
	}).compareValidator({
		desid:"new_client_pwd",
		operateor:"=",
		onerror:"两次密码不一致,请确认"
	});
};

modify_password.prototype.change_pwd = function() {
	var self = this;
	$("#change_pwd").click(function(){
		$("#change_password").submit();
	});
};

$(document).ready(function(){
	var obj = new modify_password();
	obj.checkInput();
});