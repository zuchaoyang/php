function findpwdModifyCls(){
	this.attachEvent();
	this.checkpwdstrength();
	this.checkInput();
}	

findpwdModifyCls.prototype = new toolCls();

findpwdModifyCls.prototype.checkpwdstrength = function() {
	var self = this;
	$("#new_pwd").keyup(function(){
		var pwd = this.value.toString();
		var level = self.checkPasswordLevel(pwd);
		if(!pwd) {
			level = 0;
		}
		$("#level_" + level).show().siblings("p").hide();
	});
};

findpwdModifyCls.prototype.checkInput=function() {
	var self = this;
	$.formValidator.initConfig({
		debug:false,
		submitonce:false,
		errorfocus:false,
		onerror:function(msg,obj,errorlist){
		}
	});
	
	$("#new_pwd").formValidator({
		onshow:"请输入新密码",
		onfocus:"密码长度为6~20个字符",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,max:20,
		onerror:"密码长度为6~20个字符"
	});
	
	$("#re_enter").formValidator({
		onshow:"确认新密码",
		onfocus:"两次密码必须一致",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,max:20,
		onerror:"确认密码不正确"
	}).compareValidator({
		desid:"new_pwd",
		operateor:"=",
		onerror:"两次密码不一致,请确认"
	});
};

findpwdModifyCls.prototype.attachEvent=function() {
	var self = this;

	$('#go_back').click(function(){
		window.history.back(); //返回上一页
	});
	
//	$('#new_pwd').blur(function(){
//		self.checkNewPwd();
//	});
//	$('#re_enter').blur(function(){
//		self.checkReEnter();
//	});
	
	$('#find_modify').submit(function() {
		//表单提交前的检测
		return $.formValidator.pageIsValid('1')&& self.modifyFromAjax();
	});
	$('#submit_btn').click(function() {
		$('#find_modify').submit();
	});
};

//findpwdModifyCls.prototype.checkNewPwd=function(){
//	var self = this;
//	var new_pwd = $.trim($('#new_pwd').val());
//	
//	if(new_pwd.length > 20 || new_pwd.length < 6) {
//		$('#new_pwd_span').html('密码长度为6~20个字符');
//		return false;
//	}
//	$('#new_pwd_span').html('');
//	return true;
//};

//findpwdModifyCls.prototype.checkReEnter=function(){
//	var re_enter = $.trim($('#re_enter').val());
//	var new_pwd = $.trim($('#new_pwd').val());
//	if(!re_enter){
//		$('#re_enter_span').html('确认密码不能为空');
//		return false;
//	}
//	
//	if(re_enter != new_pwd) {
//		$('#re_enter_span').html('确认密码和新密码不一致');
//		return false;
//	}
//	$('#re_enter_span').html('');
//	return true;
//};

//ajax 提交表单  验证新密码和原密码是否一致
findpwdModifyCls.prototype.modifyFromAjax=function(){
	var self = this;
	var new_pwd = $.trim($('#new_pwd').val());
	var tokey = $('#tokey').val();
	var passed = true;
	$.ajax({
		type:'post',
		url:'/Uc/Findpwd/modifyFromAjax',
		data:{
			'new_pwd':new_pwd,
			'tokey':tokey
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				
				$('#new_pwdTip').removeClass().addClass('onError').html(json.info);
				passed = false;
			}
		}
	});
	
	return passed;
};


$(document).ready(function(){
	new findpwdModifyCls();
});