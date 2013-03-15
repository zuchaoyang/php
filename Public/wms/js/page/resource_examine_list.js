function resource_examine_list(){
	this.resource_examine_show_list();
	this.resource_examine_nav();
	this.resource_examine_show_list();
	this.resource_examine_to_page();
	this.resource_examine_pass();
	this.resource_examine_no_pass();
	this.resource_examine_delete();
};

resource_examine_list.prototype.resource_examine_pass = function(){
	$("input[class='blue_word4_btn'][value='审核通过']").click(function(){
		var arr = this.id.split('_');
		var resource_id = arr[1];
		window.location.href="/Wms/Resource/Resourcetoexamine/examine_pass/resource_id/"+resource_id;
	});
};

resource_examine_list.prototype.resource_examine_no_pass = function(){
	$("input[class='blue_word5_btn'][value='审核未通过']").click(function(){
		var arr = this.id.split('_');
		var resource_id = arr[1];
		$("#comment_gai_" + resource_id).show();
	});
	
	$("input[type='button'][class='zysh_qx'][value='取消']").click(function(){
		var arr = this.id.split('_');
		var resource_id = arr[1];
		$("#comment_gai_" + resource_id).hide();
	});
	
	$("input[type='button'][value='确定'][class='zysh_qd']").click(function(){
		var arr = this.id.split('_');
		var resource_id = arr[2];
		var reason_str = $("#gai_"+resource_id).val();
		if($.trim(reason_str) == ""){
			alert("请输入不通过理由！");
		}else{
			$("#no_pass_"+resource_id).submit();
		}
	});
};




resource_examine_list.prototype.resource_examine_to_page = function() {
	$("#to_page_submit").click(function(){
		var resource_status = $("#resource_status").val();
		var to_page = $("#to_page").val() - 0;
		var current_page = $("#current_page").val() - 0;
		var total_page = $("#total_page").val() - 0;
		if(!/^\d+$/.test(to_page)) {
			alert("跳转页数必须是正整数！");
		}else if( 0 > to_page){
			alert("范围不合法！");
		}else if(to_page > total_page){
			alert("范围不合法！");
		}else{
			window.location.href="/Wms/Resource/Resourcetoexamine/show_upload_resource_list/resource_status/"+resource_status+"/page/"+to_page;
		}
	});
};

resource_examine_list.prototype.resource_examine_delete = function(){
	$("input[class='blue_word4_btn'][value='删除']").click(function(){
		var arr = this.id.split('_');
		var resource_id = arr[1];
		window.location.href="/Wms/Resource/Resourcetoexamine/examine_del/resource_id/" + resource_id;
	});
};

resource_examine_list.prototype.resource_examine_nav = function() {
	var resource_status = $("#resource_status").val();
	$("#nav_" + resource_status).addClass("word3_btn").siblings().addClass("blue_word3_btn");
};

resource_examine_list.prototype.resource_examine_show_list = function() {
	$("input[value='未审理'],input[value='通过'],input[value='未通过']").click(function(){
		var arr = this.id.split('_');
		var resource_status = arr[1];
		window.location.href="/Wms/Resource/Resourcetoexamine/show_upload_resource_list/resource_status/" + resource_status;
	});
};

$(document).ready(function(){
	new resource_examine_list();
});