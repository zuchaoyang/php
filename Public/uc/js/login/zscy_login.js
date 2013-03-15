$(function() { 
	
	$('input:text:first').focus();
	
	$("input:text,textarea,input:password").focus(function() { 
	    $(this).addClass("cur_select"); 
	}); 
	$("input:text,textarea,input:password").blur(function() { 
	    $(this).removeClass("cur_select"); 
	}); 
	
	// 登录操作
	$("#login_btn").live('click',function() {
		
		var callback = $("#callback").val();
		var client_id = $("#client_id").val();
		var client_secret = $('#client_secret').val();		
		var username = $("#username").val().replace(/(^\s*)|(\s*$)/g, "");
		var password = $("#password").val().replace(/(^\s*)|(\s*$)/g, "");
		var app = $("#app").val();
		if(username == "") {
			makeTip('username', '请输入账号或者手机号', false);
			$("#username").focus();
			return false;
		}
		if(password == "") {
			makeTip('password', '请输入密码', false);
			$("#password").focus();
			return false;
		}
		

		password = $.md5(password);
		$.ajax({
			type: "POST",
			url: "/Uc/LoginApi/login",
			dataType: "json",
			data: {"grant_type":"password", "client_id":client_id, "username":username, "password":password, "callback":callback },
			success: function(json) {
				if(json.status == 1){
					var data = json.data; 
					if (data.callback) {
						window.location.href = data.callback;
					}
					return false;
				} else {
					makeTip('username', json.info, true);
					return false;
				}
			}
		});
	});
	
	 $("#addbookmark").click(function(e){
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
	
	
	

	/*
	 *  创建依附小提示框
	 *  param
	 *     obj:  页面元素id 名称，e.g     <input type="text" name="user"/>  则参数为  "user"
	 *     msg:  提示信息
	 *	   action: 是否立即展现
	 *  jquery 插件poshytip, 参考:http://vadikom.com/demos/poshytip/#
	 *  使用此方法必须在页面加入 css js,如下:
	 *  <link rel="stylesheet" type="text/css" href="{$smarty.const.IMG_SERVER}__PUBLIC__/css/tip-green/tip-green.css" />
	 *  <script type="text/javascript" src="{$smarty.const.IMG_SERVER}__PUBLIC__/js/plugins/jquery.poshytip.min.js"></script>
	 *  
	 *  author:  lnc
	 */
	 
	function makeTip(obj, msg, action) {

		if (obj) {
			$('#'+ obj).poshytip({
				content: msg,
				className: 'tip-green',
				showOn: 'focus',
				alignTo: 'target',
				alignX: 'right',
				alignY: 'center',
				offsetX: 10
			});

			if (action) {
				$('#'+ obj).poshytip('show');
			}
		}
	}
	
});