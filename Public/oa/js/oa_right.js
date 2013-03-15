function rightCls() {}

rightCls.prototype.inited = false;

rightCls.prototype.init=function() {
	this.attachEvent();
};

rightCls.prototype.attachEvent=function() {
	this.attachEventForSearch();
	this.attachEventForScheType();
};

rightCls.prototype.attachEventForSearch=function() {
	//通过标题和时间搜索个人日程
	$("#schedule_search").click(function(){
		var schedule_name = $("#schedule_name").val();
		if(schedule_name == "" || schedule_name == 0){
			alert("请填写你要搜索的日程标题！");
			$('#schedule_name').focus();
			return false;
		}else{
			var action = "/Oa/Schedulemanage/searchScheduleinfo"
			document.getElementById("searchebytimeandname").action=action;
			$("#searchebytimeandname").submit();
		}
	});
	
	$("#up_page").click(function(){
		var schedule_name = $("#schedule_name_json").val();
		var page = $("#page_json").val()-1;
		var datatime = $("#datatime_json").val();
		$.ajax({
			type : "POST",
			url : "/Oa/Index/jsonsearchScheduleinfo",
			dataType : "json",
			data : param,
			success : function(jsonarr) {
				var data = jsonarr.data;
				var err = jsonarr.error;
				if(err.code < 0){
					alert(jsonarr.error.message);
					return false;
				}
			}
		});
	});
};

rightCls.prototype.attachEventForScheType=function() {
	var _this = this;
	
	$('#show_schetype_div').bind('click', function() {
		if(!_this.inited) {
			_this.loadScheTypeList();
			_this.inited = true;
		}
		$('#popDiv,#popIframe,#bg').show();
	});
	
	$('#close_schetype_div_img').bind('click', function() {
		$('#popDiv,#popIframe,#bg').hide();
	});
	
	//添加
	$('#show_add_div_a').bind('click', function() {
		$('#type_name_for_add').val('');
		_this.showScheDiv('pop_div_for_add');
	});
	$('#close_add_div_img, #close_add_div_a').bind('click', function() {
		_this.hideScheDiv('pop_div_for_add');
	});
	$('#submit_add_a').bind('click', function() {
		var type_name = $('#type_name_for_add').val();
		if($.trim(type_name) == '') {
			alert('分类名不能为空!');
			
			$('#type_name_for_add').focus();
			return false;
		}else if($.trim(type_name).length >14){
			alert('标题不超过14个字符');
			return false;
		}
		var params = {
			'type_name' : type_name	
		};
		_this.dealAddAction(params);
	});
	
	//修改
	$('#close_modify_div_img, #close_modify_div_a').bind('click', function() {
		_this.hideScheDiv('pop_div_for_modify');
	});
	$('#submit_modify_a').bind('click', function() {
		var type_id = $('#type_id_for_modify').val();
		var type_name = $.trim($('#type_name_for_modify').val());
		var old_type_name = $.trim($('#td_name_' + type_id).text());

		if($.trim(type_name) == '') {
			alert('新类型名不能为空!');
			return false;
		}else if($.trim(type_name).length >14){
			alert('标题不超过14个字符');
			return false;
		} else if(type_name == old_type_name) {
			alert('未做修改!');
			$('#type_name_for_modify').focus();
			return false;
		}
		
		var params = {
			'type_id' : type_id,
			'type_name' : type_name
		};
		_this.dealModifyAction(params);
	});
	
	//删除
	$('#close_delete_div_img, #close_delete_div_a').bind('click', function() {
		_this.hideScheDiv('pop_div_for_delete');
	});
	$('#submit_delete_a').bind('click', function() {
		var type_id = $('#type_id_for_delete').val();
		if(type_id == '') {
			alert('您要删除的分类不存在!');
			return false;
		}
		var params = {
			'type_id' : type_id
		};
		_this.dealDeleteAction(params);
	});
};

rightCls.prototype.loadScheTypeList=function() {
	var _this = this;
	$.ajax({
		type:'get',
		url:'/Oa/Schedulemanage/ScheduleTypeList',
		dataType:'json',
		success:function(json) {
			var err = json.error;
			if(err.code > 0) {
				var data = json.data;
				for(var i in data) {
					_this.addOneScheType(data[i]);
				}
			} else {
				if(err.message != '') {
					_this.addScheTypeConfirm(err.message);
				}
			}
		}
	});
};

