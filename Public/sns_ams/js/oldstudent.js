function oldStudentUploadExcel() {
	$('#upload_excel_submit').bind('click', function() {
		var file_obj = $('#file_excel');
		var reg = /.+?\.xls(x?)$/;
		if(!reg.exec(file_obj.val())) {
			alert("请选择Excel文件进行上传!");
			file_obj.val("");
			return false;
		}
		$("#upload").submit();
	});
}

function oldStudentPreview() {
	$("#submit_botton").bind('click', function() {
		var pFile = $('input[type="hidden"][name="pFileName"]').val();
		if($.trim(pFile) == '') {
			alert("没有数据可以导入!");
			return false;
		}
		$("#form_export").submit();
		return true;
	});
	$("#back").bind('click', function() {
		window.history.go(-1);
	});
}

function oldStudentExportAccount() {
	$('#export_button').bind('click', function() {
		$('#form_export_fail_excel').submit();
	});
}
