taskCls=function() {
	this._this = this;
	$('#task_content').xheditor({skin:'vista',tools:'Separator,BtnBr,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,Align,List,Outdent,Indent,Link,Unlink,Emot'});
};

taskCls.prototype.treeForDpt={};
taskCls.prototype.treeForMember={};
taskCls.prototype.dpt_id = "dpt_list";
taskCls.prototype.members_id = "members_list";
taskCls.prototype.selected_members_id = "selected_members_list";
taskCls.prototype.show_detail_div_id = "show_detail_list";
taskCls.prototype.draft_config = {
	inited_draft:false,
	pre_task_type:0 //记录用户上一次选择的类型
};
taskCls.prototype.datas = {};
taskCls.prototype.school_id = 0;
taskCls.prototype.dpt_json={};

taskCls.prototype.dpt_arr = [];
taskCls.prototype.member_arr = [];

taskCls.prototype.init=function() {
	var _this = this._this;
	
	_this.school_id = $('#school_id').val();
	
	_this.loadTree();
	_this.attachEvent();
};

taskCls.prototype.loadTree=function() {
	var _this = this._this;
	
	_this.loadTreeJson();
	_this.loadTreeForDpt();
	_this.loadTreeForMember();
};

taskCls.prototype.loadTreeJson=function() {
	var _this = this._this;
	$.ajax({
		type:'get',
		url:"/Public/Department/loadTree/data_type/json/school_id/" + _this.school_id,
		dataType:'json',
		async:false,
		success:function(json) {
			_this.dpt_json = json;
		}
	});
};

taskCls.prototype.loadTreeForDpt=function() {
	var _this = this._this;
	_this.treeForDpt = new dhtmlXTreeObject("doctree_box_dpt","100%","100%",0);
	_this.registerTree(_this.treeForDpt);
};

taskCls.prototype.loadTreeForMember=function() {
	var _this = this._this;
	_this.treeForMember = new dhtmlXTreeObject("doctree_box_member","100%","100%",0);
	_this.registerTree(_this.treeForMember);
};

taskCls.prototype.registerTree=function(tree) {
	var _this = this._this;
	
	tree.setImagePath("/Public/local/js/dhtmlxtree/codebase/imgs/");
	//设置节点的选中颜色
	tree.setOnClickHandler(function(id){
		tree.setItemColor(id, '#369', 'blue');
	});
	//tree.attachEvent("onOpenEnd",updateTreeSize);
	tree.enableCheckBoxes(false);
	tree.setDataMode("json");
	//load first level of tree
	tree.loadJSONObject(_this.dpt_json);
};

taskCls.prototype.addOption=function(obj, parentId) {
	if(typeof obj == 'object') {
		$('#' + parentId).append(obj);
	}
};

taskCls.prototype.removeAll=function(selectId) {
	$('#' + selectId + ' option').remove();
};

taskCls.prototype.removeSelected=function(selectId) {
	$('#' + selectId + ' option:selected').remove();
};

taskCls.prototype.getSelectVal=function(selectId) {
	return $('#' + selectId).val();
};

taskCls.prototype.fillMembersSelectWithJson=function(json) {
	var _this = this._this;
	
	//清空列表中的数据
	_this.removeAll(_this.members_id);
	
	var err = json.error;
	var datas = json.data;
	if(err.code > 0) {
		for(var i in datas) {
			var val = datas[i].client_account;
			var text = datas[i].client_name;
			_this.addOption($('<option id="id_' + val + '" value="' + val + '">' + text  + '</option>'), _this.members_id);
		}
	} else if(err.message) {
		alert(err.message);
	}
};

taskCls.prototype.attachEvent=function() {
	var _this = this._this;
	_this.attachEventForDpt();
	_this.attachEventForMember();
	_this.attachEventForDraft();
	_this.attachEventForDetail();
	_this.attachEventForExpand();
	//_this.attachEventForDrag();
};

