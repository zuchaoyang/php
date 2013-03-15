$(function(){
	//删除快速记事
	$("#delschedule").click(function(){
		var schedule_id = $("#schedule_id").val();
		var schedule_type = $("#schedule_old_type").val();
		var param={};
		if(schedule_id == "") {
			alert("删除失败!");
			return false;
		}
		param.schedule_id = schedule_id;
		if(window.confirm("你确定要将此日程删除吗？")){
			$(function() {
				$.ajax( {
					type : "POST",
					url : "/Oa/Schedulemanage/delSchedule",
					dataType : "json",
					data : param,
					success : function(jsonarr) {
						alert(jsonarr.error.message);
						if(jsonarr.error.code > 0){
							window.location = "/Oa/Index/getScheduleOrTaskbyType/type/0/show_type/"+schedule_type;
						}
					}
				});
			});
		}
	});
	
	$("#returen_list").click(function(){
		$("#re_list_form").submit();
	});
	
	
	
	//搜索结果分页
	$("#show_more_search").click(function(){
		var schedule_name = $("#schedule_name_json").val();
		var page = $("#page_json").val();
		var param = {};
		param.schedule_name = schedule_name;
		param.page = page;
		$.ajax({
			type : "POST",
			url : "/Oa/Schedulemanage/jsonsearchScheduleinfo",
			dataType : "json",
			data : param,
			success : function(jsonarr) {
				var data = jsonarr.data.mSchedule;
				var is_end_page = jsonarr.data.is_end_page;
				var err = jsonarr.error;
				if(err.code < 0){
					alert(jsonarr.error.message);
					return false;
				}else{
					var searche_content = $("#searche_content");
					for(var key in data) {
						$("<tr><th><a href='javascript:showinfo("+key+");'>"+data[key].schedule_title+"</a></th><td>上次编辑时间："+data[key].upd_time+"</td></tr>").appendTo(searche_content);
						$("<form id='myform_"+key+"' action='/Oa/Schedulemanage/serarchshowScheduleinfo' method='post'></form>").appendTo(searche_content);
						var myform = $("#myform_"+key);
						$("<input type='hidden' name='schedule_id' value='"+key+"'/>").appendTo(myform);
						$("<input type='hidden' name='schedule_name' value='"+jsonarr.data.schedule_name+"'/>").appendTo(myform);
					}
					var new_page = parseInt(page)+1;
					$("#page_json").val(new_page);
					if(is_end_page != ""){
						$("#show_more_search").hide();
					}
				}
			}
		});
	});
	
	//保存为草稿
	$("#savetodraft").click(function(){
		var schedule_title = $("#schedule_title").val();
		var schedule_message = $("#schedule_message").val();
		if(schedule_title == "" || schedule_title == "输入标题内容..."){
			alert("请输入日程标题！");
			return false;
		}
		
		if(schedule_message == ""){
			alert("请输入日程内容！");
			return false;
		}
		
		
		if(window.confirm("附加信息将不会被保存！你确定要将此日程保存为草稿吗？")){
			var param = {};
			param.draft_id = $("#draft_id").val();
			param.schedule_type = $("#schedule_type_info").val();
			param.schedule_title = schedule_title;
			param.schedule_message = schedule_message;
			$.ajax( {
				type : "POST",
				url : "/Oa/Schedulemanage/saveScheduleToDraft",
				dataType : "json",
				data : param,
				success : function(jsonarr) {
					alert(jsonarr.error.message);
//					if(jsonarr.error.code>0){
//						
//					}
					
				}
			});
		}
	});
	
	//修改日程信息
	$("#modify_btn").click(function(){
		var schedule_title = $("#schedule_title").val();
		var schedule_message = $("#schedule_message").val();
		var is_time = 0;
		var is_hours = 0;
		if(schedule_title == "" || schedule_title == "输入标题内容..."){
			alert("请输入日程标题！");
			return false;
		}
		
		if(schedule_title.length > 20) {
			alert("日程标题不能大于20个字符！");
			return false;
		}
		
		if(schedule_message == ""){
			alert("请输入日程内容！");
			return false;
		}
		
		if(schedule_message.length>10000){
			alert("日程内容不能超过10000字！");
			return false;
		}
		
		var nyr = $("#datestr").val();
		if($("#fbxgz_yi").attr("checked") && nyr == ""){
			alert("请选择日程到期日期！");
			return false;
		}else if($("#fbxgz_yi").attr("checked") && nyr != ""){
			var myDate = new Date();
			var year = myDate.getFullYear(); // 获取完整的年份(4位,1970-????)
			var math = myDate.getMonth() + 1; // 获取当前月份(0-11,0代表1月)
			var date = myDate.getDate(); // 获取当前日(1-31)
			var hour = myDate.getHours(); // 获取当前小时数(0-23)
			var min = myDate.getMinutes(); // 获取当前分钟数(0-59)
			if (math <= 9)
				math = "0" + math;
			if (date <= 9)
				date = "0" + date;
			if (hour == 0)
				hour = 12;
			if (hour <= 9)
				hour = "0" + hour;
			if (min <= 9)
				min = "0" + min;
			var str = year+math+date;
			var new_nyr = nyr.split("-");
			var new_nyr_str = "";
			for(var key in new_nyr){
				new_nyr_str += new_nyr[key];
			}
			if(new_nyr_str<str){
				alert("请选择有效的日程到期日期！");
				return false;
			}
			is_time = 1;
			
		}
		
		if($("#fbxgz_yi").attr("checked") && $("#is_message").attr("checked") && $("#remind_hours").val() == ""){
			alert("请选择短信提醒时间！");
			return false;
		}else if($("#fbxgz_yi").attr("checked") && $("#is_message").attr("checked") && $("#remind_hours").val() != ""){
			var myDate = new Date();
			var remind_hours = $("#remind_hours").val();
			myDate.setHours(remind_hours);
			var year = myDate.getFullYear(); // 获取完整的年份(4位,1970-????)
			var math = myDate.getMonth() + 1; // 获取当前月份(0-11,0代表1月)
			var date = myDate.getDate(); // 获取当前日(1-31)
			var xingqi = myDate.getDay() + 1; // 获取当前周X(0-6,0代表周天)
			var hour = myDate.getHours(); // 获取当前小时数(0-23)
			var min = myDate.getMinutes(); // 获取当前分钟数(0-59)
			var seconds = myDate.getSeconds(); // 获取当前秒数(0-59)
			var push_time = year+"-"+math+"-"+date+" "+hour+":"+min+":"+seconds;
			is_hours = 1;
		}
		
		if(window.confirm("你确定要修改并发布此日程吗？")){
			var param = {};
			param.draft_id = $("#draft_id").val();
			param.schedule_id = $("#schedule_id").val();
			param.schedule_type = $("#schedule_type_info").val();
			param.schedule_title = schedule_title;
			param.schedule_message = schedule_message;
			param.expiration_time = $("#datestr").val();
			param.deadline_hours = $("#remind_hours").val();
			param.is_time = is_time;
			param.is_hours = is_hours;
			param.push_time = push_time;
			$.ajax( {
				type : "POST",
				url : "/Oa/Schedulemanage/modifySchedule_info",
				dataType : "json",
				data : param,
				success : function(jsonarr) {
					alert(jsonarr.error.message);
					if(jsonarr.error.code > 0){
						location.href = "/Oa/Schedulemanage/Schedeule_list_info";
					}
				}
			});
		}
	});
	
	//添加日程
	$("#zjfb_btn").click(function(){
		var schedule_title = $("#schedule_title").val();
		var schedule_message = $("#schedule_message").val();
		if(schedule_title == "" || schedule_title == "输入标题内容..."){
			alert("请输入日程标题！");
			return false;
		}
		
		if(schedule_title.length > 20) {
			alert("日程标题不能大于20个字符！");
			return false;
		}
		
		if(schedule_message == ""){
			alert("请输入日程内容！");
			return false;
		}
		if(schedule_message.length>10000){
			alert("日程内容不能超过10000字！");
			return false;
		}
		
		var nyr = $("#datestr").val();
		if($("#fbxgz_yi").attr("checked") && nyr == ""){
			alert("请选择日程到期日期！");
			return false;
		}else if($("#fbxgz_yi").attr("checked") && nyr != ""){
			var myDate = new Date();
			var year = myDate.getFullYear(); // 获取完整的年份(4位,1970-????)
			var math = myDate.getMonth() + 1; // 获取当前月份(0-11,0代表1月)
			var date = myDate.getDate(); // 获取当前日(1-31)
			var hour = myDate.getHours(); // 获取当前小时数(0-23)
			var min = myDate.getMinutes(); // 获取当前分钟数(0-59)
			if (math <= 9)
				math = "0" + math;
			if (date <= 9)
				date = "0" + date;
			if (hour == 0)
				hour = 12;
			if (hour <= 9)
				hour = "0" + hour;
			if (min <= 9)
				min = "0" + min;
			var str = year+math+date;
			var new_nyr = nyr.split("-");
			var new_nyr_str = "";
			for(var key in new_nyr){
				new_nyr_str += new_nyr[key];
			}
			if(new_nyr_str<str){
				alert("过期时间小于系统当前时间，请重新选择！");
				return false;
			}
			
		}
		
		if($("#fbxgz_yi").attr("checked") && $("#is_message").attr("checked") && $("#remind_hours").val() == ""){
			alert("请选择短信提醒时间！");
			return false;
		}else if($("#fbxgz_yi").attr("checked") && $("#is_message").attr("checked") && $("#remind_hours").val() != ""){
			var myDate = new Date();
			var hour = myDate.getHours(); 
			var remind_hours = $("#remind_hours").val()+hour;
			myDate.setHours(remind_hours);
			var year = myDate.getFullYear(); // 获取完整的年份(4位,1970-????)
			var month = myDate.getMonth() + 1; // 获取当前月份(0-11,0代表1月)
			var date = myDate.getDate(); // 获取当前日(1-31)
			var hour = myDate.getHours(); // 获取当前小时数(0-23)
			var min = myDate.getMinutes(); // 获取当前分钟数(0-59)
			var seconds = myDate.getSeconds(); // 获取当前秒数(0-59)
			var push_time = year+"-"+math+"-"+date+" "+hour+":"+min+":"+seconds;
		}
		
		if(window.confirm("你确定要发布此日程吗？")){
			var param = {};
			param.schedule_id = $("#draft_id").val();
			param.schedule_type = $("#schedule_type_info").val();
			param.schedule_title = schedule_title;
			param.schedule_message = schedule_message;
			param.expiration_time = $("#datestr").val();
			param.deadline_hours = $("#remind_hours").val();
			param.push_time = push_time;
			$.ajax( {
				type : "POST",
				url : "/Oa/Schedulemanage/addSchedule_info",
				dataType : "json",
				data : param,
				success : function(jsonarr) {
					alert(jsonarr.error.message);
					if(jsonarr.error.code > 0){
						top.location.href = "/Oa/Schedulemanage/Schedeule_list_info";
					}
				}
			});
		}
	});
	
	
	//修改日程类型
	$("#change_type").click(function(){
		var scheduletype = $("#schedule_type").val();
		var scheduleid = $("#schedule_id").val();
		var schedule_old_type = $("#schedule_old_type").val();
		var param = {};
		param.schedule_id = scheduleid;
		param.schedule_type = scheduletype;
		if(schedule_old_type == scheduletype){
			alert("修改成功!");
			$("#popDiv1,#popIframe1,#bg1").hide();
			return false;
		}
		$(function() {
			$.ajax( {
				type : "POST",
				url : "/Oa/Schedulemanage/changescheduleType",
				dataType : "json",
				data : param,
				success : function(jsonarr) {
					alert(jsonarr.error.message);
					if(jsonarr.error.code > 0){
						$("#schedule_old_type").val(scheduletype);
						$("#popDiv1,#popIframe1,#bg1").hide();
					}
				}
			});
		});
	});
	
	$("#datestr").click(function(){
		WdatePicker();
		var system_date = $("#datestr").val();
		var remind_hours = $("#remind_hours").val();
		var remind_str = system_date+" "+(24-parseInt(remind_hours))+":00";
		$("#remind_str").text(remind_str);
		
	});
	
	$("#fbxgz_yi").click(function(){
		if(this.checked) {
			$("#fbxgz_er").show();
			$("#datestr").show();
		}else{
			$("#is_message").attr("checked",false);
			$("#sms_remind").hide();
			$("#fbxgz_er").hide();
			$("#datestr").hide();
		}
	});
	
	$("#is_message").click(function(){
		if(this.checked){
			var datestr = $("#datestr").val();
			if(datestr == "") { 
				alert("请选择到期日期！");
				$("#is_message").attr("checked",false);
				return false;
			}
			$("#sms_remind").css("display","block");
			$("#remind_str").css("display","block");
			var system_date = $("#datestr").val();
			var remind_str = system_date+" "+18+":00";
			$("#remind_str").text(remind_str);
		}else{
			$("#sms_remind").css("display","none");
			$("#remind_str").css("display","none");
		}
	});
	
	$("#datestr").focus(function(){
		if($("#is_message").attr("checked")) {
			var system_date = $("#datestr").val();
			var remind_hours = $("#remind_hours").val();
			var remind_str = system_date+" "+(24-parseInt(remind_hours))+":00";
			$("#remind_str").text(remind_str);
		}
	});
	
	$("#remind_hours").change(function(){
		if($("#is_message").attr("checked")){
			var system_date = $("#datestr").val();
			var date_arr = system_date.toString().split('-');
			var system_date = $("#datestr").val();
			var remind_hours = $("#remind_hours").val();
			var remind_str = system_date+" "+(24-parseInt(remind_hours))+":00";
			$("#remind_str").text(remind_str);
		}
	});
	
	//提取个人日程草稿信息
	$("#getdraftinfo").click(function(){
		var type_name = $("#schedule_type_info option:selected").text();
		var type_id = $("#schedule_type_info").val();
		var page = 1;
		var param = {};
		var old_type_id = $("#type_id").val();
		var content = $("#draft_info").val();
		$("#draft_info").text("");
		param.type_id = type_id;
		param.page = page;
			$.ajax({
				type : "POST",
				url : "/Oa/Schedulemanage/getScheduleDraftInfo",
				dataType : "json",
				data : param,
				success : function (json) {
					var err = json.error;
					if(err.code > 0){
						var data = json.data.draftinfo;
						var schedule_type_name = json.data.schedule_type_name;
						$("#schedule_type_name").text(type_name);
						var draft_info = $("#draft_info");
						for(var key in data){
							$("<p id='draft_list_"+key+"'></p>").appendTo(draft_info);
							var draft_list = $("#draft_list_"+key);
							$("<input type='radio' name='xz' id='xz_"+key+"' value='"+key+"' />&nbsp;").appendTo(draft_list);
							$("<span class='fontwidth' id='fontwidth_"+key+"'>"+data[key].schedule_title+"</span>").appendTo(draft_list);
							$("<span class='font_hui' id='font_hui_"+key+"'>上次编辑时间："+data[key].upd_time+"</span>").appendTo(draft_list);
							$("<input type='hidden' id='draft_schedule_title_"+key+"' value='"+data[key].schedule_title+"'/>").appendTo(draft_list);
							$("<input type='hidden' id='draft_schedule_message_"+key+"' value='"+data[key].schedule_message+"'/>").appendTo(draft_list);
						}
						$("#type_id").val(type_id);
						var new_page = 2;
						$("#page").val(new_page);
						var is_end_page = json.data.is_end_page;
						if(is_end_page != ""){
							$("#show_more").hide();
						}else{
							$("#show_more").show();
						}
						$("#popDiv,#popIframe,#bg").show();
					}else{
						alert(err.message);
						var old_type_id = $("#type_id").val(0);
					}
				}
				
			});
	});
	
	//提取个人日程草稿信息分页显示更多
	$("#show_more").click(function(){
		var type_id = $("#schedule_type_info").val();
		var page = $("#page").val();
		var param = {};
		param.type_id = type_id;
		param.page = page;
			$.ajax({
				type : "POST",
				url : "/Oa/Schedulemanage/getScheduleDraftInfo",
				dataType : "json",
				data : param,
				success : function (json) {
					var err = json.error;
					if(err.code>0){
						var data = json.data.draftinfo;
						var schedule_type_name = json.data.schedule_type_name;
						var draft_info = $("#draft_info");
						for(var key in data){
							var pid = "draft_list_" + key;
							$("<p id='" + pid + "'></p>").appendTo(draft_info);
							var draft_list = $("#" + pid);
							$("<input type='radio' name='xz' id='xz_"+key+"' value='"+key+"' />").appendTo(draft_list);
							$("<span id='fontwidth_" + key + "'>"+data[key].schedule_title+"</span>").addClass('fontwidth').appendTo(draft_list);
							$("<span id='font_hui_" + key + "'>上次编辑时间："+data[key].upd_time+"</span>").addClass('font_hui').appendTo(draft_list);
							
							$("<input type='hidden' id='draft_schedule_title_"+key+"' value='"+data[key].schedule_title+"' />").appendTo(draft_list);
							$("<input type='hidden' id='draft_schedule_message_"+key+"' value='"+data[key].schedule_message+"' />").appendTo(draft_list);
							$("<input type='hidden' id='draft_expiration_time_"+key+"' value='"+data[key].expiration_time+"' />").appendTo(draft_list);
							$("<input type='hidden' id='draft_deadline_hours_"+key+"' value='"+data[key].deadline_hours+"' />").appendTo(draft_list);
						}
						var new_page = parseInt(page)+1;
						var is_end_page = json.data.is_end_page;
						if(is_end_page != ""){
							$("#show_more").hide();
						}else{
							$("#show_more").show();
						}
						$("#page").val(new_page);
					}else{
						alert(err.message);
					}
				}
			});
	});
	
	//回显草稿信息
	$("#get_draft_info").click(function(){
		var xz = $(":input[name='xz']:checked").val();
		var schedule_title = $("#draft_schedule_title_"+xz).val();
		var schedule_message = $("#draft_schedule_message_"+xz).val();
		var expiration_time = $("#draft_expiration_time_"+xz).val();
		var deadline_hours = $("#draft_deadline_hours_"+xz).val();
		$("#schedule_title").val(schedule_title);
		var schedule_id = $("#draft_id").val(xz);
		$.extend(xheditor.settings,{shortcuts:{'ctrl+enter':submitForm}});
		var a = $('#schedule_message').xheditor({skin:'vista',tools:'Separator,BtnBr,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,Align,List,Outdent,Indent,Link,Unlink,Emot'});
		a.setSource(schedule_message);
		//xheditor.setSource(schedule_message);
		$("draft_id").val(xz);
		$("#popDiv,#popIframe,#bg").hide();
		
		$("#fbxgz_yi").attr("checked",false);
		$("#fbxgz_er").attr("checked",false);
		$("#datestr").val("");
		$("#datestr").hide();
		$("#fbxgz_er").hide();
		$("#remind_hours").val(6);
		$("#remind_str").text("");
		
	});
	
	$("#hidedraftinfo,#exitfraftinfo").click(function(){
		$("#popDiv,#popIframe,#bg").hide();
		$("#schedule_type_name").text("");
	});
		
	
	//修改日程分类
	$("#modifyscheduletype").click(function(){
		var schedule_type_info = $("#schedule_type_info").text();
		var schedule_old_type = $("#schedule_old_type").val();
		if(schedule_type_info == ""){
			$.ajax( {
				type : "POST",
				url : "/Oa/Schedulemanage/showSchedule_type",
				dataType : "json",
				success : function(json) {
					var err = json.error;
					var data = json.data;
					if(err.code > 0){
						$("<select name='schedule_type' id='schedule_type'></select>").appendTo($('#schedule_type_info')); 
						var selectObj = $('#schedule_type');
						for(var key in data){
							$('<option value="' + data[key].type_id + '">' + data[key].type_name + '</option>').appendTo(selectObj);
						}
						$("#schedule_type").val(schedule_old_type);
					} else if(err.message != '') {
						alert(err.message);
					}
				}
			});
		}
		$("#popDiv1,#popIframe1,#bg1").show();
	});
});

function showinfo(id){
	$("#myform_"+id).submit();
}