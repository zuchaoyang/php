function findpwdEmailCls(){
    this.time_out = 60;
	this.attachEvent();
	this.checkInput();
}

findpwdEmailCls.prototype.checkInput=function() {
	var self = this;
	$.formValidator.initConfig({
		validatorgroup:"1",
		debug:false,
		submitonce:false,
		errorfocus:false,
		onerror:function(msg,obj,errorlist){
		}
	});
	$.formValidator.initConfig({
		validatorgroup:"2",
		debug:false,
		submitonce:false,
		errorfocus:false,
		onerror:function(msg,obj,errorlist){
		}
	});
	
	$("#email").formValidator({
		validatorgroup:"1",
		onshow:"",
		onfocus:"请输入正确的邮箱",
		oncorrect:"&nbsp;"
	}).regexValidator({
		regexp:["email"],
		datatype:"enum",
		onerror:"邮箱格式错误"
	});
	
	$("#scode").formValidator({
		validatorgroup:"2",
		onshow:"请输入正确的邮箱验证码",
		onfocus:"邮箱验证码为六位数字",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,
		max:6,
		onerror:'邮箱验证码格式错误'
	}).regexValidator({
		regexp:["num1"],
		datatype:"enum",
		onerror:"邮箱验证码格式错误"
	});
};
findpwdEmailCls.prototype.attachEvent=function() {
	var self = this;

	$('#go_back').click(function(){
		window.history.back(); //返回上一页
	});

	$('#sendScode').click(function(){
		if ($.formValidator.pageIsValid('1') && self.sendEmailAjax()) {
			self.timeOut(self.time_out);
		}
	});
	
	$('#find_email').submit(function() {
		//表单提交前的检测
		return self.subFromAjax();
	});
	$('#submit_btn').click(function() {
		$('#find_email').submit();
	});
};

findpwdEmailCls.prototype.timeOut=function(times){
	var self = this;
	if(times > 0) {
		times--;
		$('#sendScode').val('重新发送(' + times + ')');
		$('#sendScode').attr('disabled',true).css('cursor', 'default');
		setTimeout(function(){
			self.timeOut(times);
		}, 1000);
	} else {
		$('#sendScode').val('获取验证码').attr('disabled', false).css('cursor', 'pointer');
		$('#sendScode_span').css('color','#999999').html('');
	}
	
};

findpwdEmailCls.prototype.sendEmailAjax=function(){
	var self = this;
	var username = $('#username').val();
	var email = $('#email').val();
	var passed = true;
	$.ajax({
		type:'post',
		url:'/Uc/Findpwd/sendEmailAjax',
		data:{
			'username':username,
			'email':email
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$('#emailTip').removeClass().addClass('onError').html(json.info);
				$('#sendScode_span').css('color','#999999').html('');
				passed = false;
			} else {
			
				$('#sendScode_span').css('color','#009900').html(json.info);
			}
		}
	});
	
	return passed;
};

//ajax 提交表单
findpwdEmailCls.prototype.subFromAjax=function(){
	var self = this;
	var username = $('#username').val();
	var email = $('#email').val();
	var scode = $('#scode').val();
	var passed = true;
	
	if (!$.formValidator.pageIsValid('1') || !$.formValidator.pageIsValid('2')) {
		return false;
	}
	$.ajax({
		type:'post',
		url:'/Uc/Findpwd/emailFromAjax',
		data:{
			'username':username,
			'email':email,
			'scode':scode
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				if (json.data.email_info) {
					$('#emailTip').removeClass().addClass('onError').html(json.data.email_info);
				}
				if(json.data.scode_info) {
					$('#scodeTip').removeClass().addClass('onError').html(json.data.scode_info);
				}
				passed = false;
			}
		}
	});
	
	return passed;
};

$(document).ready(function(){
	new findpwdEmailCls();
});