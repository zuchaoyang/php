var judgeHTML = Share.htmlFun.judgeHTML;
judgeHTML.successImg="/Public/local/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/images/error.gif";//错误的图片path
var domArr=[];
function winLoad(){
    addYear();
    domArr=[{val:"workName"}];
    var i,j;
    for(i=0,j=domArr.length;i<j;i++)
    {
        judgeHTML.addEvent(domArr[i].val);
    }
    //workName
    judgeHTML.remind={
        workName:"请输入单位名称"
    };
    judgeHTML.errorInfo={
        workName:"单位名称过长"
    };
    judgeHTML.custom={
        workName:function(id){
            var len=Share.strProcess.trimLR($("#"+id).val()).length;
            if(len<=0)
                return "请输入单位名称";
            if(len>50)
                return "error";
            return "success";
        }
    };
}

function addYear(){
    var d=new Date();
    var option, select, o=$("#entryYear");
    for(var i=1949,j=(d.getFullYear()-0);i<=j;i++)
    {
        select=i==workYear?"selected='selected'":"";
        option=$("<option value='"+i+"' "+ select +">"+i+"</option>");
        o.append(option);
    }
    o.css("visibility", "hidden");
	o.css("visibility", "visible");
	$("#entryMonth").val(workMonth);
}
//学生老师家长提交
function teacher(){
    $("#form").attr("action", "/Homeuser/Infos/modifyUserstretch");
    $("#form").submit();
}
function parent(){
    if(!judgeHTML.all(domArr,true))
        return false;
	$("#form").attr("action", "/Homeuser/Infos/modifyUserparent");
    $("#form").submit();
}
function student(){
	$("#form").attr("action","/Homeuser/Infos/modifyUserstudent");
	$("#form").submit();
}