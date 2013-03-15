function findpwdPhoneCls(){
    this.time_out = 60;
	this.attachEvent();
	this.checkInput();
}

findpwdPhoneCls.prototype.checkInput=function() {
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
	
	$("#phone").formValidator({
		validatorgroup:"1",
		onshow:"",
		onfocus:"请输入正确的手机号",
		oncorrect:"&nbsp;"
	}).regexValidator({
		regexp:["mobile"],
		datatype:"enum",
		onerror:"手机号码格式错误"
	});
	
	$("#scode").formValidator({
		validatorgroup:"2",
		onshow:"请输入正确的手机验证码",
		onfocus:"手机验证码为六位数字",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,
		max:6,
		onerror:'手机验证码格式错误'
	}).regexValidator({
		regexp:["num1"],
		datatype:"enum",
		onerror:"手机验证码格式错误"
	});
};
findpwdPhoneCls.prototype.attachEvent=function() {
	var self = this;

	$('#go_back').click(function(){
		window.history.back(); //返回上一页
	});
	
	$('#sendScode').click(function(){
		if ($.formValidator.pageIsValid('1') && self.sendPhoneAjax()) {
			self.timeOut(self.time_out);
		}
	});
	
	$('#find_phone').submit(function() {
		//表单提交前的检测
		return self.subFromAjax();
	});
	$('#submit_btn').click(function() {
		$('#find_phone').submit();
	});
};

findpwdPhoneCls.prototype.timeOut=function(times){
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

findpwdPhoneCls.prototype.sendPhoneAjax=function(){
	var self = this;
	var username = $('#username').val();
	var phone = $('#phone').val();
	var passed = true;

	$.ajax({
		type:'post',
		url:'/Uc/Findpwd/sendPhoneAjax',
		data:{
			'username':username,
			'phone':phone
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$('#phoneTip').removeClass().addClass('onError').html(json.info);
				$('#sendScode_span').css('color','#999999').html('');
				passed = false;
			} else{
				$('#sendScode_span').css('color','#009900').html(json.info);
			}
		}
	});
	return passed;
};

//ajax 提交表单
findpwdPhoneCls.prototype.subFromAjax=function(){
	var self = this;
	var username = $('#username').val();
	var phone = $('#phone').val();
	var scode = $('#scode').val();
	var passed = true;
	
	if (!$.formValidator.pageIsValid('1') || !$.formValidator.pageIsValid('2')) {
		return false;
	}
	$.ajax({
		type:'post',
		url:'/Uc/Findpwd/phoneFromAjax',
		data:{
			'username':username,
			'phone':phone,
			'scode':scode
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				if (json.data.phone_info) {
					$('#phoneTip').removeClass().addClass('onError').html(json.data.phone_info);
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
	new findpwdPhoneCls();
});