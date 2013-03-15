function modifyEmailTwoCls() {
	this.time_out = 60;
	this.attachEvent();
	this.checkInput();
};

modifyEmailTwoCls.prototype.checkInput=function() {
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
	
	$("#newemail").formValidator({
		validatorgroup:"1",
		onshow:"",
		onfocus:"请输入正确的邮箱",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:5,
		max:60,
		onerror:'邮箱长度为5-60'
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

modifyEmailTwoCls.prototype.attachEvent = function() {
	var self = this;
		
	$('#sendScode').click(function(){
		if ($.formValidator.pageIsValid('1') && self.modifyEmailSendScodeAjax()) {
			self.timeOut(self.time_out);
		}
	});
	
	$('#modify_email_step_2').submit(function() {
		//表单提交前的检测
		return $.formValidator.pageIsValid('1') && $.formValidator.pageIsValid('2') && self.modifyFromTwoAjax();
	});
	$('#submit_btn').click(function() {
		$('#modify_email_step_2').submit();
	});
};

modifyEmailTwoCls.prototype.timeOut=function(times){
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

modifyEmailTwoCls.prototype.modifyEmailSendScodeAjax=function(){
	var self = this;
	var newemail = $('#newemail').val();
	var oldemail = $('#oldemail').val();
	var passed = true;
	$.ajax({
		type:'post',
		url:'/Uc/Accountset/modifyEmailSendScodeAjax',
		data:{
			'newemail':newemail,
			'oldemail':oldemail
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$('#newemailTip').removeClass().addClass('onError').html(json.info);
				$('#sendScode_span').css('color','#999999').html('');
				passed = false;
			} else {
				$('#sendScode_span').css('color','#009900').html(json.info);
			}
		}
	});
	return passed;
};

modifyEmailTwoCls.prototype.modifyFromTwoAjax=function(){
	var self = this;
	var newemail = $('#newemail').val();
	var oldemail = $('#oldemail').val();
	var scode = $('#scode').val();
	var passed = true;
	$.ajax({
		type:'post',
		url:'/Uc/Accountset/modifyFromTwoAjax',
		data:{
			'newemail':newemail,
			'oldemail':oldemail,
			'scode':scode
		},
		
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				if (json.data.newwemail_span) {
					$('#newemailTip').removeClass().addClass('onError').html(json.data.newwemail_span);
					
				}
				if (json.data.oldemail_span) {
					$('#sendScode_span').css('color','#999999').html(json.data.oldemail_span);
				}
				if (json.data.scode_span) {
					$('#scodeTip').removeClass().addClass('onError').html(json.data.scode_span);
				}				
				passed = false;
			}
		}
	});
	
	return passed;
};

$(document).ready(function(){
	new modifyEmailTwoCls();
});


