/**
*   判断老师姓名是否合法(允许中英文 + 空格，不允许特殊字符。长度限制30个字符)
* paramter：
*   id：String(dom id)
*   tit:错误提示dom id(errInfo, errInfo_2)
* return:boolean(true正确false错误)
**/
function validateTeachName(id, tit)
{
    var val=Share.strProcess.trimLR($("#"+id).val());
    var len=val.length;
    if(len<1 || len>30)
        return jud();
    var s=Share.regexProcess;
    if(!s.isUserName_2(val))
        return jud();
    function jud()
    {
        $("#"+tit).text("姓名输入有误，请重新输入"); //alert("姓名输入有误，请重新输入");
        Share.sbf.focus(id);
        return false;
    }
    return true;
}
var addWinFun={
    errTit:"errorInfo",
    trimLR:Share.strProcess.trimLR,
    ok:function(){
        if(!validateTeachName("popDiv1_inp", this.errTit))
            return ;
        var val=this.trimLR($("#popDiv1_inp").val());
        //var selVal=$("#popDiv1_course").val();
        var selVal="";
        var obj={id:"", name:""}, obj1;
        $(":input[name='subjectId']").each(function(){
            obj1=$(this);
            if(obj1.attr("checked"))
            {
                obj.id += "," + obj1.val();
                obj.name += "," + obj1.attr("title");
            }
        });
        var is_use_office = $("#is_use_office").attr('checked');
        if(is_use_office){
    		var department_id = $("#dpt_id").val();
    		var department_name = $("#dptname").val();
    		if (department_id == "" || department_name == ""){
    			$("#"+addWinFun.errTit).text("请选择部门");
                return ;
    		}
    		var duty = $("#duty").val();
    		if (duty == ""){
    			$("#"+addWinFun.errTit).text("请填写职务");
                return ;
    		}
    		if(duty.length>8){
    			$("#"+addWinFun.errTit).text("职务长度不能超过八个字");
                return ;
    		}
    		var role = $("#role option:selected").val();
    		if (role == -1){
    			$("#"+addWinFun.errTit).text("请填写角色");
                return ;
    		}
    	}
        if(obj.id =="" || obj.id == ",")
        {
        	if(!is_use_office){
        		$("#"+addWinFun.errTit).text("请选择所任科目");
                return ;
        	}
        }
        else
        {
            obj.id = obj.id.substr(1);
            obj.name = obj.name.substr(1);
            selVal=obj.id.split(',');
        }
        $('#popDiv1').hide();
        var param={};
        param.sid=schoolID;
        param.tercherName=val;
        param["subjectId[]"]=selVal;
    	param.is_use_office=is_use_office;
        param.department_id = department_id;
        param.duty = duty;
        param.role = role;
        $.ajax({
            type:"get",
            dataType:"json",
            url:"/Amscontrol/Amsteacher/addTeacher",
            data:param,
            success:function(json){
                var d=json.result;
                if(d.code>0)
                {
                    addWinFun.add(json.data.uid, obj.id, obj.name, val, is_use_office, department_id, department_name, duty, role);
                    addWinFun.close();
                    alert("添加成功");
                }
                else
                {
                    $("#"+addWinFun.errTit).text(d.message);
                    $('#popDiv1').show();
                }
            }
        });
        /*
        //test data star
            //cussess
        addWinFun.add(4, obj.id, obj.name, val);
        addWinFun.close();
        alert("添加成功");
            //error
        //$("#"+addWinFun.errTit).text("错误了");
        //$('#popDiv1').show();
        //test data end
        */
    },
    add:function(id, selVal, selName, val, is_use_office, department_id, department_name, duty, role){
        //var optionVal=$("select[@id='popDiv1_course'] option[@selected]").text();
        var t=Share.htmlFun.TableCls();
        t.init("pTbody","");
        var tr, tdArr=[];
        tr=$("<tr></tr>");
        tdArr[0]=$('<td height="30" align="center" id="pTbody_name_'+id+'"></td>');
        tdArr[0].text(val);
        tdArr[1]=$('<td height="30" align="center" id="pTbody_course_'+id+'"></td>');
        //tdArr[1].text(optionVal);
        if(selName == "") {
        	tdArr[1].text("暂不任课");
        }else{
        	tdArr[1].text(selName);
        }
        tdArr[2]=$('<td height="30" align="center">'+id+'</td>');
        if(is_use_office){
        	var office=1;
        	tdArr[3]=$('<td height="30" align="center" id="pTbody_office_'+id+'">是</td>');
        }
        else{
        	var office=0;
        	tdArr[3]=$('<td height="30" align="center" id="pTbody_office_'+id+'">否</td>');
        }
        tdArr[4]=$('<td height="30" align="center"><a href="javascript:updWinFun.open('+id+');" class="zjxk">修改</a><input id="is_office_'+id+'" type="hidden" value="'+office+'"/><input id="dpt_name_'+id+'" type="hidden" value="'+department_name+'"/><input id="dpt_id_'+id+'" type="hidden" value="'+department_id+'"/><input id="duty_name_'+id+'" type="hidden" value="'+duty+'"/><input id="role_id_'+id+'" type="hidden" value="'+role+'"/><input id="pTbody_courseVal_'+id+'" type="hidden" value="'+selVal+'" /></td>');
        t.appendTbody(tr, tdArr, true);
    },
    open:function(){
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
    clear:function(){
        //$("#popDiv1_course").val(1);
        $("#popDiv1_inp").val("");
        $("#"+this.errTit).text("");
        $("#dpt_id").val("");
        $("#dptname").val("");
        $("#duty").val("");
        $("#is_use_office").attr('checked','');
        $("#bgpt").hide();
        $("#role").val(-1);  
        clearCheckbox("subjectId");
    }
};
var updWinFun={
    errTit:"errorInfo_2",
    sId:null,
    trimLR:Share.strProcess.trimLR,
    valObj:{},
    ok:function(){
        if(!validateTeachName("popDiv_inp", this.errTit))
            return ;
        var val=this.trimLR($("#popDiv_inp").val());
        //var selVal=$("#popDiv_course").val();
        var selVal="";
        var obj={id:"", name:""}, obj1;
        $(":input[name='subjectId_2']").each(function(){
            obj1=$(this);
            if(obj1.attr("checked"))
            {
                obj.id += "," + obj1.val();
                obj.name += "," + obj1.attr("title");
            }
        });
        var upd_is_use_office = $("#upd_is_use_office").attr('checked');
        if(upd_is_use_office){
    		var upd_department_id = $("#dpt_id1").val();
    		var upd_department_name = $("#dptname1").val();
    		if (upd_department_id == "" || upd_department_name == "" || upd_department_name == "null"){
    			$("#"+updWinFun.errTit).text("请选择部门");
                return ;
    		}
    		var upd_duty = $("#upd_duty").val();
    		if (upd_duty == ""){
    			$("#"+updWinFun.errTit).text("请填写职务");
                return ;
    		}
    		if(upd_duty.length>8){
    			$("#"+updWinFun.errTit).text("职务长度不能超过八个字");
                return ;
    		}
    		var upd_role = $("#upd_role").val();
    		if (upd_role == -1){
    			$("#"+updWinFun.errTit).text("请填写角色");
                return ;
    		}
    	}
        if(obj.id =="" || obj.id == ",")
        {
        	if(!upd_is_use_office){
	            $("#"+updWinFun.errTit).text("请选择所任科目");
	            return ;
        	}
        }
        else
        {
            obj.id = obj.id.substr(1);
            obj.name = obj.name.substr(1);
            selVal=obj.id.split(',');
        }
 //alert(obj.id + "||" + obj.name +"{"+ this.valObj.sourse+ "}" +"||"+this.valObj.show +"=="+ val);
        if(this.valObj.show == val && this.valObj.sourse==obj.id && this.valObj.is_office==upd_is_use_office && this.valObj.dpt_id==upd_department_id && this.valObj.duty_name==upd_duty && this.valObj.role_id==upd_role)
        {
            updWinFun.close();
            alert("修改成功");
            return ;
        }
        $('#popDiv').hide();
        var param={};
        param.sid=schoolID;
        param.tercherName=val;
        param["subjectId[]"]=selVal;
        param.upd_is_use_office=upd_is_use_office;
        param.upd_department_id = upd_department_id;
        param.upd_duty = upd_duty;
        param.upd_role = upd_role;
        param.uid=this.sId;
        $.ajax({
            type:"get",
            dataType:"json",
            url:"/Amscontrol/Amsteacher/modifyTercher",
            data:param,
            success:function(json){
                var d=json.result;
                if(d.code>0)
                {
                    //updWinFun.update(updWinFun.sId, selVal, val);
                    updWinFun.update(updWinFun.sId, obj.id, obj.name, val, upd_is_use_office, upd_department_id ,upd_department_name, upd_duty, upd_role);
                    updWinFun.close();
                    alert("修改成功");
                }
                else
                {
                    $("#"+updWinFun.errTit).html(d.message);
                    $('#popDiv').show();
                }
            }
        });
        
        //test data star
            //cussess
        //updWinFun.update(updWinFun.sId, obj.id, obj.name, val);
        //updWinFun.close();
        //alert("修改成功");
            //error
        //$("#"+updWinFun.errTit).text("错误了");
        //$('#popDiv').show();
        //test data end
        
    },
    update:function(id, courseId, courseArr_s,name_s, upd_is_use_office, upd_department_id ,upd_department_name, upd_duty, upd_role){
        //var optionVal=$("select[@id='popDiv_course'] option[@selected]").text();
        $("#pTbody_name_"+id).text(name_s);
        if(courseId == "") {
        	$("#pTbody_course_"+id).text("暂不任课");    //
        }else{
        	$("#pTbody_course_"+id).text(courseArr_s);    //
        }
        if(upd_is_use_office){
        	$("#is_office_"+id).val(1);
        	$("#pTbody_office_"+id).text('是'); 
        }else{
        	$("#is_office_"+id).val(0);
        	$("#pTbody_office_"+id).text('否');    //
        }
        $("#dpt_name_"+id).val(upd_department_name);
        $("#dpt_id_"+id).val(upd_department_id);
        $("#duty_name_"+id).val(upd_duty);
        $("#role_id_"+id).val(upd_role);
    	$("#pTbody_courseVal_"+id).val(courseId);
        
    },
    open:function(id){
        this.sId=id;
        this.valObj.show=this.trimLR($("#pTbody_name_"+this.sId).text());
        this.valObj.sourse=$("#pTbody_courseVal_"+this.sId).val();
        this.valObj.is_office=$("#is_office_"+id).val();
        this.valObj.dpt_id=$("#dpt_id_"+this.sId).val();
        this.valObj.duty_name = $("#duty_name_"+this.sId).val();
        this.valObj.role_id = $("#role_id_"+this.sId).val();
        var arr = this.valObj.sourse.split(',');
        $("#popDiv_inp").val(this.valObj.show);
        //subjectId_2
        var i,j=arr.length, obj;
        $(":input[name='subjectId_2']").each(function(){
            obj=$(this);
            for(i=0;i<j;i++)
            {
                if(obj.val() == arr[i])
                {
                    obj.attr("checked", "checked");
                    break;
                }
            }
        });
        if($("#is_office_"+id).val() == 1){
        	$("#upd_is_use_office").attr('checked','true');
            $("#bgpt2").show();
        	$("#dptname1").val($("#dpt_name_"+id).val());
        	$("#dpt_id1").val($("#dpt_id_"+id).val());
        	$("#upd_duty").val($("#duty_name_"+id).val());
        	$("#upd_role").val($("#role_id_"+id).val());
        }else if($("#is_office_"+id).val() == 0){
        	$("#upd_is_use_office").attr('checked','');
        	$("#bgpt2").hide();
        }
        
        
        //$("#popDiv_course").val(this.valObj.sourse);
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
        $("#popDiv_inp").val("");
        $("#"+this.errTit).text("");
        $("#dpt_id1").val("");
        $("#dptname1").val("");
        $("#upd_duty").val("");
        $("#upd_is_use_office").attr('checked','');
        $("#bgpt2").hide();
        $("#is_office").val("");
        $("#upd_role").val(-1);  
        clearCheckbox("subjectId_2");
    }
};
function clearCheckbox(id){
    $(":input[name='"+ id +"']").each(function(){
        $(this).attr("checked", "");
    });
}
