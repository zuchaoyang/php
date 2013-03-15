function modify_phone () {
	this.checkInput();
	this.save();
}

modify_phone.prototype.checkInput = function() {
	$.formValidator.initConfig({
		formid:"user_contact",
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist){

		}
	});
	$("#phone_id").formValidator({
		onshow:"请输入手机号或电话",
		onfocus:"联系电话不能为空，格式：0577-88888888或11位手机号码",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:11,
		max:13,
		onerror:"手机号必须是11位,电话介于12~13位(包括'-')"
	}).regexValidator({
		regexp:["mobile", "tel"],
		datatype:"enum",
		onerror:"您输入的手机号或电话格式错误"
	}).compareValidator({
		desid:"sub_phone",
		operateor:"!=",
		onerror:"您没有修改任何内容"
	}).keyup(function() {
		//根据用户输入的格式动态改变长度的限制
		var phone = this.value.toString() || '';
		$(this).attr('maxlength', phone.indexOf('-') >= 0 ? 13 : 11);
		var maxlength = $(this).attr('maxlength');
		if(phone.length > maxlength) {
			this.value = phone.substring(0, maxlength);
		}
	});
};
modify_phone.prototype.save = function() {
	var self = this;
	$("#save").click(function(){
		$("#user_contact").submit();
	});
};

$(document).ready(function(){
	var obj = new modify_phone();
	obj.checkInput();
});

