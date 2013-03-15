function checkUser() {
	this.tosubmit();
};
checkUser.prototype.checkInput = function() {
	var client_name = $("#cn").val().toString();
	var client_email = $("#yx").val().toString();
	var client_pwd = $("#client_pwd").val().toString();
	var re_client_pwd = $("#re_client_pwd").val().toString();
	
	if(client_name.length == 0){
		alert("用户名不能为空！");
		return false;
	}else if(client_name.length<5 || client_name.length>20) {
		alert("用户名不合法！");
		return false;
	} else if (!(/^([a-zA-Z0-9_-]|[.])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/.test(client_email))) {
		alert("邮箱格式不正确！");
		return false;
	} else if(client_pwd != re_client_pwd) {
		alert("两次输入的密码不一致！");
		return false;
	}else{
		return true;
	}
};

checkUser.prototype.tosubmit = function () {
	var self = this;
	$("#sqsubmit").unbind("click").bind("click",function(){
		var resault = self.checkInput();
		if(resault){
			$("#zdzh").submit();
		}
	});
};

$(document).ready(function() {
	new checkUser();
});