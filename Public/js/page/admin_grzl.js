var address;
var judgeHTML=Share.htmlFun.judgeHTML;
judgeHTML.successImg="/Public/local/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/images/error.gif";//错误的图片path
var domArr=[];
function winLoad(){
    address=new AddressCls();
    address.init();
    domArr=[{val:"email"},{val:"phone"},{val:"address"},{val:"birthday"}];
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
        email:"请输入电子邮箱地址",
        phone:"请输入手机号",
        birthday:"请输入生日"
    };
    judgeHTML.errorInfo={
        address:"通讯地址过长",
        email:"电子邮箱格式错误",
        phone:"电话格式错误",
        birthday:"输入日期格式错误"
    };
    var reg=judgeHTML.reg;
    reg.email=Share.regexProcess.regexEnum.email;
    reg.phone=Share.regexProcess.regexEnum.mobile;
    judgeHTML.custom={
        address:function(id){
	        if(!address.validate())
	        {
	            return "请输入通讯地址";
	        }
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
    function submitFun(client_type){
    	if(client_type == ""){
    		client_type = 0;
    	}
        if(!judgeHTML.all(domArr,true))
            return false;
        alert("成功");
        document.forms[0].action="/Adminuser/User/account_update_do/client_type/"+client_type;
        document.forms[0].submit();
    }