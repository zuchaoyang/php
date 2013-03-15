var judgeHTML=Share.htmlFun.judgeHTML;
var address;
judgeHTML.successImg="/Public/local/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/images/error.gif";//错误的图片path

var domArr=[];
function winLoad(){
    address=new AddressCls();
    address.init();
    domArr=[{val:"e_mail"},{val:"phone"},
            {val:"password_1"},{val:"password_2"},{val:"address"}];
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
        e_mail:"请输入电子邮箱地址",
        phone:"请输入手机号",
        password_1:"请输入新密码",
        password_2:"请输入确认密码"
    };
    judgeHTML.errorInfo={
        address:"通讯地址过长",
        e_mail:"电子邮箱格式错误",
        phone:"电话格式错误",
        password_1:"密码只能是6-20个字母、数字",
        password_2:"确认密码和新密码输入不一致"
    };
    var reg=judgeHTML.reg;
    reg.e_mail=Share.regexProcess.regexEnum.email;
    reg.phone=Share.regexProcess.regexEnum.mobile;
    reg.password_1="^[0-9a-zA-Z]{6,20}$";
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
            {
                return "请输入通讯地址";
            }
            if(len>50)
            {
                return "error";
            }
            return "success";
        },
        password_2:function(id){
            var pwd=$("#password_1").val();
            var pwd_2=$("#"+id).val();
	    if(!Share.regexProcess.judge(reg.password_1, pwd_2))
	    {
		return "确认密码输入有误";
	    }
            if(pwd != pwd_2 || pwd_2.length<=0)
            {
                return "error";
            }
            return "success";
        }
    };
    srChange();
    addYear();
}

function addYear(){
    var d=new Date();
    var option, o=$("#entryYear");
    for(var i=1949,j=(d.getFullYear()-0);i<=j;i++)
    {
        option=$("<option value='"+i+"'>"+i+"</option>");
        o.append(option);
    }
    o.css("visibility", "hidden");
	o.css("visibility", "visible");
}
function srChange(){
    var date_s=$("#birthday").val();
    if(date_s == ""){
    	return false;
    }
    var sd=Share.dateProcess;
    var sxObj=sd.getSX_d(date_s);
    var astro=sd.getAstro_d(date_s);
    $("#psx").text(sxObj.show);
    $("#psx_inp").val(sxObj.value);
    $("#pAstro").text(astro.show);
    $("#pAstro_inp").val(astro.value);
}

$(document).ready(function() {
	winLoad();
	
	var url_append = $('#url_append').val();
	if(typeof url_append == 'undefined') {
		url_append = '';
	}
	$('#teachersubmit').submit(function() {
		var options = {
			type:'post',
			url:"/Homeuser/Activate/modifyAccount" + url_append,
			dataType:'json',
			beforeSubmit:function() {
				var rs = judgeHTML.all(domArr,true);
				if(rs) {
					$(':submit').attr('disabled', 'disabled');
					return true;
				} else {
					return false;
				}
			},
			success:function(json) {
				var err = json.error;
				alert(err.message);
				if(err.code > 0) {
					window.location.href = json.data.backurl;
				} else {
					$(':submit').attr('disabled', '');
				}
			}
		};
		$(this).ajaxSubmit(options);
		return false;
	});
});