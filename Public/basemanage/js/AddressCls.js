/***
功能：
    通讯地址ajax功能(有请选择)
描述：
    根据用户选择的省市来做ajax操作
页面数据：
        <select name="select" class="pulldown_menu" id="address_1">
			<option value="-1">请选择省</option>
		</select>
		<select name="select" class="pulldown_menu" id="address_2">
			<option value="-1">请选择市</option>
		</select>
		<select name="select" class="pulldown_menu" id="address_3">
			<option value="-1">请选择县/区</option>
		</select>
		<input type="hidden" name="init_area_id" id="init_area_id" value="'.$area_id.'"/>
		<input type="hidden" name="area_id" id="area_id" value="'.$area_id.'"/>'
使用方法：
    var address;
    window.onload=function(){
        address=new AddressCls();
        address.init();
    }
    //
    var obj=address.getValue();
    //obj={val:array, type:int};
    //val所有值[0]=province值,[1]=city,[2]county;type:到基层(0-2);
    //obj.val[obj.type]:要给服务器的值
    alert( obj.val[0] +"||"+ obj.val[1] +"||"+ obj.val[2] +"||"+obj.type +"||"+obj.val[obj.type]);
    //
    address.validate();//验证用户选择完整性(true为完整/false不完整)
***/
function AddressCls(){this.province="address_1";this.city="address_2";this.county="address_3";this._this=this;}
AddressCls.prototype.init=function(){var _a=AddressCls._addEvent;var t=this._this;_a(t.province,1,t);_a(t.city,2,t);var initVal=$("#init_area_id").val();if(initVal=="")
initVal=0;AddressCls._ajax(initVal,0,t);};AddressCls.prototype.getValue=function(){var obj={};obj.type=-1;obj.val=[];obj.val[0]=$("#"+this.province).val();if(obj.val[0]>0)
obj.type++;obj.val[1]=$("#"+this.city).val();if(obj.val[1]>0)
obj.type++;obj.val[2]=$("#"+this.county).val();if(obj.val[2]>0)
obj.type++;var areaVal=obj.type==-1?-1:obj.val[obj.type];$("#area_id").val(areaVal);return obj;};AddressCls.prototype.setValue=function(province,city,county){if(province!=undefined||province!=-1)
$("#"+this.province).val(province);if(city!=undefined||city!=-1)
$("#"+this.city).val(city);if(county!=undefined||county!=-1)
$("#"+this.county).val(county);};AddressCls.prototype.validate=function(){var obj=this.getValue();var val=obj.val;if(val[0]==-1||val[1]==-1)
return false;if(val[2]==-1)
{var len=$("#"+this.county+" option").length;if(len>1)
return false;}
return true;};AddressCls._addEvent=function(domId,level,t){var obj=$("#"+domId);var val;obj.change(function(){val=obj.val();if(val==-1)
{switch(level)
{case 1:AddressCls.__emptySel(t.city,1);case 2:AddressCls.__emptySel(t.county,2);break;}}
else
AddressCls._ajax(val,level,t);});};AddressCls.__emptySel=function(id,type){$("#"+id).empty().append("<option value='-1' selected='selected'>"+this.__optionTitle(type)+"</option>");};AddressCls.__optionTitle=function(type){var title="";switch(type)
{case 0:title="请选择省";break;case 1:title="请选择市";break;case 2:title="请选择县/区";break;}
return title;};AddressCls._showData=function(data,t,level){this._addSelect(data.province,t.province,0,level);this._addSelect(data.city,t.city,1,level);this._addSelect(data.county,t.county,2,level);};AddressCls._addSelect=function(data,selID,selType,level){var selObj=$("#"+selID);selObj.show();var _option_s="<option value='-1' selected='selected'>"+this.__optionTitle(selType)+"</option>";if(data==undefined||data=="")
{if(selType==2)
{selObj.empty();selObj.append(_option_s);selObj.hide();}
return;}
var _sel=$("<select></select>").append(_option_s);var option,obj,select_s;for(var i in data)
{obj=data[i];if(obj.selected=="")
select_s="";else
select_s="selected='"+obj.selected+"'";option=$("<option value='"+obj.value+"' "+select_s+">"+obj.innerHtml+"</option>");_sel.append(option);}
selObj.html(_sel.html());selObj.css("visibility","hidden");selObj.css("visibility","visible");};