taskCls.prototype.attachEventForDpt=function() {
	var _this = this._this;
	
	//选择部门
	$('#choice_dpt').bind('click', function() {
		_this.showPopDiv('show_dpt_div');
	});
	
	$("#task_content").bind('keyup',function(){
		alert(1111);
		$("#word_counter").html(this.val().length);
	});
	
	//关闭部门弹出层 
	$('#close_dpt_div_img').bind('click', function() {
		_this.hidePopDiv('show_dpt_div');
	});
	
	//添加部门
	$('#add_dpt_button').bind('click', function(){
		var id = _this.treeForDpt.getSelectedItemId();
		var text = _this.treeForDpt.getItemText(id);
		//判断部门是否已经存在于列表中
		if($('#' + _this.dpt_id + ' option[id="id_' + id + '"]').length > 0) {
			alert("部门:" + text + ",已在列表中!");
		} else {
			_this.addOption($('<option id="id_' + id + '" value="' + id + '">' + text + '</option>'), _this.dpt_id);
		}
	});
	
	//移除部门
	$('#remove_dpt_button').bind('click', function(){
		_this.removeSelected(_this.dpt_id);
	});
	
	//确认部门
	$('#submit_dpt_button').bind('click', function(){
		var old_arr = _this.dpt_arr.slice(0, _this.dpt_arr.length);
		_this.dpt_arr = [];
		$('#' + _this.dpt_id + " option").each(function() {
			_this.dpt_arr.push(this.value);
		});
		//需要刷新详细页
		if(!_this.arr_equal(old_arr, _this.dpt_arr)) {
			_this.loadDetailJson();
		}
		_this.hidePopDiv('show_dpt_div');
	});
};

taskCls.prototype.attachEventForMember=function() {
	var _this = this._this;
	
	//选择用户
	$('#choice_member').bind('click', function() {
		_this.showPopDiv('show_member_div');
	});
	
	//关闭用户弹出层
	$('#close_member_div_img').bind('click', function(){
		_this.hidePopDiv('show_member_div');
	});
	
	//显示用户
	$('#show_members_button').bind('click', function() {
		var id = _this.treeForMember.getSelectedItemId();
		//检测该部门是否已经添加
		if($.inArray(id, _this.dpt_arr) >= 0) {
			alert('该部门成员已在部门中添加!');
			_this.removeAll(_this.members_id);
			return false;
		} else if(id > 0) {
			$.ajax({
				type : 'get',
				url : '/Oa/Task/getDptMembersByDptId/dpt_id/' + id,
				dataType : 'json',
				success : function(json) {
					_this.fillMembersSelectWithJson(json);
				}
			});
		} else {
			_this.removeAll(_this.members_id);
		}
	});
	//添加用户
	$('#add_member_button').bind('click', function() {
		var val = _this.getSelectVal(_this.members_id);
		if(val && typeof val != 'undefined') {
			val = val.toString();
			var arr = val.split(',');
			for(var i in arr) {
				//当已经选则的列表中没有的时候添加
				if($('#' + _this.selected_members_id + ' option[id="id_' + arr[i] + '"]') .length == 0) {
					var _option = $('#' + _this.members_id + ' option[id="id_' + arr[i] + '"]').get(0);
					$('#' + _this.selected_members_id).append($('<option id="' + _option.id + '" value="' + _option.value + '">' + _option.text + '</option>'));
				}
			}
		}
	});
	
	//移除用户
	$('#remove_member_button').bind('click', function() {
		_this.removeSelected(_this.selected_members_id);
	});
	
	//确认用户
	$('#submit_member_button').bind('click', function() {
		var old_arr = _this.member_arr.slice(0, _this.member_arr.length);
		_this.member_arr = [];
		$('#' + _this.selected_members_id + " option").each(function() {
			_this.member_arr.push(this.value);
		});
		
		if(!_this.arr_equal(old_arr, _this.member_arr)) {
			_this.loadDetailJson();
		}
		_this.hidePopDiv('show_member_div');
	});
};

