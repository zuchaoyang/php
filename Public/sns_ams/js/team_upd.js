setTeam.open=function(){
        var team_id = $("#team_id").val();
        $('#popIframe').show();
        $('#bg').show();
        var param={};   param.team_id=team_id;
        if(this.baseData == null)
        {
            $.ajax({
                type:"get",
                dataType:"json",
                url:"/Amscontrol/AmsTeam/getClassMemberListForModify",
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
                        alert(d.error.message);
                    }
                }
//                //测试内容  star
//                ,error:function(){
//                    var json = {
//                            error:{
//			                    code:1,	//“<0”失败 “>0”成功
//	                            message:'hkh'	//错误信息
//                            },
//                            data:[{
//			                            uid:"1",
//			                            username:"Wade22",
//			                            'checked' : true
//                                    },
//                                    {
//			                            uid:"12",
//			                            username:"Wade33",
//			                            'checked' : false
//                                    },
//                                    {
//			                            uid:"13",
//			                            username:"Wade334",
//			                            'checked' : false
//                                    },
//                                    {
//			                            uid:"14",
//			                            username:"Wade55",
//			                            'checked' : true
//                                    },
//                                    {
//			                            uid:"15",
//			                            username:"Wade666",
//			                            'checked' : false
//                                    },
//                                    {
//                            	        uid:"2",
//			                            username:"Wade277",
//			                            'checked' : false
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
//                //测试内容  end
            });
        }
        else
        {
			setTeam.createSelectTable(this.baseData);
            $("#popDiv").show();
        }
    };
teamData._name = "member_list[]";

//提交方法
function upd_submit(){
	var flag = upd_check();
	if(flag) {
		document.getElementById('form_modify_team').submit();
	}
}

function upd_check() {
	var passed = document.getElementById('passed').value;
	if(passed < 0) {
		alert('小队名字不可用，请重新输入!');
		return false;
	}
	return true;
}

function upd_check_name() {
	var team_name = document.getElementById('team_name').value;
	var old_team_name = document.getElementById('old_team_name').value;
	var class_code = document.getElementById('class_code').value;
	if(trim(team_name) != trim(old_team_name)) {
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
}

function trim(str) {
	return str.replace(/(^\s*)|(\s*$)/g,"");
}