AddressCls._ajax=function(id, level, t){
    var param={};
    param.area_id=id;
    if(level==0)
        param.init=true;
    $.ajax({
        type:"get",
        dataType:"json",
        data:param,
        url:"/Public/Area/getAreaList",
        success:function(json){
            var d=json.error;
            if(d.code>0)
            {
                AddressCls._showData(json.data, t, level);
            }
            if(d.message!="")
                alert(d.message);
        }
    });
    /*
    //test data star
    var json;
    if(level==0)
        json=demoData.get_0();
    else if(level==1)
        json=demoData.get_1();
    else
        json=demoData.get_2_2();
    AddressCls._showData(json.data, t, level);
    //test data end
	*/
};
/**
*
*   ============测试数据===========
*
**/
/*
var demoData={
    get_0:function(){
        var json={
		    error:{
			    code:-1,	//“<0”失败 “>0”成功
	            message:'错误信息'	//错误信息
            },
            data:{
	            province:{
		            0:{
			            value:1,
			            innerHtml:"select option省",
			            selected:""
                    },
                    1:{
                	    value:2,
			            innerHtml:"select省2",
			            selected:"selected"
                    }
                },
                city:{
		            0:{
			            value:1,
			            innerHtml:"select option市",
			            selected:"selected"
                    },
                    1:{
                	    value:2,
			            innerHtml:"select市2",
			            selected:""
                    }
                },
                county:{
		            0:{
			            value:1,
			            innerHtml:"select option区",
			            selected:"selected"
                    },
                    1:{
                	    value:2,
			            innerHtml:"select区2",
			            selected:""
                    }
                }
            }
        };
        return json;
    },
    get_1:function(){
        var json={
		    error:{
			    code:-1,	//“<0”失败 “>0”成功
	            message:'错误信息'	//错误信息
            },
            data:{
                province:"",
                city:{
		            0:{
			            value:1,
			            innerHtml:"get1_select option市",
			            selected:""
                    },
                    1:{
                	    value:2,
			            innerHtml:"get1_select市2",
			            selected:""
                    }
                },
                county:{
		            0:{
			            value:1,
			            innerHtml:"get1_select option区",
			            selected:""
                    },
                    1:{
                	    value:2,
			            innerHtml:"get1_select区2",
			            selected:""
                    }
                }
            }
        };
        return json;
    },
    get_1_2:function(){
        var json={
		    error:{
			    code:-1,	//“<0”失败 “>0”成功
	            message:'错误信息'	//错误信息
            },
            data:{
                province:"",
                city:{
		            0:{
			            value:1,
			            innerHtml:"get1_2_select option市",
			            selected:""
                    },
                    1:{
                	    value:2,
			            innerHtml:"get1_2_select市2",
			            selected:""
                    }
                },
                county:""
            }
        };
        return json;
    },
    get_2:function(){
        var json={
		    error:{
			    code:-1,	//“<0”失败 “>0”成功
	            message:'错误信息'	//错误信息
            },
            data:{
                province:"",
                city:"",
                county:{
		            0:{
			            value:1,
			            innerHtml:"get2_select option区",
			            selected:""
                    },
                    1:{
                	    value:2,
			            innerHtml:"get2_select区2",
			            selected:""
                    }
                }
            }
        };
        return json;
    },
    get_2_2:function(){
        var json={
		    error:{
			    code:-1,	//“<0”失败 “>0”成功
	            message:'错误信息'	//错误信息
            },
            data:{
                province:"",
                city:"",
                county:""
            }
        };
        return json;
    },
    getTestData:function(){
        var json={"error":{"code":1,"message":""},"data":{"province":{"34":{"value":34000000,"innerHtml":"\u5b89\u5fbd","selected":""},"11":{"value":11000000,"innerHtml":"\u5317\u4eac","selected":"selected"},"50":{"value":50000000,"innerHtml":"\u91cd\u5e86","selected":""},"35":{"value":35000000,"innerHtml":"\u798f\u5efa","selected":""},"62":{"value":62000000,"innerHtml":"\u7518\u8083","selected":""},"44":{"value":44000000,"innerHtml":"\u5e7f\u4e1c","selected":""},"45":{"value":45000000,"innerHtml":"\u5e7f\u897f","selected":""},"52":{"value":52000000,"innerHtml":"\u8d35\u5dde","selected":""},"46":{"value":46000000,"innerHtml":"\u6d77\u5357","selected":""},"13":{"value":13000000,"innerHtml":"\u6cb3\u5317","selected":""},"23":{"value":23000000,"innerHtml":"\u9ed1\u9f99\u6c5f","selected":""},"41":{"value":41000000,"innerHtml":"\u6cb3\u5357","selected":""},"42":{"value":42000000,"innerHtml":"\u6e56\u5317","selected":""},"43":{"value":43000000,"innerHtml":"\u6e56\u5357","selected":""},"15":{"value":15000000,"innerHtml":"\u5185\u8499\u53e4","selected":""},"32":{"value":32000000,"innerHtml":"\u6c5f\u82cf","selected":""},"36":{"value":36000000,"innerHtml":"\u6c5f\u897f","selected":""},"22":{"value":22000000,"innerHtml":"\u5409\u6797","selected":""},"21":{"value":21000000,"innerHtml":"\u8fbd\u5b81","selected":""},"64":{"value":64000000,"innerHtml":"\u5b81\u590f","selected":""},"63":{"value":63000000,"innerHtml":"\u9752\u6d77","selected":""},"14":{"value":14000000,"innerHtml":"\u5c71\u897f","selected":""},"37":{"value":37000000,"innerHtml":"\u5c71\u4e1c","selected":""},"31":{"value":31000000,"innerHtml":"\u4e0a\u6d77","selected":""},"51":{"value":51000000,"innerHtml":"\u56db\u5ddd","selected":""},"12":{"value":12000000,"innerHtml":"\u5929\u6d25","selected":""},"54":{"value":54000000,"innerHtml":"\u897f\u85cf","selected":""},"65":{"value":65000000,"innerHtml":"\u65b0\u7586","selected":""},"53":{"value":53000000,"innerHtml":"\u4e91\u5357","selected":""},"33":{"value":33000000,"innerHtml":"\u6d59\u6c5f","selected":""},"61":{"value":61000000,"innerHtml":"\u9655\u897f","selected":""},"71":{"value":71000000,"innerHtml":"\u53f0\u6e7e","selected":""},"81":{"value":81000000,"innerHtml":"\u9999\u6e2f","selected":""},"82":{"value":82000000,"innerHtml":"\u6fb3\u95e8","selected":""},"400":{"value":400000000,"innerHtml":"\u6d77\u5916","selected":""},"100":{"value":100000000,"innerHtml":"\u5176\u4ed6","selected":""}},"city":{"1":{"value":11001000,"innerHtml":"\u4e1c\u57ce\u533a","selected":""},"2":{"value":11002000,"innerHtml":"\u897f\u57ce\u533a","selected":""},"3":{"value":11003000,"innerHtml":"\u5d07\u6587\u533a","selected":""},"4":{"value":11004000,"innerHtml":"\u5ba3\u6b66\u533a","selected":""},"5":{"value":11005000,"innerHtml":"\u671d\u9633\u533a","selected":""},"6":{"value":11006000,"innerHtml":"\u4e30\u53f0\u533a","selected":""},"7":{"value":11007000,"innerHtml":"\u77f3\u666f\u5c71\u533a","selected":""},"8":{"value":11008000,"innerHtml":"\u6d77\u6dc0\u533a","selected":"selected"},"9":{"value":11009000,"innerHtml":"\u95e8\u5934\u6c9f\u533a","selected":""},"11":{"value":11011000,"innerHtml":"\u623f\u5c71\u533a","selected":""},"12":{"value":11012000,"innerHtml":"\u901a\u5dde\u533a","selected":""},"13":{"value":11013000,"innerHtml":"\u987a\u4e49\u533a","selected":""},"14":{"value":11014000,"innerHtml":"\u660c\u5e73\u533a","selected":""},"15":{"value":11015000,"innerHtml":"\u5927\u5174\u533a","selected":""},"16":{"value":11016000,"innerHtml":"\u6000\u67d4\u533a","selected":""},"17":{"value":11017000,"innerHtml":"\u5e73\u8c37\u533a","selected":""},"28":{"value":11028000,"innerHtml":"\u5bc6\u4e91\u53bf","selected":""},"29":{"value":11029000,"innerHtml":"\u5ef6\u5e86\u53bf","selected":""}},"county":""}};
        return json;
    }
};
*/