taskCls.prototype.attachEventForDraft=function() {
	var _this = this._this;
	
	//页面提取草稿
	$('#get_draft_list_button').bind('click', function() {
		var tasktypeObj = $('#task_type option:selected');
		$('#draft_type_name').text(tasktypeObj.text());
		
		var task_type = tasktypeObj.val();
		var task_type_changed = _this.draft_config.pre_task_type != task_type ? true : false;
		if(_this.draft_config.inited_draft === false || task_type_changed) {
			//草稿类型改变，则以前的信息要清空
			if(task_type_changed) {
				$('#show_draft_list *').remove();
			}
			var params = {
				'task_type':task_type,
				'page':1
			};
			var result = _this.loadDraft(params);
			_this.draft_config.inited_draft = true;
			_this.draft_config.pre_task_type = task_type;
		}
		if(result == true || $("#show_draft_list p").text() != "") {
			_this.showPopDiv('show_draft_div');
		}else{
			alert("获取个人日程草稿信息失败或这当前分类下没有草稿！");
		}
	});
	
	//关闭草稿列表
	$('#close_draft_div_button, #close_draft_div_img').bind('click', function() {
		$('#show_draft_div, #bg, #popIframe').hide();
	});
	
	//获取更多草稿
	$('#load_more_draft').bind('click', function() {
		var task_type = $('#task_type').val();
		var load_page = $('#load_page').val();
		load_page = parseInt(load_page);
		if(!isNaN(load_page) && load_page > 1) {
			var params = {
				'task_type':task_type,
				'page':load_page
			};
			_this.loadDraft(params);
		} else {
			alert('没有更多的草稿可以加载!');
		}
	});
	
	//通过id获取单个草稿信息
	$('#show_draft_by_id_submit').bind('click', function() {
		var draft_id = $(':radio[name="draft_id_radio"]:checked').val();
		var md5_key = $('#md5_key_' + draft_id).val();
		if(draft_id > 0) {
			$.ajax({
				type:'get',
				url:'/Oa/Task/getDraftById/draft_id/' + draft_id + '/md5_key/' + md5_key,
				dataType:'json',
				async:false,
				success:function(json) {
					_this.fillPageWithJson(json);
				}
			});
		}
		$('#show_draft_div, #bg, #popIframe').hide();
	});
};

taskCls.prototype.attachEventForDetail=function() {
	var _this = this._this;
	
	$('#choice_detail').bind('click', function() {
		_this.showPopDiv('show_detail_div');
	});
	
	$('#close_show_detail_div_img').bind('click', function() {
		_this.hidePopDiv('show_detail_div');
	});
};

taskCls.prototype.attachEventForExpand=function() {
	var _this = this._this;
	
	$('#task_title').bind({
		'keyup':function() {
			_this.loadPreviewMsg();
		},
		'focus':function() {
			var task_title_default = $('#task_title_default').val();
			if($.trim(task_title_default) == $.trim(this.value)) {
				this.value = "";
			}
		},
		'blur':function() {
			if($.trim(this.value) == "") {
				this.value = $('#task_title_default').val();
			}
		}
	});
	
	$('#expiration_time_switch').bind('click', function() {
		if(this.checked) {
			$('#show_expiration_time,#sms_remind_div').show();
		} else {
			$('#show_expiration_time,#sms_remind_div').hide();
		}
	});
	
	$('#need_sms_remind').bind('click', function() {
		if(this.checked) {
			var expiration_time = $('#expiration_time').val();
			if(!expiration_time) {
				alert('请您先设置到期日期!');
				this.checked = false;
			} else {
				$('#show_dealine_hours').show();
				_this.fillRemindDate();
			}
		} else {
			$('#show_dealine_hours').hide();
		}
	});
	
	$('#deadline_hours').bind('change', function() {
		_this.fillRemindDate();
	});
	
	$('#need_sms_push').bind('click', function() {
		if(this.checked) {
			$('#show_sms_push_msg').css('width', "100%").show();
			_this.loadPreviewMsg();
		} else {
			$('#show_sms_push_msg').hide();
		}
	});
	
	$('#tag_names').bind({
		'focus':function() {
			var val = $.trim(this.value);
			var default_val = $.trim($('#tag_names_default').val());
			if(val == default_val) {
				this.value = "";
			}
		},
		'blur':function() {
			var val = $.trim(this.value);
			if(val == '') {
				this.value = $('#tag_names_default').val();
			} else {
				var tag_arr = val.toString().split(" ");
				for(var i in tag_arr) {
					var tag = $.trim(tag_arr[i]);
					if(tag.length > 4) {
						alert("标签要应该少于4个字!");
					}
				}
			}
		}
	});
	
	$('#submit_button').bind('click', function() {
		if(_this.judgeHTML()) {
			$('#form_task').submit();
			return true;
		} else {
			return false;
		}
	});
	
	$('#submit_draft_button').bind('click', function() {
		var passed = _this.judgeHTML();
		if(passed && confirm("您确认要保存为草稿吗?\n草稿将导致附加功能信息丢失!")) {
			$('<input type="hidden" name="is_draft" value="1"/>').appendTo($('#form_task'));
			$('#form_task').submit();
			return true;
		} else {
			return false;
		}
	});
};

