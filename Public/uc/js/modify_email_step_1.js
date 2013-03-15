function modifyEmailOneCls() {

	this.attachEvent();
	this.checkInput();
};

modifyEmailOneCls.prototype.checkInput=function() {
	var self = this;
	$.formValidator.initConfig({
		debug:false,
		submitonce:false,
		errorfocus:false,
		onerror:function(msg,obj,errorlist){
		}
	});
	$("#oldemail").formValidator({
		onshow:"",
		onfocus:"请输入已经设置的邮箱",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:5,
		max:60,
		onerror:'邮箱长度为5-60'
	}).regexValidator({
		regexp:["email"],
		datatype:"enum",
		onerror:"邮箱格式不正确"
	});
};

modifyEmailOneCls.prototype.attachEvent = function() {
	var self = this;
	$('#modify_email_step_1').submit(function() {
		//表单提交前的检测
		return $.formValidator.pageIsValid('1') && self.modifyEmailOneAjax();
	});
	$('#submit_btn').click(function() {
		$('#modify_email_step_1').submit();
	});
	
};

modifyEmailOneCls.prototype.modifyEmailOneAjax=function(){
	var self = this;
	var oldemail = $('#oldemail').val();
	var passed = true;
	$.ajax({
		type:'post',
		url:'/Uc/Accountset/modifyEmailOneAjax',
		data:{
			'oldemail':oldemail
		},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$('#oldemailTip').removeClass().addClass('onError').html(json.info);
				passed =  false;
			}
		}
	});
	
	return passed;
};

$(document).ready(function(){
	new modifyEmailOneCls();
});


