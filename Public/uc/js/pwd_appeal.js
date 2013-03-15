function appeal() {
	this.submit();
};

appeal.prototype.checkInput = function() {
	$.formValidator.initConfig({formid:"appeal",debug:false,submitonce:true,onerror:function(msg,obj,errorlist){}
	});
	$("#username").formValidator({
		onshow:"请输入姓名",
		onfocus:"请输入您的真实姓名",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:2,
		max:20,
		onerror:"长度在2——20之间"
	});
	
	$("#uid").formValidator({
		empty:true,
		onshow:"请输入账号",
		onfocus:"如果忘记可以不填写，账号可以是邮箱、账号或手机号",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:5,
		max:60,
		onerror:"账号长度为5-60"
	}).regexValidator({
		regexp:["mobile","email","num1"],
		datatype:"enum",
		onerror:"账号只能是邮箱、账号或手机号"
	});
	
	$("#phone").formValidator({
		onshow:"请输入手机号或电话",
		onfocus:"联系电话不能为空，格式：0577-88888888或11位手机号码",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:11,
		max:13,
		onerror:"手机号必须是11位,电话介于12~13位(包括'-')"
	}).regexValidator({
		regexp:["mobile","tel"],
		datatype:"enum",
		onerror:"您输入的手机号或电话格式错误"
	}).keyup(function() {
		//根据用户输入的格式动态改变长度的限制
		var phone = this.value.toString() || '';
		$(this).attr('maxlength', phone.indexOf('-') >= 0 ? 13 : 11);
		var maxlength = $(this).attr('maxlength');
		if(phone.length > maxlength) {
			this.value = phone.substring(0, maxlength);
		}
	});
	$("#email").formValidator({
		empty:true,
		onshow:"请输入邮箱",
		onfocus:"请填写您常用的邮箱",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:5,
		max:60,
		onerror:"邮箱长度5-60"
	}).regexValidator({
		regexp:"email",
		datatype:"enum",
		onerror:"邮箱格式错误"
	});
	
	$("#school_name").formValidator({
		onshow:"请输入学校名称",
		onfocus:"请填写您的学校名称",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:4,
		max:60,
		onerror:"学校名称长度为4到60位"
	});
	
	$("#class_name").formValidator({
		onshow:"请输入班级名称",
		onfocus:"请正确填写您的班级名称",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:4,
		max:20,
		onerror:"班级名称长度为4到20位"
	});
	
	$("#problem_description").formValidator({
		onshow:"请输入您的问题描述",
		onfocus:"请输入您的问题描述",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:1,
		max:100,
		onerror:"问题描述在100字以内"
	});
};

appeal.prototype.submit = function() {
	$("#commit_appeal").bind('click',function(){
		$("#appeal").submit();
	});
};

$(document).ready(function(){
	var obj = new appeal();
	obj.checkInput();
});