//taskCls.prototype.attachEventForDrag=function() {
//	$('#show_dpt_div, #show_member_div, #show_detail_div, #show_draft_div').draggable({cursor:'move'});
//};

taskCls.prototype.loadPreviewMsg=function() {
	$('#show_preview_msg').css('color', $('#show_sms_push_msg').css('color'));
	
	var school_name = $('#school_name').val();
	var tasktype_name = $('#task_type option:selected').text();
	var task_title = $('#task_title').val();
	var expiration_time = $.trim($('#expiration_time').val());
	var expiration_time_str = '';
	if(expiration_time != '') {
		expiration_time_str = "到期时间:" + expiration_time.toString() + " "; 
	}
	var preview_msg = "";
	//2012-11-20 客服部门提出修改短信内容
	//preview_msg = school_name + "-" + tasktype_name + "：" + task_title + "。" + expiration_time_str + "请登录集中办公平台查看全文";
	preview_msg = "学校通知：" + task_title + "详情登录【我们网】";
	
	preview_msg = $.trim(preview_msg);
	if(preview_msg.length > 70) {
		preview_msg = preview_msg.slice(0, 70) + "...";
	}
	$('#show_preview_msg').text(preview_msg);
};

taskCls.prototype.loadDetailJson=function() {
	var _this = this._this;
	
	var params = {};
	if(_this.dpt_arr.length > 0) {
		params.dpt_arr = _this.dpt_arr;
	}
	if(_this.member_arr.length > 0) {
		params.member_arr = _this.member_arr;
	}
	$.ajax({
		type:'post',
		url:'/Oa/Task/getSelectedMembers',
		dataType:'json',
		data:params,
		success:function(json) {
			_this.fillDetailWithJson(json);
		}
	});
};

taskCls.prototype.fillDetailWithJson=function(json) {
	var _this = this._this;
	
	var parentObj = $('#' + _this.show_detail_div_id);
	//清空div下的所有子元素
	$('#' + _this.show_detail_div_id + " *").remove();
	
	var err = json.error;
	if(err.code > 0) {
		var data = json.data;
		var stat = json.stat;
		$('#total_nums').html(stat.total_nums);
		for(var i in data) {
			$('<h4>' + data[i].dpt_name + "(" + data[i].nums + '人)</h4>').appendTo(parentObj);
			var item = data[i].item;
			var str = "";
			for(var j in item) {
				str += item[j].client_name + "&nbsp;";
			}
			$('<p id="id_"' + data[i].dpt_id +'>' + str + '</p>').appendTo(parentObj);
		}
		
		var label_stat_arr = [];
		if(stat.total_nums) {
			label_stat_arr.push('总人数:' + stat.total_nums + "人");
		}
//		if(stat.real_nums) {
//			label_stat_arr.push("实际人数:" + stat.real_nums + "人");
//		}
		if(stat.repeat_nums) {
			label_stat_arr.push("实际人数:" + stat.real_nums + "人");
			label_stat_arr.push('重复或有误人数:' + stat.repeat_nums + "人");
		}
		var label_stat_str = label_stat_arr.join(",");
		if(!!label_stat_str) {
			label_stat_str = "(" + label_stat_str + ")";
		}
		$('#label_show_stat').text(label_stat_str);
		
	} else {
		if(err.message != '') {
			$('#total_nums').html(0);
			$('<p>' + err.message + '</p>').appendTo(parentObj);
			$('#' + _this.show_detail_div_id).css('height', '300px');
		}
	}
};

