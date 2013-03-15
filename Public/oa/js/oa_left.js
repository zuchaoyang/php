leftCls=function() {
	this._this = this;
	this._json_list = [];
};

leftCls.prototype.init=function() {
	var _this = this._this;
	_this.attachEvent();
	_this.fillData();
};

leftCls.prototype.attachEvent=function() {
	var _this = this._this;
	$('#show_dpt_member').bind({
		'mouseover':function() {
			$('#show_dpt_member_div').show();
		},
		'mouseout':function() {
			$('#show_dpt_member_div').hide();
		}
	});

	$('#dpt_list_for_left').bind('change', function() {
		this.fillData();
	});
};

leftCls.prototype.fillData=function() {
	this.loadDptPhoto();
	this.loadDptMember();
};

leftCls.prototype.loadDptPhoto=function() {
	var dpt_id = $('#dpt_list_for_left').val();
	var dpt_photo_url = $('#dpt_photo_small_url_' + dpt_id).val();
	$('#dpt_photo_for_left').attr('src', !!dpt_photo_url ? dpt_photo_url : "/Public/local/oa/images/photo1.gif" );
};

leftCls.prototype.loadDptMember=function() {
	var _this = this._this;
	var dpt_id = $('#dpt_list_for_left').val();
	
	var json = _this.getJsonFromJsonList(dpt_id);
	if($.isEmptyObject(json)) {
		$.ajax({
			type:'get',
			url:'/Public/Department/getDptMemberByDptId/dpt_id/' + dpt_id,
			dataType:'json',
			success:function(json) {
				_this.fillMemberDivWithJson(json);
				_this.buildJsonList(dpt_id, json);
			}
		});
	} else {
		_this.fillMemberDivWithJson(json);
	}
};

leftCls.prototype.buildJsonList=function(dpt_id, json) {
	var _this = this._this;
	_this._json_list[dpt_id] = json;
};

leftCls.prototype.getJsonFromJsonList=function(id) {
	var _this = this._this;
	if(!$.isEmptyObject(_this._json_list[id])) {
		return _this._json_list[id];
	}
	return null;
};

leftCls.prototype.fillMemberDivWithJson=function(json) {
	var err = json.error;

	var parentObj = $('#show_dpt_member_p');
	if(err.code > 0) {
		var data = json.data;
		$('#show_dpt_member_p *').remove();
		for(var i in data) {
			$('<span>' + data[i].duty_name + ':' + data[i].client_name + '</span>').appendTo(parentObj);
		}
	} else {
		if(err.message != '') {
			$('<span>' + err.message + '</span>').appendTo(parentObj);
		}
	}
};

//跳转链接
function openHref(href){
	window.location.href = href;
};

$(function() {
	var obj = new leftCls();
	obj.init();
	
});