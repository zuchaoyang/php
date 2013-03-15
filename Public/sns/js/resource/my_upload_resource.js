function my_upload_resource() {
	this.all_selected();
	this.resource_search();
	this.resource_resource_list();
	this.resource_status_nav();
};

my_upload_resource.prototype.all_selected = function() {
	var self = this;
	
	$("#all_button").click(function(){
		$(":checkbox[name='delete_resources[]']").attr("checked", true);
	});
	
	$("#no_all_button").click(function() {
		$(":checkbox[name='delete_resources[]']").attr("checked", false);
	});
	
	$("#delete_button").click(function(){
		if(self.hasResourceSelected()) {
			if(confirm('确定要删除吗?')) {
				$("#resource_form").submit();
			}
		} else {
			alert('请勾选要删除资源!');
		}
	});
};
my_upload_resource.prototype.hasResourceSelected=function() {
	var has_selected = false;
	$(":checkbox[name='delete_resources[]']").each(function() {
		if($(this).attr('checked')) {
			has_selected = true;
			//终止循环
			return false;
		}
	});
	return has_selected;
};
my_upload_resource.prototype.resource_search = function () {
	var self=this;
	$("#search_button").click(function(){
		$("#form_search").submit();
	});
};

my_upload_resource.prototype.resource_status_nav = function() {
	var resource_status = $("#resource_status").val() || 0;
	$("#nav_" + resource_status).addClass("word3_btn").siblings().addClass("blue_word3_btn");
};

my_upload_resource.prototype.resource_resource_list = function() {
	$(":input[id^='nav_']").click(function(){
		var resource_status = $(this).attr('id').toString().split('_')[1];
		window.location.href="/Sns/Resource/Resource/my_upload_resource_list/resource_status/" + resource_status;
	});
};

$(document).ready(function(){
	new my_upload_resource();
});