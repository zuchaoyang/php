/**
* 判断课程名是否合法(实用“新增”和“修改”)
* parameter：
*   id：dom id
*   tit:错误提示位置dom id
* return:boolean(true正确)
**/
function validateCourseName(id, tit)
{
    var val=Share.strProcess.trimLR( $("#"+id).val() );
    var len=val.length;
    if(len == 0)
        return reFun("请输入课程名",id, tit);
    else if(len>50)
        return reFun("课程名最长为50个字符",id, tit);
    return true;
}
function reFun(str, id, tit){
    $("#"+tit).text(str);//alert("课程名长度为1-50，请修改后重试");
    Share.sbf.focus(id);
    return false;
}
function judRepeat(val, sid){
    var tf=true;
    var id,id_2,objVal,pg=Share.strProcess.pgSubstr,trimLR=Share.strProcess.trimLR;
    id_2=sid||-1;
    $("#courseTable tr").each(function(i, o){
        id=pg($(o).attr("id"));
        objVal=trimLR($("#course_"+id).text());
        if(val == objVal && id!=id_2)
        {
            tf=false;
            return false;
        }
    });
    return tf;
}
var updWinFun={
    uid:null,
    str:"course_",
    sVal:"",
    open:function(id){
        this.uid=id;
        this.sVal=Share.strProcess.trimLR($("#"+this.str+id).text());
        $("#popDiv1_inp").val(this.sVal);
        $('#popDiv1').show();
        $('#popIframe1').show();
        $('#bg1').show();
    },
    close:function(){
        $('#popDiv1').hide();
        $('#popIframe1').hide();
        $('#bg1').hide();
        this.clear();
    },
    ok:function(schoolId){
        var inp="popDiv1_inp";
        $('#popDiv1').hide();
        var val = Share.strProcess.trimLR($("#"+inp).val());
 
        if(val == this.sVal)
        {
            updWinFun.close();
            alert("修改课程成功");
            return;
        }
        if(!validateCourseName(inp, "errorInfo_2"))
        {
            $('#popDiv1').show();
            return ;
        }
        if(!judRepeat(val, this.uid))
        {
            reFun("课程名重复，请修改后重试", inp, "errorInfo_2");
            $('#popDiv1').show();
            return ;
        }
        
        var param={};
        param.subject_name=val;
        param.subject_id=this.uid;
        $.ajax({
            type:"post",
            data:param,
            dataType:"json",
            url:"/Amscontrol/Subjectmanage/modifySubjectInfo/schoolId/"+schoolId,
            success:function(data){
                var d=data.result;
                if(d.code>0)
                {
                    $("#course_"+param.subject_id).text(val);
                    updWinFun.close();
                    alert("修改课程成功");
                }
                else
                {
                    reFun(d.message, inp, "errorInfo_2");//alert(d.message);
                    $('#popDiv1').show();
                }
            }
        });
        /*
        //test data star
            //success 
        $("#"+this.str+this.uid).text(val);
        updWinFun.close();
            //error
        //reFun("错误了", inp, "errorInfo_2");//alert(d.message);
        //$('#popDiv1').show();
        //test data end
        */
    },
    clear:function(){
        $("#errorInfo_2").text("");
    }
};
var addWinFun={
    inp:"popDiv_inp",
    ok:function(schoolId){
        var inp=this.inp;
        $('#popDiv').hide();
        var val = Share.strProcess.trimLR($("#"+inp).val());

        if(!validateCourseName(inp, "errorInfo"))
        {
            $('#popDiv').show();
            return ;
        }
        if(!judRepeat(val))
        {
            reFun("课程名重复，请修改后重试", inp, "errorInfo");
            $('#popDiv').show();
            return ;
        }
        
        var param={};
        param.subject_name=val;
        $.ajax({
            type:"post",
            data:param,
            dataType:"json",
            url:"/Amscontrol/Subjectmanage/addSubjectInfo/schoolId/"+schoolId,
            success:function(data){
                var d=data.result;
                if(d.code>0)
                {
                    //show data
                    addWinFun.addTable(data.data.subject_id, val);
                    addWinFun.close();
                    alert("添加课程成功");
                }
                else
                {
                    reFun(d.message, inp, "errorInfo");//alert(d.message);
                    $('#popDiv').show();
                }
            }
        });
        /*
        //test data star
            //success show data
        addWinFun.addTable(3, "wade");
        addWinFun.close();
           //error show data
        //reFun("错误了", inp, "errorInfo");//alert(d.message);
        //$('#popDiv').show();
        //test data end
        */
    },
    addTable:function(id, value){
        var t=new Share.htmlFun.TableCls();
        t.init("courseTable","");
        var tr, tdArr=[], td_a;
        tr=$("<tr id='courseTr_"+ id +"'></tr>");
        tdArr[0]=$('<td height="30" id="course_'+id+'" align="center" bgcolor="#dfdbdb"></td>');
        tdArr[1]=$('<td height="30" align="center" bgcolor="#dfdbdb"></td>');
        td_a=$('<a href="javascript:updWinFun.open(\''+ id +'\');" class="zjxk">修改</a>');//思想品德 id
        tdArr[0].text(value);//思想品德
        tdArr[1].append(td_a);
        t.appendTbody(tr,tdArr,true);
    },
    open:function(){
        $('#popDiv').show();
        $('#popIframe').show();
        $('#bg').show();
    },
    close:function(){
        $('#popDiv').hide();
        $('#popIframe').hide();
        $('#bg').hide();
        this.clear();
    },
    clear:function(){
        $("#"+this.inp).val("");
        $("#errorInfo").text("");
    }
};