taskCls.prototype.arr_equal=function(arr, arr1) {
	if(!$.isArray(arr) || !$.isArray(arr1)) {
		return false;
	}
	if(arr.length != arr1.length) {
		return false;
	}
	for(var i in arr) {
		if($.inArray(arr[i], arr1) < 0) {
			return false;
		}
	}
	return true;
};

taskCls.prototype.dump=function(obj) {
	for(var i in obj) {
		document.writeln(i + "=>" + obj[i] + "=>" + typeof obj[i] + "<br />");
	}
};

taskCls.prototype.fillPageWithJson=function(json) {
	var _this = this._this;
	
	var err = json.error;
	if(err.code > 0) {
		var data = json.data;
		
		//页面追加信息来源处理
		var formObj = $('#form_task');
		$('<input type="hidden" name="from" value="draft"/>').appendTo(formObj);
		$('<input type="hidden" name="md5_key" value="' + data.md5_key + '"/>').appendTo(formObj);
		$('<input type="hidden" name="draft_id" value="' + data.task_id + '">').appendTo(formObj);
		
		$('#task_title').val(data.task_title);
		//判断是否已经引入KE对象
		$.extend(xheditor.settings,{shortcuts:{'ctrl+enter':submitForm}});
		var a = $('#task_content').xheditor({skin:'vista',tools:'Separator,BtnBr,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,Align,List,Outdent,Indent,Link,Unlink,Emot'});
		a.setSource(data.task_content);
		
//		if(typeof KE == 'object') {
//			KE.html('task_content', data.task_content);
//		}
		$('#task_tags').val(data.task_tags);
		$('#task_type option[value="' + data.task_type + '"]').attr('selected', 'selected');
		
		//填充已选部门
		_this.dpt_arr = [];
		$('#' + _this.dpt_id + ' option').remove();
		var dpt_list = data.dpt_list;
		for(var i in dpt_list) {
			_this.addOption($('<option id="id_' + dpt_list[i].dpt_id + '" value="' + dpt_list[i].dpt_id + '">' + dpt_list[i].dpt_name + '</option>'), _this.dpt_id);
			_this.dpt_arr.push(dpt_list[i].dpt_id);
		}
		
		//填充追加人员
		_this.member_arr = [];
		$('#' + _this.selected_members_id + ' option').remove();
		var append_userlist = data.append_userlist;
		for(var i in append_userlist) {
			var val = append_userlist[i].client_account;
			var text = append_userlist[i].client_name;
			$('#' + _this.selected_members_id).append($('<option id="id_' + val + '" value="' + val + '">' + text + '</option>'));
			_this.member_arr.push(val);
		}
		//填充标签信息
		$('#tag_names').val(data.task_tags);
		
		//填充详细信息
		$('#' + _this.show_detail_div_id + " *").remove();
		_this.loadDetailJson();
	}
};

taskCls.prototype.loadDraft=function(params) {
	var _this = this._this;
	var result = false;
	$.ajax({
		type:'get',
		url:'/Oa/Task/getDraftByUidWithTasktype',
		dataType:'json',
		data:params,
		async:false,
		success:function(json) {
			result = _this.fillDraftWithJson(json);
		}
	});
	
	return result;
};

