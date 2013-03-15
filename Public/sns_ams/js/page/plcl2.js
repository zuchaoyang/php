    /** 判断提交  */
	function confirmData2(){
	    var _tt=$("#textarea");
	    var _trMot = Share.strProcess.trimLR;
	    var cont = _trMot( _tt.val() );
	    var i=0, _arr = cont.split("\n"), j=_arr.length;
	    if(j == 1)
	    {
	        if(_arr[0] == "")
	        {
	            alert("请输入学生姓名");
	            _tt.val(""); 
	            _tt.focus();
	            return ;
	        }
	    }
	    var val="", len, s=Share.regexProcess, saveArr=[];
	    for(;i<j;i++)
	    {
	        val=_trMot(_arr[i]);
	        len = val.length;
	        //为空省略过去
	        if(len > 0)
            {
                if(len<2 || len>30)
                    return find(val, "学生姓名输入有误");
                if(!s.isUserName_2(val))
                    return find(val, "学生姓名输入有误");
                saveArr[saveArr.length]=val;
            }
	    }
	    showTableData(saveArr);
	    for(i=0;i<4;i++)
        {
            j=i+1;
            $("#xh_0"+j).show();
            $("#xm_0"+j).show();
        }
	    if(saveArr.length<4)
	    {
	        for(i=4;i>saveArr.length;i--)
	        {
	            $("#xh_0"+i).hide();
	            $("#xm_0"+i).hide();
	        }
	    }
	    sh("none", "block");
	    //提示错误
	    function find(str, title)  
        {
            alert(title);
            if(_tt[0].createTextRange){
                var rng=_tt[0].createTextRange();
                if(rng.findText(str))
                {
　　                rng.select();
　　            }
　　        }
　　        else
　　        {
　　            var s = window.getSelection();
　　            window.find(str);
　　        }
　　        return ;
        }  
	}
	/** 返回 */
	function backFun(){
	    sh("block","none");
	}
	/** 显示隐藏控制 */
	function sh(f1,f2){
	    $("#pInputData").css("display", f1);
	    $("#pShowData").css("display", f2);
	}
	/** 
	 * 显示用户输入信息
	 * param dataArr=["name01","name02"];
	 */
	function showTableData(dataArr){
	    var table=new Share.htmlFun.TableCls();
        table.init("studShow_table", "");
        table.clear();
        var tr, tdArr=[], _sum=0, _i=0;
        for(var i=0,j=dataArr.length;i<j;i++)
        {
            if(i % 4 == 0)
            {
                tr=$("<tr></tr>");
            }
            tdArr[_i]=$('<td width="5%" height="30" align="center" bgcolor="#dfdbdb">'+ (i+1) +'</td>');
            _i++;
            tdArr[_i]=$('<td width="20%" height="30" align="center" bgcolor="#dfdbdb"></td>');
            tdArr[_i].text(dataArr[i]);
            _i++;
            if(i % 4 == 3)
            {
                table.appendTbody(tr, tdArr, true);
                tdArr=[];
                _i=0;
            }
        }
        if(_i != 0)
        {
            table.appendTbody(tr, tdArr, true);
        }
        //table.createEnd();
	}