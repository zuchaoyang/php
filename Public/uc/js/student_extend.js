function modify_student_extend () {
	this.checkInput();
	this.save();
}

modify_student_extend.prototype.checkInput = function() {
	$.formValidator.initConfig({
		formid:"student_extend_form",
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist){

	}
	});

	$("#client_address").formValidator({
		empty:true,
		onshow:"请输入通讯地址 ",
		onfocus:"通讯地址可以为空",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:3,
		max:50,
		onerror:"通讯地址在6~50个字"
	});
	
};

modify_student_extend.prototype.save = function() {
	var self = this;
	$("#save").click(function(){
		$("#student_extend_form").submit();
	});
};

$(document).ready(function(){
	var obj = new modify_student_extend();
	obj.checkInput();
});