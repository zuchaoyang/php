var pageTable={
    select:function(id){
        $('#popIframe2').show();
        $('#bg2').show();
        var param={};
        param.sid=id;
        $.ajax({
            type:"post",
            dataType:"json",
            data:param,
            url:"/Basecontrol/Applyschool/getSchoolInfo",
            success:function(data){
                var r=data.error;
                if(r.code > 0)
                {
                    var d=data.data;
                    $("#school_name").text(d.school_name);              //学校名称（string）
                    $("#school_address").text(d.school_address);        //学校地址（string）
                    $("#post_code").text(d.post_code);                  //邮政编码（string）
                    $("#school_create_date").text(d.school_create_date);//建校时间（string）
                    $("#school_type").text(d.school_type);              //学校类型（string）
                    $("#resource_advantage").text(d.resource_advantage);//资源优势（string）
                    $("#school_master").text(d.school_master);          //校长（string）
                    $("#contact_person").text(d.contact_person);        //联系方式（string）
                    $("#class_num").text(d.class_num);                  //班级数量（string）
                    $("#teacher_num").text(d.teacher_num);              //教师数量（string）
                    $("#student_num").text(d.student_num);              //学生数量（string）
                    $("#net_manager").text(d.net_manager);              //姓名（string）
                    $("#net_manager_phone").text(d.net_manager_phone);  //联系方式（string）
                    $("#net_manager_email").text(d.net_manager_email);  //邮箱设置（string）
                    $("#school_url_old").text(d.school_url_old);        //原学校网址（string）
                    $("#school_url_new").text(d.school_url_new);        //
                    $('#popDiv2').show();
                }
                else
                {
                    pageTable.closeSelect();
                    alert(r.message);
                }
            }
        });
        /*
        //test data star
        $("#school_name").text("三中");              //学校名称（string）
        $("#school_address").text("天津市红桥区");        //学校地址（string）
        $('#popDiv2').show();
        //test data end
        */
    },
    closeSelect:function(){
        $('#popIframe2').hide();
        $('#bg2').hide();
        $('#popDiv2').hide();
    },
    nopass:function(id){
        $('#bobIframe').show();
        $('#bger').show();
        var param={};
        param.sid=id;
        $.ajax({
            type:"get",
            dataType:"json",
            data:param,
            url:"/Basecontrol/Applyschool/showRefuseReason",
            success:function(data){
                var r=data.error;
                if(r.code > 0)
                {
                    $("#bobDiv_p").html(data.data.refusereason);//因为有html标记
                    $('#bobDiv').show();
                }
                else
                {
                    pageTable.closeNOpass();
                    alert(r.message);
                }
            }
        });
        /*
        //test data star
        $("#bobDiv_p").html("1.就是没有通过<br />2.不让过");//因为有html标记
        $('#bobDiv').show();
        //test data end
        */
    },
    closeNOpass:function(){
        $('#bobDiv').hide();
        $('#bobIframe').hide();
        $('#bger').hide();
    },
    saveId:null,
    saveSM_Id:null,
    resetPwd:function(id, school_manager_id){
        this.saveId=id;
        this.saveSM_Id=school_manager_id;
        $('#popDiv').show();
        $('#popIframe').show();
        $('#bg').show();
    },
    okPwd:function(){
        var url="/Basecontrol/Applyschool/changePwd";
        window.location.href=url+"/sid/"+this.saveId+"/net_manager_account/"+this.saveSM_Id;
    },
    closePwd:function(){
        $('#popDiv').hide();
        $('#popIframe').hide();
        $('#bg').hide();
    }
}