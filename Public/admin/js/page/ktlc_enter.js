var judgeHTML=Share.htmlFun.judgeHTML;
var address;
judgeHTML.successImg="/Public/local/basemanage/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/basemanage/images/error.gif";//错误的图片path

var domArr;
function winLoad()
{
    address=new AddressCls();
    address.init();//schoolAddress_Content
    for(i=1; i<4; i++)
    {
        $("#address_"+i).focus(function(){
            judgeHTML.focus("schoolAddress_Content");
        }).blur(function(){
            judgeHTML.blur("schoolAddress_Content");
        });
    }
    domArr=[{val:"schoolName"},{val:"schoolAddress_Content"},{val:"zipCode"},{val:"createSchoolDate"},
                {val:"principal"},{val:"contact"},{val:"classNum"},{val:"teachNum"},
                {val:"studentNum"},{val:"personInCharge"},{val:"PICcontact"},{val:"setMail"},
                /*{val:"oldWebUrl"},*/{val:"scanFile"}];
    for(var i=0,j=domArr.length;i<j;i++)
    {
        if(domArr[i].val == "scanFile" || domArr[i].val == "createSchoolDate")
            judgeHTML.addEvent(domArr[i].val, domArr[i].val, true);
        else
            judgeHTML.addEvent(domArr[i].val);
    }
    judgeHTML.remind={
        schoolName:"请输入学校名称",
        schoolAddress_Content:"请输入学校地址",
        zipCode:"请输入邮政编码",
        createSchoolDate:"请输入建立年月",
        principal:"请输入校长",
        contact:"请输入联系方式",
        classNum:"请输入班级数量",
        teachNum:"请输入教师人数",
        studentNum:"请输入学生人数",
        personInCharge:"请输入姓名",
        PICcontact:"请输入联系方式",
        setMail:"请输入邮箱设置",
        //oldWebUrl:"请输入原校园网站",
        scanFile:"请选择图片"
    };
    judgeHTML.errorInfo={
        zipCode:"邮政编码格式错误",
        createSchoolDate:"建校年月格式输入错误",
        principal:"校长名输入错误",
        contact:"联系方式输入错误",
        classNum:"只能输入数字",
        teachNum:"只能输入数字",
        studentNum:"只能输入数字",
        personInCharge:"格式输入错误",
        PICcontact:"格式输入错误",
        setMail:"格式输入错误",
        //oldWebUrl:"格式输入错误",
        scanFile:"扫描件后缀名只能为\".jpeg、.jpg、.gif、.bmp和.png\",请重新选择"
    };
    var reg=judgeHTML.reg;
    reg.zipCode=Share.regexProcess.regexEnum.zipcode;
    reg.classNum=Share.regexProcess.regexEnum.intege1;
    reg.teachNum=Share.regexProcess.regexEnum.intege1;
    reg.studentNum=Share.regexProcess.regexEnum.intege1;
    reg.setMail=Share.regexProcess.regexEnum.email;
    reg.oldWebUrl=Share.regexProcess.regexEnum.url;
    judgeHTML.custom={
	    schoolName:function(id){
	        var schoolNameVal = Share.strProcess.trimLR( $("#"+id).val() );
		    if(schoolNameVal.length<2||schoolNameVal.length>20)
                return "学校名称长度为2-20个字符";
            var schoolName_Exp="^[a-zA-Z\\u4E00-\\u9FA5\\uF900-\\uFA2D]+$";
            if(!Share.regexProcess.judge(schoolName_Exp, schoolNameVal))
                return "学校名称输入有误";
            return "success";
	    },
	    schoolAddress_Content:function(id){
	        var saContentVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(!address.validate())
            {
                return "请输入通讯地址";
            }
	        if(saContentVal.length<2||saContentVal.length>50)
                return "学校地址长度为2-50个字符";
            if(saContentVal=="乡村\街道\具体门牌号")
                return "请填写乡村\街道\具体门牌号";
            var sap=$("#schoolAddress_Province").val();//学校地址
            var sac=$("#schoolAddress_City").val();
            var saa=$("#schoolAddress_Area").val();
            if(sap==-1 || sac==-1 || saa==-1)
                return "请选择省市";
            return "success";
	    },
	    createSchoolDate:function(id){
	        var csd = Share.strProcess.trimLR( $("#"+id).val() );
	        if(!Share.dateProcess.isDate(csd, "yyyy-MM-dd"))
                return "error";
            return "success"; 
	    },
	    principal:function(id){
	        var principalVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(principalVal.length<2||principalVal.length>30)
                return "error";
            return "success"; 
	    },
	    contact:function(id){
	        var contactVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(!(contactVal.length>=8&&contactVal.length<=11&&Share.regexProcess.isNum1(contactVal)))
                return "error";
            return "success"; 
	    },
	    personInCharge:function(id){
	        var personInChargeVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(personInChargeVal.length<2||personInChargeVal.length>30)
                return "error";
            return "success"; 
	    },
	    PICcontact:function(id){
	        var PICcontactVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(!(PICcontactVal.length>=8&&PICcontactVal.length<=11&&Share.regexProcess.isNum1(PICcontactVal)))
                return "error";
            return "success"; 
	    },
	    scanFile:function(id){
	        var scanFileVal = Share.strProcess.trimLR( $("#"+id).val() );
	        if(!Share.sbf.judgeImgType(scanFileVal))
	            return "error";
            return "success"; 
	    }
    };
}

