dptCls = function(){
	this._this = this;
	this._school_id = $('#school_id').val();
	
	this.loadTree();
};

dptCls.prototype.loadTree=function() {
	var _this = this._this;
	
	var tree = new dhtmlXTreeObject('doctree_box', "100%", '100%', 0);
	tree.setImagePath("/Public/local/js/dhtmlxtree/codebase/imgs/");
	tree.setOnClickHandler(function(id) {
		tree.setItemColor(id, '#000', 'blue');
		_this.loadDeparment(id);
	});
	tree.enableCheckBoxes(false);
	tree.setDataMode("json");
	tree.loadJSON("/Public/Department/loadTree/data_type/json/school_id/" + _this._school_id);
	tree.onXLE=function() {
		var selected_id = tree.getSelectedItemId();
		_this.loadDeparment(selected_id);
	};
};

dptCls.prototype.loadDeparment=function(dtp_id) {
	var _this = this._this;
	dpt_id = parseInt(dtp_id);
	if(!isNaN(dpt_id) && dpt_id > 0) {
		$.ajax({
			type:'get',
			url:'/Oa/Department/getDptById/dpt_id/' + dpt_id,
			dataType:'json',
			success:function(json) {
				_this.fillPageWithJson(json);
			}
		});
	}
};

dptCls.prototype.fillPageWithJson=function(json) {
	var err = json.error;
	var data = json.data;
	if(err.code > 0) {
		$('#g_dpt_name').text(data.dpt_name);
		$('#g_dpt_phone').text(data.dpt_phone);
		$('#g_dpt_description').text(data.dpt_description);
		if(data.dpt_photo_src == ''){
			$('#g_dpt_photo').html('暂无照片');
		}else{
			$('#g_dpt_photo').html('<img alt="部门照片" src="'+ data.dpt_photo_src+'" />');
		}
		var dpt_member_list = data.dpt_member_list;
		if(!$.isEmptyObject(dpt_member_list)) {
			$('#g_dpt_member_list tr:gt(0)').remove();
			var parentTab = $('#g_dpt_member_list');
			for(var i in dpt_member_list) {
				var member = dpt_member_list[i];
				var id = "g_id_" + i;
				$('<tr id="' + id + '"></tr>').appendTo(parentTab);
				var trObj = $('#' + id);
				$('<td height="30">' + member.client_name + '</td>').appendTo(trObj);
				$('<td>' + member.duty_name + '</td>').appendTo(trObj);
				$('<td>' + member.client_account + '</td>').appendTo(trObj);
				$('<td>' + member.phone_id + '</td>').appendTo(trObj);
			}
		}else{
			$('#g_dpt_member_list tr:gt(0)').remove();
			var parentTab = $('#g_dpt_member_list');
			$('<tr id="nomember"></tr>').appendTo(parentTab);
			var trObj = $('#nomember');
			$('<td colspan="4" height="30" style="">暂无人员信息</td>').appendTo(trObj);
		}
	} else {
		if(err.message != '') {
			alert(err.message);
		}
	}
};

$(function() {
	new dptCls();
});
