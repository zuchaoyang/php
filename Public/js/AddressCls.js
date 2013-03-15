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
	function AddressCls(){
		this.province="address_1";
		this.city="address_2";
		this.county="address_3";
		this._this=this;
	}
	AddressCls.prototype.init=function(){
		var _a=AddressCls._addEvent;
		var t=this._this;
			_a(t.province,1,t);
			_a(t.city,2,t);
		var initVal=$("#init_area_id").val();
		if(initVal=="")
			initVal=0;
		AddressCls._ajax(initVal,0,t);
	};
	AddressCls.prototype.getValue=function(){
		var obj={};
		obj.type=-1;
		obj.val=[];
		obj.val[0]=$("#"+this.province).val();
		if(obj.val[0]>0)
			obj.type++;
			obj.val[1]=$("#"+this.city).val();
		if(obj.val[1]>0)
			obj.type++;
			obj.val[2]=$("#"+this.county).val();
		if(obj.val[2]>0)
			obj.type++;
		var areaVal=obj.type==-1?-1:
			obj.val[obj.type];
		$("#area_id").val(areaVal);
		return obj;
	};
	AddressCls.prototype.setValue=function(province,city,county){
		if(province!=undefined||province!=-1)
			$("#"+this.province).val(province);
		if(city!=undefined||city!=-1)
			$("#"+this.city).val(city);
		if(county!=undefined||county!=-1)
			$("#"+this.county).val(county);
	};
	AddressCls.prototype.validate=function(){
		var obj=this.getValue();
		var val=obj.val;
		if(val[0]==-1||val[1]==-1)
			return false;
		if(val[2]==-1){
			var len=$("#"+this.county+" option").length;
			if(len>1)
				return false;
		}
			return true;
	};
	AddressCls._addEvent=function(domId,level,t){
		var obj=$("#"+domId);
		var val;
		obj.change(function(){
			val=obj.val();
			if(val==-1){
				switch(level){
					case 1:AddressCls.__emptySel(t.city,1);
					case 2:AddressCls.__emptySel(t.county,2);
						   break;
				}
			}else
				AddressCls._ajax(val,level,t);
		});
	};
	AddressCls.__emptySel=function(id,type){
		$("#"+id).empty().append("<option value='-1' selected='selected'>"+this.__optionTitle(type)+"</option>");
	};
	AddressCls.__optionTitle=function(type){
		var title="";
		switch(type){
			case 0:title="请选择省";
				break;
			case 1:title="请选择市";
				break;
			case 2:
				title="请选择县/区";
				break;
		}
		return title;
	};
	AddressCls._showData=function(data,t,level){
		this._addSelect(data.province,t.province,0,level);
		this._addSelect(data.city,t.city,1,level);
		this._addSelect(data.county,t.county,2,level);
	};
	AddressCls._addSelect=function(data,selID,selType,level){
		var selObj=$("#"+selID);
		selObj.show();
		var _option_s="<option value='-1' selected='selected'>"+this.__optionTitle(selType)+"</option>";
		if(data==undefined||data==""){
			if(selType==2){
				selObj.empty();
				selObj.append(_option_s);
				selObj.hide();
			}
			return;
		}
		var _sel=$("<select></select>").append(_option_s);
		var option,obj,select_s;
		for(var i in data){
			obj=data[i];
			if(obj.selected=="")
				select_s="";
			else
				select_s="selected='"+obj.selected+"'";
			option=$("<option value='"+obj.value+"' "+select_s+">"+obj.innerHtml+"</option>");
			_sel.append(option);
		}
		selObj.html(_sel.html());
		selObj.css("visibility","hidden");
		selObj.css("visibility","visible");
	};

AddressCls._ajax=function(id, level, t){
    var param={};
    param.area_id=id;
    if(level==0) {
        param.init=true;
    }
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
};