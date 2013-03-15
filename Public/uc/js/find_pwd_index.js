function findpwdCls(){
	this.default_username = "账号/手机号/邮箱";
	this.attachEvent();
	this.checkInput();
}

findpwdCls.prototype.checkInput=function() {
	var self = this;
	$.formValidator.initConfig({
		debug:false,
		submitonce:false,
		errorfocus:false,
		onerror:function(msg,obj,errorlist){}
	});
	$("#username").formValidator({
		onshow:self.default_username,
		onfocus:"账号只能是邮箱、账号或手机号",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:5,
		max:60,
		onerror:'账号长度为5-60'
	}).regexValidator({
		regexp:["mobile","email","num1"],
		datatype:"enum",
		onerror:"账号只能是邮箱、账号或手机号"
	});
};
findpwdCls.prototype.attachEvent=function() {
	var self = this;
	$('form:first').submit(function() {
		if(!$.formValidator.pageIsValid('1')) {
			return false;
		}
		//远程验证
		var is_passed = true;
		$.ajax({
			type:'post',
			url:'/Uc/Findpwd/checkUsernameAjax',
			dataType:'json',
			async:false,
			data:{'username' : $('#username').val()},
			success:function(json) {
				if(json.status < 0) {
					$('#username').focus();
					$('#usernameTip').removeClass().addClass('onError').html(json.info);
					is_passed = false;
				}
			}
		});
		return is_passed ? true : false;
	});
	$('#submit_btn').bind('click', function() {
		$('form:first').submit();
	});
	
};
$(document).ready(function(){
	var obj = new findpwdCls();
});


