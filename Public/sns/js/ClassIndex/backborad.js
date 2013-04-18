function backborad() {
	this.course_cache = [];
	this.getcourseinfo();
	this.init();
	this.getclassnotice();
};

backborad.prototype.init = function(){
	$("#today").trigger("click");
};

backborad.prototype.getcourseinfo = function() {
	var me = this;
	var class_code = $("#class_code").val();
	var context = $("#backborad");
	$("#today,#tommor", context).click(function(){
		var myDate = new Date();
		if(this.id.toString() == 'today') {
			var weekday = myDate .getDay();
		}else{
			var weekday = myDate .getDay();
			if(weekday == 6) {
				weekday = 0;
			}else{
				weekday = parseInt(weekday) + parseInt(1);
			}
		}
		var cache_key = class_code + ":" + weekday;
		var cache_datas = me.course_cache[cache_key] || {};
		$(this).addClass('tommor_bj').siblings().removeClass('tommor_bj');
		
		if($.isEmptyObject(cache_datas)){
			$.ajax({
				type:'get',
				url:'/Api/Class/Course/getCourse/class_code/'+ class_code +'/weekday/'+ weekday,
				dataType:'json',
				async:false,
				success:function(json) {
					cache_datas = me.course_cache[cache_key] = json.data || {};
				}
			});
		}
		
		if(!$.isEmptyObject(cache_datas)){
			$("p:last", context).hide();
			$("table", context).show();
			me.fillcourseinfo(cache_datas);
		}else{
			$("table", context).hide();
			$("p:last", context).show();
		}
	});
};

backborad.prototype.fillcourseinfo = function(data){
	var context = $("#backborad");
	var am_course = data[0];
	var pm_course = data[1];
	var max_len = 6;
	var tr_str_1 = "<tr>";
	var tr_str_2 = "<tr>";
	
	for(var i in am_course){
		var course_name = am_course[i].name || '';
		course_name = course_name.length > max_len ? course_name.substring(0, max_len) + " .." : course_name;
		var course = $.isEmptyObject(am_course[i])? "暂无" : course_name;
		tr_str_1 += "<td>"+ i +"</td>";
		tr_str_2 += "<td>"+ course +"</td>";
	}
	for(var i in pm_course){
		var course_name = pm_course[i].name || '';
		course_name = course_name.length > max_len ? course_name.substring(0, max_len) + " .." : course_name;
		var course = $.isEmptyObject(pm_course[i])? "暂无" : course_name;
		tr_str_1 += "<td>"+ i +"</td>";
		tr_str_2 += "<td>"+ course +"</td>";
	}
	
	tr_str_1 += "</tr>";
	tr_str_2 += "</tr>";
	$("table", context).html("").append(tr_str_1 + tr_str_2);
};

backborad.prototype.getclassnotice = function() {
	var class_code = $("#class_code").val();
	var me = this;
	var context = $("div[class='black_left']", $("#backborad"));
	$.ajax({
		type:'get',
		url:'/Api/Class/Notice/getLastNoticeByClassCode/class_code/'+ class_code,
		dataType:'json',
		async:true,
		success:function(json) {
			if(json.status > 0) {
				$("p:first", context).html(json.data.notice_content);
				$("p:last", context).html(json.data.add_time);
			}
		}
	});
};


$(function(){
	new backborad();
});