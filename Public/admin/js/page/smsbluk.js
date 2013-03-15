function smsBlukCls() {
	this.load();
	this.showTime();
}
smsBlukCls.prototype.load=function() {
	var self = this;
	if($("#date_ymd").val()) {
		$("#set_time").attr({checked: "checked"});
		self.viewdate();
		$("#list_time").val($("#date_ymd").val());
		$("#hour_time").val($("#date_h").val());
		$("#sec_time").val($("#date_i").val());
	}
};
smsBlukCls.prototype.showTime=function() {
	var self = this;
	$("#set_time").bind('click',function() {
		self.viewdate();
	});
};
smsBlukCls.prototype.viewdate=function() {
	if($("#set_time").attr("checked"))     
	{     
//		<input type="text" id="list_time" name="list_time"/>
//		<select id="hour_time" name="hour_time">
//			<option>10</option>
//		</select>
//		时
//		<select id="sec_time" name="sec_time">
//			<option>0</option>
//			<option>30</option>
//		</select>
//		分
		var str, i=1, option1;
		str = "<input type='text' id='list_time' name='list_time' readonly/>" +
		"<select id='hour_time' name='hour_time' style='width:50px;margin:0px 10px 0px 10px;'>" ;
		var today = new Date();
		for(i=1;i<25;i++) {
			str += "<option value="+i+">"+i+"</option>"
		}
		str += "</select><span>时</span>"+
			   "<select id='sec_time' name='sec_time' style='width:50px;height:'>";
	    for(i=0;i<2;i++) {
	    	option1 = i*30;
			str += "<option value="+option1+">"+option1+"</option>";
		}		
	    str+="</select><span>分</span>";
		$("#show_set_time").html(str);
		$("#list_time").bind('click',function() {
			WdatePicker({minDate:'%y-%M-%d'});
		});
		$('#show_set_time').show();
	}else{
		$('#show_set_time').html('');
		$('#show_set_time').hide();
	}     
}
$(document).ready(function(){
	new smsBlukCls();
});