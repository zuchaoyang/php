var address;
var judgeHTML=Share.htmlFun.judgeHTML;
judgeHTML.successImg="/Public/local/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/images/error.gif";//错误的图片path
var domArr=[];
function winLoad(){
    address=new AddressCls();
    address.init();
    domArr=[{val:"phone"},{val:"address"},{val:"birthday"}];//{val:"e_mail"},
    var i,j;
    for(i=0,j=domArr.length;i<j;i++)
    {
        judgeHTML.addEvent(domArr[i].val);
    }
    for(i=1; i<4; i++)
    {
        $("#address_"+i).focus(function(){
            judgeHTML.focus("address");
        }).blur(function(){
            judgeHTML.blur("address");
        });
    }
    judgeHTML.remind={
        address:"请输入通讯地址",
        //e_mail:"请输入电子邮箱地址",
        phone:"请输入手机号",
        birthday:"请输入生日"
    };
    judgeHTML.errorInfo={
        address:"通讯地址过长",
        //e_mail:"电子邮箱格式错误",
        phone:"电话格式错误",
        birthday:"输入日期格式错误"
    };
    var reg=judgeHTML.reg;
    reg.e_mail=Share.regexProcess.regexEnum.email;
    reg.phone=Share.regexProcess.regexEnum.mobile;
    judgeHTML.custom={
        address:function(id){
            var len=Share.strProcess.trimLR($("#address").val()).length;
            /*
            for(var i=1;i<4;i++)
            {
                if(($("#address_"+i).val()+"")=="-1")
                    return "请输入通讯地址";
            }*/
            if(!address.validate())
            {
                return "请输入通讯地址";
            }
            if(len<=0)
                return "请输入通讯地址";
            if(len>50)
                return "error";
            return "success";
        },
        birthday:function(id){
            var val=Share.strProcess.trimLR($("#"+id).val());
            if(!Share.dateProcess.isDate(val, "yyyy-MM-dd"))
                return "error";
            return "success";
        }
    };
    srChange();
}
function srChange(){
    var date_s=$("#birthday").val();
    if(date_s.length>0){
        var sd=Share.dateProcess;
        var sxObj=sd.getSX_d(date_s);
        var astro=sd.getAstro_d(date_s);
        $("#psx").text(sxObj.show);
        $("#psx_inp").val(sxObj.value);
        $("#pAstro").text(astro.show);
        $("#pAstro_inp").val(astro.value);
    }
}
    /**
    * submit方法
    **/
    function submitFun(){
        if(!judgeHTML.all(domArr,true))
            return false;
        document.forms[0].action="/Homeuser/Infos/modifyUserInfo";
        document.forms[0].submit();
    }