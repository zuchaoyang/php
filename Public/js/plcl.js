var judgeHTML=Share.htmlFun.judgeHTML;

judgeHTML.successImg="/Public/local/amsmanage/images/success.gif";//成功的图片path
judgeHTML.errorImg="/Public/local/amsmanage/images/error.gif";//错误的图片path

function winLoad()
{
    $(".kctx").focus(function(){
        judgeHTML.focus($(this).attr("id"), "st");
    });
    $(".kctx").blur(function(){
        judgeHTML.blur($(this).attr("id"), "st");
    });
    $("#student_table_input_1").focus();
}
    /**
    * 确定输入用户信息
    **/
    function confirmData(){
        var tf=true;
        var dataArr=[];
        var arr=[];
        arr[0]={
            type:"name",
            val:"ap",                   //php人员需要修改成学生姓名input的name值
            reg:"st"
        };
        if(!judgeHTML.all(arr))
            return ;
        $("#student_table input").each(function(i, o){
            var obj=$(o);
            var id=obj.attr("id");
            var val=Share.strProcess.trimLR(obj.val());
            //出现的效果不同
            if(val.length>0)
                dataArr[dataArr.length]=val;
            if(!validateStudentName(id))
            {
                tf=false;
                return false;
            }
        });
        if(dataArr.length==0)
        {
            alert("请输入学生姓名");
            $("#student_table_input_1").focus();
        }
        else if(tf && dataArr.length>0)
        {
            var table=new Share.htmlFun.TableCls();
            table.init("studShow_table", "");
            table.clear();
            var tr, tdArr=[];
            for(var i=0,j=dataArr.length;i<j;i++)
            {
                tr=$("<tr></tr>");
                tdArr[0]=$('<td width="22%" height="30" align="center" bgcolor="#CCCCCC">'+ (i+1) +'</td>');
                tdArr[1]=$('<td width="78%" height="30" align="center" bgcolor="#CCCCCC"></td>');
                tdArr[1].text(dataArr[i]);
                table.appendTbody(tr, tdArr, true);
            }
            //table.createEnd();
            $("#pInputData").hide();
            $("#pShowData").show();
        }
    }
    /**
    * 返回录入
    **/
    function backFun(){
        $("#pShowData").hide();
        $("#pInputData").show();
    }
    
/**
*   判断学生姓名是否合法(允许中英文 + 空格，不允许特殊字符。长度限制30个字符)
* paramter：
*   id：String(dom id)
* return:boolean(true正确[为空直接返回true]false错误)
**/
function validateStudentName(id)
{
    var val=Share.strProcess.trimLR($("#"+id).val());
    var len=val.length;
    if(len > 0)
    {
        if(len<2 || len>30)
            return jud();
        var s=Share.regexProcess;
        if(!s.isUserName_2(val))
            return jud();
    }
    function jud()
    {
        /*$("#"+id+"_err").text("姓名输入有误"); //alert("姓名输入有误，请重新输入");
        Share.sbf.focus(id);*/
        return false;
    }
    return true;
}    
var studentTable={
    name:"nameValue",
    addStudent:function(){
        var trObj = $("#student_table tr:last-child");
        var len = $("#student_table tr").length;
        var table=new Share.htmlFun.TableCls();
        table.init("student_table", "");
        var tr, tdArr=[], id;
        tr=$("<tr id='student_table_"+ len +"'></tr>");
        tdArr[0]=$('<td height="30" align="center" bgcolor="#dfdbdb">'+len+'</td>');
        id = 'student_table_input_'+len;
        tdArr[1]=$('<td height="30" align="center" bgcolor="#dfdbdb"><input id="'+id+'" name="'+this.name+'" type="text" class="kctx" onfocus="judgeHTML.focus(\''+ id +'\', \'st\');" onblur="judgeHTML.blur(\''+ id +'\', \'st\');"/></td>');
        tdArr[2]=$('<td height="30" align="left"><span id="'+id+'_err" style="color:red;size:15px;"></span></td>');
        table.appendTbody(tr, tdArr, true);    
        $("#student_table_input_"+len).focus();
    },
    cutStudent:function(){
        var len = $("#student_table tr").length;
        if(len > 41)
        {
            if(window.confirm("确定删除序号"+(len-1)+"的数据？"))
            {
                $("#student_table tr:last-child").remove();
                $("#student_table_input_"+(len-2)).focus();
            }
        }
        else
        {
            alert("最少保持在40行数据");
        }
    }
}