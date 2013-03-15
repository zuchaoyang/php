var selectCounselor={
    _cId:null,
    _saveVal:null,
    _smallId:null,
    _id:null,
    open:function(classId,smallId,id){
        this._cId=classId;
        this._smallId=smallId;
        this._id=id;
        if(this._saveVal==null)
            this._saveVal=$("#pop_sel").val();
        $("#pop_sel").val(this._saveVal);
        $('#popDiv1').show();
        $('#popIframe1').show();
        $('#bg1').show();
    },
    close:function(){
        this.hide();
        this.clear();
    },
    hide:function(){
        $('#popDiv1').hide();
        $('#popIframe1').hide();
        $('#bg1').hide();
    },
    ok:function(){
        var val = $("#pop_sel").val();
        if(val == -1)
        {
            $("#errorInfo_2").text("请选择小辅导员");
            return;
        }
        if(val == this._saveVal)
        {
            alert("小辅导员设置成功");
            this.close();
            return ;
        }
        $('#popDiv1').hide();
        var param={};
        param.squadroncode=this._cId;
        param.clientaccount=val;
        param.smallId=this._smallId;
        param.id=this._id;
        $.ajax({
            type:"post",
            data:param,
            dataType:"json",
            url:"/Amscontrol/Amssquadron/setSmallCounselor",
            success:function(data){
                var d=data.error;
                if(d.code>0)
                {
                    selectCounselor._saveVal=val;
                    selectCounselor.close();
                    alert("小辅导员设置成功");
                }
                else
                {
                    $("#errorInfo_2").text(d.message);
                    $('#popDiv1').show();
                }
            }/*
            //测试数据 star
            ,error:function(){
                var data={
		            error:{
			            code:1,	//“<0”失败 “>0”成功
	                    message:'保存失败'	//错误信息
                    },
                    success:{
			            code:1,	//“<0”失败 “>0”成功
	                    message:'保存成功'	//错误信息
                    }
                }
                var d=data.error;
                if(d.code>0)
                {
                    selectCounselor._saveVal=val;
                    selectCounselor.close();
                    alert("小辅导员设置成功");
                }
                else
                {
                    $("#errorInfo_2").text(d.message);
                    $('#popDiv1').show();
                }
            }//测试数据 end
            */
        });
    },
    clear:function(){
        $("#errorInfo_2").text("");
        $("#pop_sel").val(-1);
    }
};