var dataProcess={
    betect_b:null,//记录检测成功与否
    validateUrl:function(){
        var newWebUrlVal=Share.strProcess.trimLR($("#newWebUrl").val());//新网址申请 
        var _newUrl = newWebUrlVal;
        
        if(newWebUrlVal.length > 0)
        {
            newWebUrlVal+=".wmw.cn";
            if(!Share.regexProcess.isUrl_2(newWebUrlVal)){
                this.betect_b=false;
                this.setHtml("新网站申请格式输入错误");
                return ;
            }
            this.wait();
            //demo star
            //this.ok("", true);
            //demo end
            
            var param={};
            param.newWebUrl=_newUrl;
            param.schoolid = Share.strProcess.trimLR($("#schoolid").val());
            $.ajax({
                type:"post",
                data:param,
                dataType:"json",
                url:"/Admingroup/Schoolmanage/checkNewUrl",
                success:function(data){//alert(data);
            		var d = data.result;
                    if(d.code>0)
                    {
                        //成功
                        dataProcess.ok("", true);
                    }
                    else
                    {
                        //失败
                        switch(d.code)
                        {
                            case -1:
                            	dataProcess.ok("已存在网址,请更改后重试", false);
                                break;
                        }
                    }
                }
            });
        }
        else
        {
            Share.sbf.focus("newWebUrl");
            return ;
        }
    },
    wait:function(){
        var but=$("#validate_but");
        but.attr("disabled", "disabled");
        $("#newWebUrl").attr("disabled", "disabled");
        $("#newWebUrl_err").text("");
        $("#validateLoading_img").show();
    },
    ok:function(info, tf){
        this.betect_b=tf;
        $("#validateLoading_img").hide();
        this.setHtml(info);
        $("#newWebUrl").attr("disabled", "");
        $("#validate_but").attr("disabled", "");
    },
    onfocus:function(){
        var span=$("#newWebUrl_err");
        span.text("请输入新网址");
    },
    onblur:function(){
        var len=Share.strProcess.trimLR($("#newWebUrl").val()).length;
        if(len>0)
        {
            if(this.betect_b==null)
            {
                $("#newWebUrl_err").text("请检测网址");
            }
            else 
            {
                this.setHtml("网址格式错误");
            }
         }
         else
            $("#newWebUrl_err").text("");
    },
    onchange:function(){
        this.betect_b=null;
        $("#newWebUrl_err").text("请输入网址");
    },
    setHtml:function(errInfo){
        if(this.betect_b)
        {
            $("#newWebUrl_err").html("<img src='"+judgeHTML.successImg+"'>");
        }
        else
        {
            $("#newWebUrl_err").html("<img src='"+judgeHTML.errorImg+"'>"+errInfo);
        }
    }
};