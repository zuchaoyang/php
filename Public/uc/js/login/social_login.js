function socialLoginCls() {
	this.default_var = "用户名/邮箱/手机号";
	this.init();
}

socialLoginCls.prototype.init = function() {
	this.doLogin();
};

socialLoginCls.prototype.doLogin = function() {
	var self = this;
	var callback = $("#callback").val();
	var client_id = $("#client_id").val();
	var client_secret = $('#client_secret').val();		
	var username = $("#username").val().replace(/(^\s*)|(\s*$)/g, "");
	
	
	var social_account = $("#social_account").val();
	var social_type = $("#social_type").val();		
	var access_token = $("#access_token").val();
	
	var app = $("#app").val();
	
	$.ajax({
		type: "POST",
		url: "/Uc/LoginApi/social_login",
		dataType: "json",
		data: {"grant_type":"password", "client_id":client_id, "username":username,  
			   "callback":callback, "social_account": social_account, "social_type":social_type, "access_token": access_token},
		success: function(json) {
			if(json.status == 1){
				var data = json.data; 
				if (data.callback) {
					window.location.href = data.callback;
				}
				return false;
			} else {
				window.location.href = 'http://home.wmw.cn';
			}
		}
	});	
};

$(document).ready(function() {
	new socialLoginCls();
});
