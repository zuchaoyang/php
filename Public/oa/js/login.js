	$(function(){
		$("#loginuser").focus(function(){
			var text_val=$(this).val();
		    if(text_val==this.defaultValue){
			  $(this).val("");
		    }
		});
		$("#loginpwd").focus(function(){
			var text_val=$(this).val();
		    if(text_val==this.defaultValue){
			  $(this).val("");
		    }
		});
		$("#loginuser").blur(function(){
			var text_val=$(this).val();
			if(text_val==""){
				$(this).val(this.defaultValue);
			}
		});
		$("#loginpwd").blur(function(){
			var text_val=$(this).val();
			if(text_val==""){
				$(this).val(this.defaultValue);
			}
		});
	})
	function checkinfo(){
		var checkflag = true;
		if(($("#loginuser").val()).replace(/(^\s*)|(\s*$)/g, "") == "" || ($("#loginpwd").val()).replace(/(^\s*)|(\s*$)/g, "") == ""){
			$("#error_login").css("color","#FF0000").html("请您完整输入账号和密码");
			checkflag = false;
		}
		return checkflag;
	}
	
	function loginsubmit() {
		if (checkinfo()) {
			updLogin.loading();
            var h = window.SSOController;
            h.entry = 'oa';
            h.login($("#loginuser").val(), $("#loginpwd").val(),0)
                h.customLoginCallBack = function(j) {
                    if (!parseInt(j.code)) {
                        h.customLoginCallBack = function() {
                        };
                        succ = function(result) {
                            if ($('#url').val()) {
                               location.replace($('#url').val()); 
                            } else {
                               location.replace(result.data.backurl); 
                            }
                        };
                        succ(j);
                    } else {
                        $('#error_login').html(j.message);
                        updLogin.login();
                    }
                    h.customLoginCallBack = function() {
                    };
                    h = null;
                };
		}
	}

	  document.onkeydown = function Enter(e) { 
        var e = e || event; 
        var keyPress = e.keyCode || e.whick || e.charCode; 
        if (Number(keyPress) == 13) { 
        	loginsubmit();  //调用登录按钮的登录事件
         }
      };
	var updLogin={
		    login:function(){
		        var but=document.getElementById("ploginBut");
		        but.value="登录";
		        but.setAttribute("readonly", "");
		        but.className="login_btn";
		    },
		    loading:function(){
		        var but=document.getElementById("ploginBut");
		        but.value="";
		        but.setAttribute("readonly", "readonly");
		        but.className="login_btn_loading";
		    }
		}

	