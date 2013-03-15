    var judgeHtml=Share.htmlFun.judgeAjaxHtml();
    judgeHtml.successImg="/Public/local/amsmanage/images/success.gif";//成功的图片path
    judgeHtml.errorImg="/Public/local/amsmanage/images/error.gif";//错误的图片path
    judgeHtml.loadingImg="/Public/local/amsmanage/images/ajax_loader.gif";//loading的图片path
    judgeHtml.remind={st:"请输入班级名称"};
    judgeHtml.errorInfo={st:"班级名称不能超过50个字符"},
    judgeHtml.custom.st=validateClassName;
    /**
    *   判断班级名称
    * paramter：
    *   domId：String(html班级名称input id)
    * return:String(success成功/error错误/null可为空/non不能为空)//success和null为正确，error和non为错误
    *备注：submit可以根据这个方法来判断，班级名输入是否正确
    **/
    function validateClassName(domId)
    {
        var obj=$("#"+domId);
        var val=Share.strProcess.trimLR(obj.val());
        var len=val.length;
        if(len<=0)
        {
            /*alert("请输入班级名称");*/
            return "non";
        }
        if(len>50)
        {
            /*alert("班级名称不能超过50个字符，请重新输入");*/
            return "error";
        }
        obj.attr("disabled", "disabled");
        //需要修改ajax方法
        var param={};
        param.className=val;
        param.classcode=$("#classcode").val();
        param.schoolid=$("#schoolid").val();
        $.ajax({
            url:"/Homeclass/Classmanage/checkClassName",
            type:"get",
            dataType:"json",
            data:param,
            success:function(json){
                var err=json.error;
                if(err.code > 0)
                {
                    judgeHtml.ajaxCall(domId, "st", "success");
                }
                else
                {
                    judgeHtml.ajaxCall(domId, "st", "班级名称重复");
                }
                obj.attr("disabled", "");
            }
            /*
            ,error:function(e){
                //judgeHtml.ajaxCall(domId, "st", "班级名称重复");
                judgeHtml.ajaxCall(domId, "st", "success");
                obj.attr("disabled", "");
            }*/
        });
        return "callback";
    }
    
    function pageSubmit(){
        var arr=[];
        arr[0]={
            val:"className",
            reg:"st"
        };
        if(!judgeHtml.all(arr))
            return ;
        //判断加入学科（是否需要）
        if(classTable.judIsNull())
        {
            alert("最少添加一个科目");
            return ;
        }
        classTable.getJson();
        //修改成 form提交
        //alert("成功");
        $("#form1").submit();
    }
    
//page operate
    //classSel和teachSel是否有"请选择"？如果有需要更改代码  //ClassSel
