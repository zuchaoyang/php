var popWin={
        _lr:Share.strProcess.trimLR,
        _saveData:null,
        /*判断信息*/
        judge:function(jqObj){
            var val = this._lr(jqObj.val());
            if( val.length == 0 )
            {
                alert("请输入职务名");
                jqObj.focus();
                return false;
            }
            return true;
        },
        open:function(){
            this.select();
            $('#popDiv1').show();
            $('#popIframe1').show();
            $('#bg1').show();
        },
        select:function(){
            if(this._saveData==null)
                return ;
            var i, j=this._saveData.length, obj;
            for(i=0; i<j; i++)
            {
                obj=this._saveData[i];
                $("#popDiv_inp_"+obj.val).attr("checked","checked");
                $("#popDiv_inpVal_"+obj.val).val(obj.position);
            }
        },
        close:function(){
            $('#popDiv1').hide();
            $('#popIframe1').hide();
            $('#bg1').hide();
            this.clear();
        },
        clear:function(){
            $("input[@name='popDiv_inp']").each(function(i, o){
                $(o).attr("checked","");
                $("#popDiv_inpVal_"+$(o).val()).val("");
            });
        },
        save:function(){
            var _arr=$("input[@name='popDiv_inp']:checked");
            if(_arr.length==0)
            {
                alert("请选择中队委员");
                return ;
            }
            var obj, val, saveObj=[], tf=true;
            _arr.each(function(i,o){
                obj={};
                val=$(o).val();
                obj.show=$("#popDiv_td_"+val).text();
                obj.val=val;
                obj.position=popWin._lr($("#popDiv_inpVal_"+val).val());
                if(!popWin.judge($("#popDiv_inpVal_"+val)))
                {
                    tf=false;
                    return false;
                }
                saveObj[saveObj.length]=obj;
            });
            if(tf)
            {
                this._saveData=saveObj;
                this.createShow();
                this.close();
            }
        },
        createShow:function(){
            var t=new Share.htmlFun.TableCls();
            t.init("showTbody");
            t.clear();
            var i,j,obj, tr, tdArr=[], td_span, td_1, td_2;
            var sumbitObj=[], o;
            for(i=0,j=this._saveData.length;i<j;i++)
            {
                obj=this._saveData[i];
                tr=$("<tr></tr>");
                tdArr[0]=$('<td height="30" align="center" bgcolor="#fbf6f7"></td>');
                tdArr[0].text(obj.show);
                tdArr[1]=$('<td align="center" bgcolor="#fbf6f7"></td>');
                td_span=$("<span></span>"); td_span.text(obj.position);
                tdArr[1].append(td_span);
                t.appendTbody(tr, tdArr, true);
                o={};
                o.wmw_uid=obj.val;
                o.duties_name=obj.position;
                sumbitObj[sumbitObj.length]=o;
            }
            $("#positionArr").val(Share.jsonProcess.toJSON(sumbitObj));
        }
    };