taskCls.prototype.fillDraftWithJson=function(json) {
	var _this = this._this;
	
	var err = json.error;
	var data = json.data;
	
	var load_page = json.load_page;
	load_page = load_page > 0 ? load_page : 0;
	if(load_page > 0) {
		$('#load_more_draft, #show_more_draft_p').show();
	} else {
		$('#load_more_draft, #show_more_draft_p').hide();
	}
	$('#load_page').val(load_page);
	
	var parentObj = $('#show_draft_list');
	if(err.code > 0) {
		for(var i in data) {
			var task = data[i];
			var p_id = "pid_" + task.task_id;
			$('<p id="' + p_id + '"></p>').appendTo(parentObj);
			
			var pObj = $('#' + p_id);
			$('<input type="radio" name="draft_id_radio" value="' + task.task_id + '"/>').appendTo(pObj);
			$('<input type="hidden" id="md5_key_' + task.task_id + '" value="' + task.md5_key + '"/>').appendTo(pObj);
			$('<span>&nbsp;' + task.task_title + '</span>').addClass('fontwidth').appendTo(pObj);
			$('<span>上次编辑时间：' + task.upd_time + '</span>').addClass('font_hui').appendTo(pObj);
		}
		return true;
	} else {
		if(err.message != '') {
			alert("获取个人日程草稿信息失败或这当前分类下没有草稿！");
		}
		return false;
	}
};

taskCls.prototype.fillRemindDate=function() {
	var _this = this._this;
	
	var system_date = $('#expiration_time').val().toString();
	var offset = $('#deadline_hours').val();
	offset = !isNaN(parseInt(offset)) ? parseInt(offset) : 0;
	
	if(offset > 0) {
		var remind_date = system_date + " " + (24 - offset == 0 ? "00" : (24 - offset).toString()) + ":00";
		var obj = $('#show_remind_date');
		obj.css({
			'color':obj.parent().css('color'),
			'padding':'0 5px'
		}).text(remind_date);
	}
};

taskCls.prototype.showPopDiv=function(div_id) {
	$('#' + div_id + ", #bg, #popIframe").show();
};

taskCls.prototype.hidePopDiv=function(div_id) {
	$('#' + div_id + ", #bg, #popIframe").hide();
}; 

taskCls.prototype.judgeHTML=function() {
	var _this = this._this;
	
	var task_title = $.trim($('#task_title').val());
	if(task_title.length>55){
		alert('工作标题不能超过55个字!');
		return false;
	}
	if(task_title == '' || task_title == $.trim($('#task_title_default').val())) {
		alert('请输入标题!');
		$('#task_title').focus();
		return false;
	}
	
	var task_content = $('#task_content').val();
	if($.trim(task_content) == '') {
		alert('请输入工作内容!');
		return false;
	} else {
		$('#task_content').text(task_content);
	}
	
	var total_nums = parseInt($('#total_nums').html());
	total_nums = (isNaN(total_nums) || total_nums <= 0) ? 0 : total_nums;
	if((_this.dpt_arr.length == 0 && _this.member_arr.length == 0) || total_nums <= 0) {
		alert('请添加收件人信息!');
		return false;
	}
	var expiration_checked = $("#expiration_time_switch").attr("checked");
	var expiration_time = $('#expiration_time').val();
	if(expiration_checked == true){
		if(expiration_time == "") {
			alert("请选择到期的日期");
			return false;
		} else {
			//检测过期时间是否小于当前时间
			if(!_this.checkExpirationTime()) {
				return false;
			}
		}
	}
	
	var tag_names = $.trim($('#tag_names').val());
	var tag_names_default = $.trim($('#tag_names_default').val());
	if(tag_names != '' && tag_names != tag_names_default) {
		var tag_arr = tag_names.toString().split(" ");
		for(var i in tag_arr) {
			var tag = $.trim(tag_arr[i]);
			if(tag.length > 4) {
				alert('标签要应该少于4个字!');
				$('#tag_names').focus();
				return false;
			}
		}
	} else if(tag_names == tag_names_default) {
		$('#tag_names').val('');
	}
	
	$('#dpt_arr').text(_this.dpt_arr.join(","));
	$('#member_arr').text(_this.member_arr.join(","));
	
	return true;
};

taskCls.prototype.checkExpirationTime=function() {
	var expiration_time = $.trim($('#expiration_time').val().toString());
	var system_date = $('#system_date').val().toString();
	
	if(expiration_time < system_date) {
		alert('过期时间小于系统当前时间,请重新选择!');
		$('#expiration_time').val('');
		return false;
	}
	return true;
};

$(document).ready(function() {
	//全局变量
	tcObj = new taskCls();
	tcObj.init();
});