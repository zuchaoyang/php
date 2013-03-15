function checkAmsUser() {
	this.client_name = $("#client_name").val().toString();
	this.client_email = $("#client_email").val().toString();
	this.client_pwd = $("#client_pwd").val().toString();
	this.re_client_pwd = $("#re_client_pwd").val().toString();
	this.tosubmit();
};
checkAmsUser.prototype.checkInput = function() {
	var self = this;
	if(self.client_name.length == 0){
		alert("用户名不能为空！");
		return false;
	}else if(self.client_name.length<5 || self.client_name.length>20) {
		alert("用户名不合法！");
		return false;
	} else if (!(/^([a-zA-Z0-9_-]|[.])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/.test(self.client_email))) {
		alert("邮箱格式不正确！");
		return false;
	} else if(self.client_pwd != self.re_client_pwd) {
		alert("两次输入的密码不一致！");
		return false;
	}else{
		return true;
	}
};

checkAmsUser.prototype.tosubmit = function () {
	var self = this;
	$("#amsuser").unbind("click").bind("click",function(){
		var resault = self.checkInput();
		if(resault){
			var param = {};
			param.client_name = $("#client_name").val().toString();
			param.client_email = $("#client_email").val().toString();
			param.client_pwd = $("#client_pwd").val().toString();
			param.re_client_pwd = $("#re_client_pwd").val().toString();
			
			$.ajax({
				type:"POST", //ajax提交的方式，有post和get两种
				url:"/Amscontrol/Amsaccountmanage/modifyAmsUserInfo", //ajax请求的页面
				dataType:"json",//ajax用什么方式返回，html以html代码的形式返回，text是以文本的形式返回
				data:param,
				success:function(data){ //当ajax调用成功后返回，返回的数据存在data中
	                  alert(data.message);
	            }
			});
		}
	});
};

$(document).ready(function() {
	new checkAmsUser();
});