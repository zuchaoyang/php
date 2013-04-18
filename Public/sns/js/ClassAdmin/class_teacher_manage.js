function class_teacher_manage(){
	this.selected();
	this.init();
	this.teacher_del();
	this.teacher_select();
	this.teacher_add();
	this.to_change_teacher();
};

class_teacher_manage.prototype.init = function(){
	this.teacher_cache = {};//教师缓存处理
	
	//操作老师存储
	this.data_json = {};
	this.data_json.del = {};
	this.data_json.add = {};
	this.class_code = $('#class_code').val();
	this.selected_subject_id = 0;
	this.onload_selected();
};

class_teacher_manage.prototype.onload_selected = function(){
	$("a:first", $("#subject_list")).trigger('click');
};

class_teacher_manage.prototype.selected=function(){
	var me = this;
	//远程请求教师数据
	$("a[id^='subject_id_']", $("#subject_list")).click(function(){
		var subject_id = (this.id.toString().match(/(\d+)/) || [])[1];
		me.selected_subject_id = subject_id;
		var cache_key = subject_id + ":" + class_code;
		var cache_datas = me.teacher_cache[cache_key] || {};
		if($.isEmptyObject(cache_datas)) {
			$.ajax({
				type:"post",
				url:"/Sns/ClassAdmin/Teacher/getTeacherlist_json",
				data:{
					'subject_id':subject_id,
					'class_code':me.class_code
				},
				dataType:"json",
				async:false,
				success:function(json) {
					cache_datas = me.teacher_cache[cache_key] = json.data || {};
				}
			});
		}
		//填充教师列表信息
		me.fillTeacher(cache_datas);
	});
};

class_teacher_manage.prototype.fillTeacher=function(datas) {
	datas = datas || {};
	var context = $("#teacher_list");
	//移除已有的教师信息
	$('tr:gt(0)', context).remove();
	//教师信息填充
	var cloneObj = $('tr:eq(0)', context);
	for(var i in datas) {
		var trObj = cloneObj.clone().show().appendTo(context);
		var data = datas[i];
		
		var key = data.client_account + ':' + data.subject_id;
		data.is_checked = $.isEmptyObject(this.data_json.del[key]) ? data.is_checked : false;
		data.is_checked = $.isEmptyObject(this.data_json.add[key]) ? data.is_checked : true;
		
		$('td:eq(2)', trObj).html(data.client_name);
		$('td:eq(1)>img', trObj).attr({'src':data.client_headimg_url,'width':'60px','height':'60px'});
		$(":input:radio", trObj).attr({
			'id':'teacher_' + data.client_account,
			'checked':data.is_checked
		});
	}
};

class_teacher_manage.prototype.teacher_del = function(){
	var self = this;
	$("a",$("#teacher_select_list")).live('click', function(){
		var subject_id = (this.id.toString().match(/(\d+)/ig) || [])[0];
		var uid = (this.id.toString().match(/(\d+)/ig) || [])[1];
		$(":input:radio[id='teacher_"+ uid +"']", $("#teacher_list")).attr('checked', false);
		self.data({'subject_id':subject_id,'uid':uid}, 'del');
		$("#" + this.id, $("#teacher_select_list")).parent().parent().remove();
		
	});
};

class_teacher_manage.prototype.teacher_add = function() {
	var context_1 = $("#teacher_select_list");
	var self = this;
	$("#add_teacher").live('click', function(){
		var uid = ($(':input:radio:checked',$("#teacher_list")).attr('id').toString().match(/(\d+)/) || [])[1];
		var is_can_add = self.checked_is_add(self.selected_subject_id);
		if(is_can_add.result == false) {
			var windowWidth = document.documentElement.clientWidth;   
		    var windowHeight = document.documentElement.clientHeight;   
		    var popupHeight = $("#del_teacher_tip").height();   
		    var popupWidth = $("#del_teacher_tip").width();  
		    var subject_name= $("#subject_id_" + self.selected_subject_id, $("#subject_list")).html();
		    $("p:eq(1)", $("#teacher_selected_repeat")).html(subject_name + "课老师已存在&nbsp;请重新选择");
			$("#teacher_selected_repeat").css({
				'z-index':200,
				"position":"absolute",
				"top":(windowHeight-popupHeight)/2+$(document).scrollTop(),
				"left":(windowWidth-popupWidth)/2
			}).show();
			return false;
		}
		
		var cloneObj = $('tr:eq(0)', context_1);
		var trObj = cloneObj.clone().show().appendTo(context_1);
		$('td:eq(0)', trObj).attr('id', 'selected_subject_' + self.selected_subject_id).html($("#subject_id_"+self.selected_subject_id, $("#subject_list")).html());
		$('td:eq(1)', trObj).html($('td:eq(2)', $(":input:radio:checked",$("#teacher_list")).parent().parent()).html());
		$('td:eq(2) a', trObj).attr('id', 'class_teacher_' + self.selected_subject_id + '_' + uid);
		self.data({'subject_id':self.selected_subject_id,'uid':uid}, 'add');
		$(':input:radio', $(this)).attr('checked',true);
	});
	
	
	$("input[class='qd_btn']").click(function(){
		$("#teacher_selected_repeat").hide();
	});
};

class_teacher_manage.prototype.teacher_select = function(){
	var context = $("#teacher_list");
	$(":input:radio", context).live('click', function(){
		
	});
};

class_teacher_manage.prototype.checked_is_add = function(subject_id){
	var context = $("#teacher_select_list");
	var con = $("#selected_subject_" + subject_id, context).html();
	var result = false;
	if(con == null)
		result = true;

	return {'result':result};
};

class_teacher_manage.prototype.data = function(data,type){
	var key =data.uid + ':' + data.subject_id;
	if(type == 'del') {
		!$.isEmptyObject(this.data_json.add[key]) ? this.data_json.add.add[key] = '' : this.data_json.del[key] = data;
	}else if(type == 'add') {
		!$.isEmptyObject(this.data_json.del[key]) ? this.data_json.del[key] = '' : this.data_json.add[key] = data;
	}
};

class_teacher_manage.prototype.to_change_teacher = function(){
	var self = this;
	var result = 1;
	$("#to_change_teacher").click(function(){
		if(!$.isEmptyObject(self.data_json.del) || !$.isEmptyObject(self.data_json.add)) {
			$.ajax({
				type:"post",
				url:"/Sns/ClassAdmin/Teacher/setTeacher",
				data:{
					'data':self.data_json,
					'class_code':self.class_code
				},
				dataType:"json",
				async:false,
				success:function(json) {
					if(json.status>0){
						$.showSuccess("保存成功");
						self.data_json.del = {};
						self.data_json.add = {};
						return false;
					}
					$.showError("保存失败");
					return;
				}
			});
		}
		
		$.showTip("没有任何更改");
	});
	
};

$(document).ready(function(){
	new class_teacher_manage();
});