var ClassSel={
    obj:new selectPro(),
    ajax:function(val){
        if(val == -1)
        {
            $("#teachSel").empty().append("<option value='-1'>--请选择--</option>");
            return ;
        }
        $("#addCourseDut").attr("disabled", "disabled");
        $("#teachSel").attr("disabled", "disabled");
        this.obj.ajax(val, this);
    },
    complete:function(json){
        var err=json.error;
		if(err.code>0)
        {
            this.obj.addData(json.data, "teachSel", this.fun);
        }
        else
        {	
            alert(err.message);
            $("#teachSel").empty().append("<option value='-1'>--请选择--</option>");
            $("#teachSel").attr("disabled", "");
        }
        /*
        //test data star  
            //success
        var d=[];
        d=[{uid:1, uName:"wade"}, {uid:2, uName:"meleke"}, {uid:3, uName:"ly"}];
        this.obj.addData(d, "teachSel", this.fun);
            //error
        //$("#teachSel").attr("disabled", "");
        //alert("获取教师名失败，请重新选择科目");
        //test data end
        */
    },
    fun:{
        success:function(){
            $("#addCourseDut").attr("disabled", "");
        },
        error:function(){
            $("#addCourseDut").attr("disabled", "disabled");
        }
    }
};
function selectPro(){
    this.req=null;
    this.ajax=function(val, obj){
        if(this.req != null)
            this.req.abort();
        var t=this;
        var param={};
        param.subjectid=val;
        param.schoolid=$("#schoolid").val();
        this.req = $.ajax({	
			type:"get",
			dataType:"json",
			url:"/Homeclass/Classmanage/showTeacherInfoBySubjectId",
			data:param,
			success:function(json){
                obj.complete(json);
                this.req=null;
			}
		});
    };
    this.addData=function(data, selectName, fun){
        var obj=$("#"+selectName);    //$("#teachSel");
        obj.attr("disabled", "");
        obj.empty();
        //判断数据是否小于0，如果小于，那么就没法添加学科(addCourseDut)
        if(data.length > 0)
        {
            var sel=$("<select></select>");
            sel.append("<option value='-1'>--请选择--</option>");
            var option;
            for(var i in data)//data:{uid, uName}
            {
                option=$("<option></option>");
                option.val(data[i].uid).text(data[i].uName);
                sel.append(option);
            }
            obj.html(sel.html());
            obj.attr("disabled", "");
            fun.success();
        }
        else
        {
            fun.error();
            obj.append("<option value='-1'>无数据</option>");
            obj.attr("disabled", "disabled");
        }
        obj.css("visibility","hidden");
        obj.css("visibility","visible");
    }
}
var winUpd={
    _sid:-1,
    _courseVal:-1,
    _teacherVal:-1,
    _selObj:new selectPro(),
    open:function(id){
        this._sid=id;
        this._courseVal=$("#pCT_courseVal_"+id).val();
        this._teacherVal=$("#pCT_teacherVal_"+id).val();
        $("#popDiv1_course").val(this._courseVal);
        
        this.des();
        
        $('#popIframe1').show();
        $('#bg1').show();
        
        this._selObj.ajax(this._courseVal, this);
    },
    ajax:function(val){
        if(val==-1)
        {
            $("#popDiv1_teacher").empty().append("<option value='-1'>--请选择--</option>");
            return ;
        }
        this._teacherVal=-1
        this.des();
        this._selObj.ajax(val, this);
    },
    des:function(){
        $("#popDiv1_cls01").attr("disabled", "disabled");
        $("#popDiv1_cls02").attr("disabled", "disabled");
        $("#popDiv1_ok").attr("disabled", "disabled");
        $("#popDiv1_teacher").attr("disabled", "disabled");
    },
    complete:function(json){
        var err=json.error;
		if(err.code>0)
        {
            this._selObj.addData(json.data, "popDiv1_teacher", this.fun);
        }
        else
        {
            alert(err.message);
            $("#popDiv1_teacher").empty().append("<option value='-1'>--请选择--</option>");
            this.fun.error();
        }
        /*
        //test data star  
            //success
        var d=[];
        d=[{uid:1, uName:"wade"}, {uid:2, uName:"meleke"}, {uid:3, uName:"ly"}];
        this._selObj.addData(d, "popDiv1_teacher", this.fun);
            //error
        //alert("获取数据失败");
        //this.fun.error();
        //test data end
		*/
        $('#popDiv1').show();
    },
    fun:{
        success:function(){
            if(winUpd._teacherVal!=-1)
                $("#popDiv1_teacher").val(winUpd._teacherVal);
            winUpd.clear();
        },
        error:function(){
            $("#popDiv1_teacher").attr("disabled", "disabled");
            $("#popDiv1_cls01").attr("disabled", "");
            $("#popDiv1_cls02").attr("disabled", "");
            $("#popDiv1_ok").attr("disabled", "disabled");
        }
    },
    ok:function(){
        var courseVal =$("#popDiv1_course").val();
        var teacherVal =$("#popDiv1_teacher").val();
        if(teacherVal == -1){
            alert("请选择任课老师");
            $("#popDiv1_teacher").focus();
            return ;
        }
        var id=this._sid;
        if(!(this._courseVal==courseVal && this._teacherVal==teacherVal))
        {
            if(!classTable.judReset(id, courseVal))
            {
                alert("修改科目重复,请重新选择");
                return ;
            }
            var course={}, teacher={};
            course.val=courseVal;
            course.show=$("select[id='popDiv1_course'] option:selected").text();
            teacher.val=teacherVal;
            teacher.show=$("select[id='popDiv1_teacher'] option:selected").text();
            classTable.update(id, course, teacher);
        }
        this.close();
        alert("修改成功");
    },
    close:function(){
        $('#popDiv1').hide();
        $('#popIframe1').hide();
        $('#bg1').hide();
        this.clear();
    },
    clear:function(){
        $("#popDiv1_teacher").attr("disabled", "");
        $("#popDiv1_cls01").attr("disabled", "");
        $("#popDiv1_cls02").attr("disabled", "");
        $("#popDiv1_ok").attr("disabled", "");
    }
};
var classTable={
    num:-1,
    classSel:"classSel",
    teachSel:"teachSel",
    update:function(id, course, teacher){
        $("#pCT_courseVal_"+id).val(course.val);
        $("#pCT_teacherVal_"+id).val(teacher.val);
        $("#pCT_course_"+id).text(course.show);
        $("#pCT_teacher_"+id).text(teacher.show);
        var sObj=$("#pCT_saveVal_"+id);
        var sVal=sObj.val();
        if(sVal!="add")
            sObj.val("upd");
        //alert($("#pageCourseTbody").html());
    },
    del:function(id){
        var show=$("#pCT_course_"+id).text();
        if(window.confirm("您确定要删除"+show+"科目吗？"))
        {
            $("#pCT_tr_"+id).hide();
            var sObj=$("#pCT_saveVal_"+id);
            var sVal=sObj.val();
            if(sVal!="add")
                sObj.val("del");
            else
                $("#pCT_tr_"+id).remove();
            if(this.judIsNull())
                $("#pageCourseTfoot").show();
        }
    },
    add:function(){
        var clsVal=$("#"+this.classSel).val();
        var teaVal=$("#"+this.teachSel).val();
        if(teaVal == -1){
            alert("请选择任课老师");
            $("#teachSel").focus();
            return ;
        }
        if(!this.judReset(-1, clsVal))
        {
            alert("添加科目重复,请重新选择");
            return ;
        }
        $("#pageCourseTfoot").hide();
        this.addData(clsVal, teaVal);
    },
    getJson:function(){
        var id, obj, pg=Share.strProcess.pgSubstr;
        var json={};
        json.data=[];
        $("#pageCourseTbody tr").each(function(i, o){
            id=pg($(o).attr("id"));
            obj={};
            obj.subjectid=$("#pCT_courseVal_"+id).val();
            obj.new_teacherid=$("#pCT_teacherVal_"+id).val();
            obj.old_teacherid=$("#pCT_id_"+id).val();
            obj.client_class_id=$("#pCT_saveid_"+id).val();
            obj.type=$("#pCT_saveVal_"+id).val();
            obj.class_teacher_id=$("#pCT_class_teacher_id_"+id).val();
            json.data[i]=obj;
        });
        var json_s=Share.jsonProcess.toJSON(json);
        $("#json_input").val(json_s);
    },
    addData:function(clsVal, teaVal){
        if(this.num == -1)
            this.num=$("#pageCourseTbody tr").length -0 +10;
        var clsText = $("#classSel option:selected").text();
        var teaText = $("#teachSel option:selected").text();
        var t=new Share.htmlFun.TableCls();
        t.init("pageCourseTbody","");
        var tr, tdArr=[], td_a=[], td_inp=[];
        tr=$("<tr id='pCT_tr_"+ this.num +"'></tr>");
        tdArr[0]=$('<td height="30" align="center" id="pCT_course_'+ this.num +'"></td>');
        tdArr[1]=$('<td height="30" align="center" id="pCT_teacher_'+ this.num +'"></td>');
        tdArr[2]=$('<td height="30" align="center" colspan="3"></td>');
        tdArr[0].text(clsText);
        tdArr[1].text(teaText);
        td_a[0]=$('<a href="javascript:winUpd.open('+ this.num +');" class="zjxk">修改</a>');
        td_a[1]=$('<span>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</span>');
        td_a[2]=$('<a href="javascript:classTable.del('+ this.num +');" class="zjxk">删除</a>');
        td_inp[0]=$('<input type="hidden" id="pCT_courseVal_'+ this.num +'" />');           //course添加name属性
        td_inp[1]=$('<input type="hidden" id="pCT_teacherVal_'+ this.num +'" />');          //teacher添加name属性
        //记录数据类型[add:新添加数据\upd:以更改数据\del:要删除的数据\"":从数据库中读出的数据(没有进行过update和delete操作)]
        td_inp[2]=$('<input type="hidden" id="pCT_saveVal_'+ this.num +'" value="add" />'); //param添加name属性
        td_inp[3]=$('<input type="hidden" id="pCT_id_'+ this.num +'" value="'+ this.num +'" />'); //id添加name属性
        td_inp[0].val(clsVal);
        td_inp[1].val(teaVal);
        t.append(tdArr[2], td_a);
        t.append(tdArr[2], td_inp);
        t.appendTbody(tr, tdArr, true);
        this.num++;
    },
    judIsNull:function(){//
        var tf=true;
        var id, _dtf, pg=Share.strProcess.pgSubstr;
        $("#pageCourseTbody tr").each(function(i, o){
            id=pg($(o).attr("id"));
            _dtf=$("#pCT_saveVal_"+id).val();
            if(_dtf!="del")
            {
                tf=false;
                return false;
            }
        });
        return tf;
    },
    judReset:function(eId, val){
        var tf=true;
        var id, _val, _dtf, pg=Share.strProcess.pgSubstr;
        $("#pageCourseTbody tr").each(function(i, o){
            id=pg($(o).attr("id"));
            _dtf=$("#pCT_saveVal_"+id).val();
            if(eId != id && _dtf!="del")
            {
                _val=$("#pCT_courseVal_"+ id).val();
                if(val == _val)
                {
                    tf=false;
                    return false;
                }
            }
        });
        return tf;
    }
};