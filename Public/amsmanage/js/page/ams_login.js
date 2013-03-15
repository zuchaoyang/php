function winLoad(){
        $("#pUserId").focus();
    }
function titErrInfo(str){
    $("#errInfo").text(str);
}
    /**
    *   判断用户名是否合法
    * paramter：
    *   userId：String(html用户名id)
    * return:boolean(true正确false错误)
    **/
    function isUserName(userId){
        var userName=Share.strProcess.trimLR($("#"+userId).val());
        var len=userName.length;
        if(len<3 || len>30)
	{
	    titErrInfo("用户名长度为3-30"); 
            return jud();
	}
        if(!Share.regexProcess.isUserName(userName))
	{
	    titErrInfo("用户名输入有误"); 
            return jud();
	}
        function jud(){
            //alert("用户名不存在");
            Share.sbf.focus(userId);
            return false;
        }
        return true;
    }
    /**
    * 判断密码是否合法
    * parameter：
    *   pwdId：String(html密码id)
    * return：boolean(true正确false错误)
    **/
    function isPassWord(pwdId)
    {
        //您输入的密码和账户名不匹配，请重新输入
        var len=$("#"+pwdId).val().length;
        if(len<6 || len>20)
        {
            titErrInfo("密码长度为6-20"); //alert("您输入的密码和账户名不匹配，请重新输入");
            Share.sbf.focus(pwdId);
            return false;
        }
        return true;
    }
    /**
    * 页面submit提交事件
    * return:boolean
    **/
    function pageSubmit(){
        //页面对应id
        var ph={
            userId:"pUserId",
            pwdId:"pPwdId"
        }
        if(!isUserName(ph.userId))
            return false;
        if(!isPassWord(ph.pwdId))
            return false;
        return true;
    }
    
    function pSubmit(){
    	if(login_param)
    	{
	        if(!pageSubmit())
	        {
	            return ;
	        }
	        login_param=false;
	        updLogin.loading();
	        
	        updLogin.loading();
	        $("#iform").submit();
    	}
    }
    var login_param=true;
    var updLogin={
		    login:function(){
		        var but=$("#submit_a");
		        but.text("登录");
		        but.css({color:"#000"});
		    },
		    loading:function(){
		        var but=$("#submit_a");
		        but.text("登录中");
		        but.css({color:"#666"});
		    }
		}