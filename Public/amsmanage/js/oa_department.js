function dptCls(){
	this._this = this;
};
dptCls.prototype.check=function() {
	return this.checkSortId() && this.checkName() && this.checkDescription() && this.checkPhone() && this.checkPhoto();
};
dptCls.prototype.checkSortId=function() {
	var sort_id = $('#sort_id').val();
	var reg = /^[0-9]+$/;
	if(sort_id != '') {
		if(!reg.exec(sort_id) || parseInt(sort_id) <= 0) {
			alert('部门排序号必须是正整数!');
			$('#sort_id').focus();
			return false;
		}
	}
	return true;
};
dptCls.prototype.checkName=function() {
	var name = $('#dpt_name').val();
	name = $.trim(name);
	if(!name) {
		alert('部门名称不能为空!');
		$('#dpt_name').focus();
		return false;
	}
	return true;
};
dptCls.prototype.checkPhone=function() {
	var phone = $('#dpt_phone').val();
	var reg = {};
	reg.mobile = Share.regexProcess.regexEnum.mobile;
	reg.tel = Share.regexProcess.regexEnum.tel;
	if(phone == '') {
		alert('请填写部门电话!');
		$('#dpt_phone').focus();
		return false;
	} else if(!Share.regexProcess.judge(reg.mobile, phone) && !Share.regexProcess.judge(reg.tel, phone)) {
		alert("电话号码格式错误!");
		$('#dpt_phone').focus();
		return false;
	}
	return true;
};
dptCls.prototype.checkDescription=function() {
	var des = $('#dpt_description').val();
	var des = Share.strProcess.trimLR(des);
	if(des == '') {
		alert('请填写部门职能!');
		$('#dpt_description').focus();
		return false;
	}
	var len = Share.strProcess.StringLength(des);
	if(len > 500) {
		alert('部门职能描述信息太多!');
		$('#dpt_description').focus();
		return false;
	}
	return true;
};
dptCls.prototype.checkPhoto=function() {
	var photo = $('#dpt_photo').val();
	var ignore_dpt_photo = $('#ignore_dpt_photo').val();
	
	if(!ignore_dpt_photo && photo != '' && !Share.sbf.judgeImgType(photo)) {
		alert('请选择图片类型进行上传!');
		$('#dpt_photo').attr('value', '').trigger('click');
		return false;
	}
	return true;
};

function imgnotfind(img) {
	$(img).after($('<label>暂无部门图片</label>')).remove();
}

function reloadTree(dpt_id) {
	window.parent.window.document.getElementById('hoho').contentWindow.reloadTree(dpt_id);
}

$(document).ready(function() {
	var dpObj = new dptCls();
	$('#dpt_form').submit(function() {
		var options = {
			type:'post',
			url:$(this).attr('action'),
			dataType:'json',
			beforeSubmit:function() {
				return dpObj.check();
			},
			success:function(json) {
				var err = json.error;
				if(err.message != '') {
					alert(err.message);
				}
				if(err.code > 0) {
					var data = json.data;
					$('#dpt_form').resetForm();
					
					var id = data.up_id || data.dpt_id;
					setTimeout(reloadTree, 3000, [id]);
				}
			}
		};
		$('#dpt_form').ajaxSubmit(options);
		return false;
	});
});