function modify_parent_extend () {
	this.checkInput();
	this.save();
}

modify_parent_extend.prototype.checkInput = function() {
	$.formValidator.initConfig({
		formid:"parent_extend_form",
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
		min:6,
		max:50,
		onerror:"通讯地址在6~50个字"
	});
	
	$("#job_address").formValidator({
		empty:true,
		onshow:"请输入单位地址",
		onfocus:"单位可以为空",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:6,
		max:50,
		onerror:"单位在6~50个字"
	});
	
};

modify_parent_extend.prototype.save = function() {
	var self = this;
	$("#save").click(function(){
		$("#parent_extend_form").submit();
	});
};

$(document).ready(function(){
	var obj = new modify_parent_extend();
	obj.checkInput();
});