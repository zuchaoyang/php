function ucLoginCls() {
	this.default_var = "用户名/邮箱/手机号";
	this.init();
}

ucLoginCls.prototype.init = function() {
	this.attachEvent();



};

ucLoginCls.prototype.attachEvent=function() {
	var self = this;


	$('#username').focus(function() {

		if(this.value == self.default_var) {
			this.value = "";
		}
		$("#errorMessage").html('&nbsp;');
	}).blur(function() {
		if(!this.value) {
			this.value = self.default_var;
		}
		self.doShowCaptcha("/Uc/LoginApi/show_captcha");
	}).val(self.default_var);

	$("#login_btn").bind('click', function() {
		if (self.validator()) {
			self.doLogin();
		}
	});

	$('#password').focus(function() {
		$("#errorMessage").html('&nbsp;');
	});

	$("#change_captcha").bind('click', function(event) {
		event.preventDefault();
		self.doShowCaptcha("/Uc/LoginApi/refresh_captcha");
	});

	$("#addbookmark").bind('click', function(e) {
		 var ctrl = (navigator.userAgent.toLowerCase()).indexOf('mac') != -1 ? 'Command/Cmd': 'CTRL';
         if (document.all) {
             window.external.addFavorite('http://home.wmw.cn', '我们网');
         } else if (window.sidebar) {
             window.sidebar.addPanel('我们网', 'http://home.wmw.cn', "");
         } else {
             alert('您可以尝试通过快捷键' + ctrl + ' + D 加入到收藏夹~');
         }
         return false;
	});

	$("#qq_login_btn").bind('click', function() {
		window.location.href = '/Uc/Oauth2/login?connect=qzone';
	});

    $(document).keydown(function(e){
        var e = e || event;
	    var keyPress = e.keyCode || e.whick || e.charCode;
	    if (Number(keyPress) == 13) {
	    	$("#login_btn").trigger("click");
	    }
    });
};

ucLoginCls.prototype.validator = function() {
	var self = this;
	var username = $("#username").val().replace(/(^\s*)|(\s*$)/g, "");
	var password = $("#password").val().replace(/(^\s*)|(\s*$)/g, "");

	var app = $("#app").val();
	if(username == "" || username == self.default_var) {
		this.makeTip('请填写账号');
//		$("#username").focus();
		return false;
	}
	if(password == "") {
		this.makeTip('请填写密码');
//		$("#password").focus();
		return false;
	}

	// 验证验证码
	var show_captcha = $("#captcha").css('display');

	var captcha_input_code = $("#captcha_input_code").val();
	if (show_captcha == "block") {

		if (captcha_input_code == "") {
			this.makeTip('请填写验证码');
			$("#captcha_input_code").focus();
			return false;
		}

	}

	return true;
};

ucLoginCls.prototype.doLogin = function() {
	var self = this;
	var callback = $("#callback").val();
	var client_id = $("#client_id").val();
	var client_secret = $('#client_secret').val();
	var username = $("#username").val().replace(/(^\s*)|(\s*$)/g, "");
	var password = $("#password").val().replace(/(^\s*)|(\s*$)/g, "");

	var social_account = $("#social_account").val();
	var social_type = $("#social_type").val();
	var access_token = $("#access_token").val();

	var app = $("#app").val();

	// 验证验证码
	var show_captcha = $("#captcha").css('display');

	var captcha_input_code = $("#captcha_input_code").val();
	if (show_captcha == "none") {
		captcha_input_code = "";
	}

	password = $.md5(password);
	$.ajax({
		type: "POST",
		url: "/Uc/LoginApi/login",
		dataType: "json",
		data: {"grant_type":"password", "client_id":client_id, "username":username, "password":password,
			   "callback":callback, "captcha":captcha_input_code, "social_account": social_account, "social_type":social_type,
			   "access_token":access_token},
		success: function(json) {
			if(json.status == 1){
				var data = json.data;
				if (data.callback) {
					window.location.href = data.callback;
				}
				return false;
			} else {
				if (show_captcha == "block") {
					self.doShowCaptcha("/Uc/LoginApi/refresh_captcha");
				} else {
					self.doShowCaptcha("/Uc/LoginApi/show_captcha");
				}
				self.makeTip(json.info);
				return false;
			}
		}
	});

};

ucLoginCls.prototype.doShowCaptcha = function(url) {
	if (url == "") {
		return false;
	}

	var username = $("#username").val().replace(/(^\s*)|(\s*$)/g, "");

	var show_captcha = $("#captcha").css('display');

	$.ajax({
		type: "POST",
		url: url,
		dataType: "json",
		data: {"username":username},
		success: function(json) {
			if(json.status == 1){

				var data = json.data;
				var img_src = data.image_src;
				var captcha_code = data.code;

				$("#img_captcha").attr('src', img_src);
				$("#captcha_input_code").val("");
				$("#captcha").css('display',"block");

			} else if (json.status == 0) {
				$("#captcha").css('display',"none");
			}
		}
	});
};

ucLoginCls.prototype.makeTip = function(msg) {
	$("#errorMessage").html(msg);
//	$("#errorMessage").css('display',"block");
};

$(document).ready(function() {
	new ucLoginCls();
});
