function winLoad(){
        $("#pUserId").focus();
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
            return jud();
        if(!Share.regexProcess.isUserName(userName))
            return jud();
        function jud(){
            alert("用户名不存在");
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
            alert("您输入的密码和账户名不匹配，请重新输入");
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
    
    function abc(){
        if(!pageSubmit())
        {
            return ;
        }
        $("#myform").submit();  //myform:form标签id
    }