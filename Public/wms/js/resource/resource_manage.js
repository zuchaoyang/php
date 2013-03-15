function resource_import() {
	this.attachEvent();
}
resource_import.prototype.attachEvent=function() {
	$('form:first').submit(function() {
		var excel_name = $.trim($('#excel_name').val());
		var excel_filename = $.trim($('#excel_filename').val());
		var product_id = $(':input[type="radio"][name="product_id"]:checked').val();
		if(!excel_name) {
			alert("请填写excel名称");
			return false;
		}
		if(!excel_filename) {
			alert("请先选择要导入的excel表格");
			return false;
		}
		if(!product_id) {
			alert("请先选择资源类型");
			return false;
		}
		
		$('#button_import').attr({
			'disabled' : 'disabled',
			'style' : 'color:#ccc;cursor:default;'
		});
		
		return true;
	});
	
	$('#button_import').click(function() {
		$('form:first').submit();
	});
};
$(document).ready(function() {
	new resource_import();
});