rightCls.prototype.dealModifyAction=function(params) {
	var _this = this;
	$.ajax({
		type:'post',
		url:'/Oa/Schedulemanage/modifyScheduleType',
		dataType:'json',
		data:params,
		success:function(json) {
			var err = json.error;
			if(err.code > 0) {
				var data = json.data;
				$('#tr_' + data.type_id + " td:eq(0)").html(data.type_name);
				_this.hideScheDiv('pop_div_for_modify');
			} else if(err.message != '') {
				alert(err.message);
				$('#type_name_for_modify').focus();
			}
		}
	});
};

rightCls.prototype.dealDeleteAction=function(params) {
	var _this = this;
	$.ajax({
		type:'get',
		url:'/Oa/Schedulemanage/delScheduleType',
		data:params,
		dataType:'json',
		success:function(json) {
			var err = json.error;
			if(err.code > 0){
				$('#tr_' + json.data.type_id).remove();
				//alert("删除分类成功，该分类下的日程被移至‘默认分类下’");
				window.location.reload();
			} else if(err.message != '') {
				alert(err.message);
			}
			_this.hideScheDiv('pop_div_for_delete');
		}
	})
};

rightCls.prototype.dealAddAction=function(params) {
	var _this = this;
	$.ajax({
		type:'post',
		url:'/Oa/Schedulemanage/addScheduleType',
		dataType:'json',
		data:params,
		success:function(json) {
			var err = json.error;
			if(err.code > 0) {
				_this.addOneScheType(json.data);
				_this.hideScheDiv('pop_div_for_add');
			} else if(err.message != '') {
				alert(err.message);
				$('#type_name_for_add').focus();
			}
		}
	});
};

rightCls.prototype.addOneScheType=function(data) {
	var _this = this;
	
	if($.isEmptyObject(data)) {
		return false;
	}
	
	//删除提示信息
	$('#tr_0').remove();
	
	var tr_id = "tr_" + data.type_id;
	$('<tr id="' + tr_id + '"></tr>').appendTo($('#stype_list'));
	
	$('<td bgcolor="#ffffff" id="td_name_' + data.type_id + '">' + data.type_name + '</td>').css({
		'height':'40', 
		'padding-left':'10px'}
	).appendTo($('#' + tr_id));
	
	var td_id = "td_" + data.type_id;
	$('<td id="' + td_id + '" bgcolor="#ffffff"></td>').css({
		'padding-left':'10px'
	}).appendTo($('#' + tr_id));
	
	var modify_id = "modify_" + data.type_id;
	var delete_id = "delete_" + data.type_id;
	
	$('<a href="javascript:;" id="' + modify_id + '">修改</a>').css('color', '#069').appendTo($('#' + td_id));
	$('<span>&nbsp;| &nbsp;</span>').appendTo($('#' + td_id));
	$('<a href="javascript:;" id="' + delete_id + '">删除</a>').css('color', 'gray').appendTo($('#' + td_id));	
	
	var type_id = data.type_id;
	//添加相应事件
	$('#' + modify_id).bind('click', function() {
		$('#type_id_for_modify').val(type_id);
		var type_name = $('#td_name_' + type_id).text();
		$('#type_name_for_modify').val(type_name);
		_this.showScheDiv('pop_div_for_modify');
	});
	
	$('#' + delete_id).bind('click', function() {
		$('#type_id_for_delete').val(type_id);
		var type_name = $('#td_name_' + type_id).text();
		$('#type_name_for_delete').text(type_name);
		_this.showScheDiv('pop_div_for_delete');
	});
};

rightCls.prototype.addScheTypeConfirm=function(message) {
	var tr_id = "tr_0";
	$('<tr id="' + tr_id + '"></tr>').appendTo($('#stype_list'));
	$('<td bgcolor="#ffffff" colspan="2" align="center">' + message + '</td>').css({
		'height':'40', 
		'padding-left':'10px'}
	).appendTo($('#' + tr_id));
};

rightCls.prototype.showScheDiv=function(id) {
	$('#bg2, #popIframe2, #' + id).show();
};

rightCls.prototype.hideScheDiv=function(id) {
	$('#bg2, #popIframe2, #' + id).hide();
};

$(function() {
	WdatePicker( {
		eCont : 'div1',
		onpicked : function(dp) {
			
		}
	});
	
	var obj = new rightCls();
	obj.init();
});