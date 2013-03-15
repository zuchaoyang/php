var setTeam={
    baseData:null,
    open:function(){
        var class_code = $("#class_code").val();
        $('#popIframe').show();
        $('#bg').show();
        var param={};   param.class_code=class_code;
        if(this.baseData == null)
        {
            $.ajax({
                type:"get",
                dataType:"json",
                url:"/Amscontrol/AmsTeam/getClassMemberList",
                data:param,
                success:function(json){
                    var d=json.error;
                    if(d.code>0)
                    {
                        setTeam.baseData=json.data;
                        setTeam.createSelectTable(json.data);
                        $("#popDiv").show();
                    }
                    else
                    {
                        setTeam.close();
                        alert(d.message);
                    }
                }
                //测试内容  star
//                ,error:function(){
//                    var json = {
//                            error:{
//			                    code:1,	//“<0”失败 “>0”成功
//	                            message:'hkh'	//错误信息
//                            },
//                            data:[{
//			                            uid:"1",
//			                            username:"Wade22"
//                                    },
//                                    {
//			                            uid:"12",
//			                            username:"Wade33"
//                                    },
//                                    {
//			                            uid:"13",
//			                            username:"Wade334"
//                                    },
//                                    {
//			                            uid:"14",
//			                            username:"Wade55"
//                                    },
//                                    {
//			                            uid:"15",
//			                            username:"Wade666"
//                                    },
//                                    {
//                            	        uid:"2",
//			                            username:"Wade277"
//                                    }]
//                        };
//                    var d=json.error;
//                    if(d.code>0)
//                    {
//                        setTeam.baseData=json.data;
//                        setTeam.createSelectTable(json.data);
//                        $("#popDiv").show();
//                    }
//                    else
//                    {
//                        setTeam.close();
//                        alert(d.error.message);
//                    }
//                }
                //测试内容  end
            });
        }
        else
        {
//setTeam.createSelectTable(this.baseData);
            $("#popDiv").show();
        }
    },
    createSelectTable:function(data){
        //setTeamTable
        var t=new Share.htmlFun.TableCls();
        t.init("setTeamTable","");
        t.clear();
        if(data.length > 0)
        {
            var tr, tdArr=[],td_inp,i=0,j=data.length,_data, check_s;
            for(;i<j;i++)
            {
            	check_s="";
                _data=data[i];
                tr = $("<tr></tr>");
                tdArr[0]=$("<td></td>");
                tdArr[0].attr("id", "setTeamTable_td_"+_data.uid);
                tdArr[0].text(_data.username);
                tdArr[1]=$("<td></td>");
                if(_data.checked)
                	check_s=' checked="checked"';
                td_inp=$('<input type="checkbox"' + check_s +'/>');//checked="checked"
                td_inp.attr("name", "setTeamTable_td_CheckBox");

                td_inp.val(_data.uid);
                tdArr[1].append(td_inp);
                t.appendTbody(tr, tdArr, true);
            }
            //t.createEnd();
        }
        else
        {
            t.nullData(2);
        }
    },
    ok:function(){
        var arr = $("input[@name='setTeamTable_td_CheckBox']:checked");
        if(arr.length)
        {
            var _obj=[], data;
            arr.each(function(i, o){
                val=$(o).val();
                data={};
                data.show=$("#setTeamTable_td_" + val).text();
                data.value=val;
                _obj[_obj.length]=data;
            });
            teamData.create(_obj);
            this.close();
        }
        else
        {
            alert("请选择小队员");
        }
    },
    close:function(){
        $("#popDiv").hide();
        $("#bg").hide();
        $("#popIframe").hide();
    }
};
var teamData={
    _name:"member_list[]",
    create:function(data){
        this.show(data);
        this.select("team_head", data);
        this.select("team_head_assistant", data);
    },
    show:function(data){
        var t=new Share.htmlFun.TableCls();
        t.init("teamTable","");
        t.clear();
        if(data.length > 0)
        {
            var tr, tdArr, td_span, td_inp, i=0,j=data.length, k, str, _data;
            for(;i<j;i++)
            {
                _data=data[i];
                if(i%5==0)
                {
                    tr = $("<tr></tr>");
                    k=0;
                    tdArr=[];
                }
                if(k==4)
                    str='<td align="center" bgcolor="#dfdbdb"></td>';
                else
                    str='<td height="30" align="center" bgcolor="#dfdbdb"></td>';
                tdArr[k]=$(str);
                td_span=$("<span></span>");
                td_span.text(_data.show);
                td_inp=$('<input type="hidden" />');
                td_inp.attr("name", this._name);
                td_inp.val(_data.value);
                tdArr[k].append(td_inp).append(td_span);
                if((i!=0 && k==4) || (i==(j-1)))
                {
                    t.appendTbody(tr, tdArr, false);
                }
                k++;
            }
            t.createEnd();
        }
        else{
            t.nullData(5, "请选择小队员");
        }
    },
    select:function(id, data){
        var obj = $("#"+id);
        obj.empty();
        obj.append($("<option value='-1'>请选择</option>"));
        if(data.length > 0)
        {
            var option, o;
            for(var i=0,j=data.length;i<j;i++)
            {
                o=data[i];
                option = $("<option></option>");
                option.val(o.value);
                option.text(o.show);
                obj.append(option);
            }
        }
        obj.css("visibility","hidden");
        obj.css("visibility","visible");
    }
};
    var judgeHTML=Share.htmlFun.judgeHTML;
    judgeHTML.successImg="../images/success.gif";//成功的图片path
    judgeHTML.errorImg="../images/error.gif";//错误的图片path
    judgeHTML.remind={
            teamName:"请输入小队名称",
            team_head:"请选择小队长",
            team_head_assistant:"请选择副队长"
            };
    judgeHTML.errorInfo={
            teamName:"输入小队名称有误",
            team_head:"请选择小队长",
            team_head_assistant:"请选择副队长"
            };
    judgeHTML.custom={
	    teamName:function(id){
	        var val = Share.strProcess.trimLR( $("#"+id).val() );
	        //判断小队名称限制位置
	        if(val.length == 0)
	            return "请输入小队名称";
	        if(val.length > 10)
	            return "error";
	        return "success";
	    },
	    team_head:function(id){
	        var val = $("#"+id).val();
	        if(val == -1)
	            return "error";
	        return "success";
	    },
	    team_head_assistant:function(id){
	        var val = $("#"+id).val();
	        if(val == -1)
	            return "error";
	        return "success";
	    }
    }
var domArr;

//提交方法
function pageSubmit(){
	var flag = check();
	
	if(flag) {
		document.getElementById('form_add_team').submit();
	}
}

function check() {
	var passed = document.getElementById('passed').value;
	if(passed < 0) {
		alert('小队名字不可用，请重新输入!');
		return false;
	}
	return true;
}

function check_name() {
	var team_name = document.getElementById('team_name').value;
	var class_code = document.getElementById('class_code'). value;
	$.ajax({
		type:"post",
		url:"/Amscontrol/AmsTeam/checkTeamSameName",
		dataType:"json",
		data:{team_name:team_name,class_code:class_code},
		success: function(data) {
			var code = data.code;
			if(code > 0) {
				document.getElementById('team_name_err').innerHTML = data.message;
				document.getElementById('passed').value = 1;
			} else {
				document.getElementById('team_name_err').innerHTML = data.message;
				document.getElementById('team_name').focus();
				document.getElementById('passed').value = -1;
			}
		}
	});
}

