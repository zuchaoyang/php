function resource_attribute_mana(){
	this.change_nav();
	this.modify1_show();
	this.modify2_show();
	this.add1_show();
	this.close1();
	this.add();
	this.del();
	this.modify();
};

resource_attribute_mana.prototype.change_nav = function() {
	var type_id = $("#type_id").val();
	$("#nav_" + type_id).addClass("urse").siblings().removeClass("urse");
	$("#nav_" + type_id + "_1").siblings().hide();
	$("#nav_" + type_id + "_1").show();
	$("#nav_main").show();
};

resource_attribute_mana.prototype.modify1_show = function(){
	var self = this;
	$(".modify1").click(function(){
		var id = this.id;
		$("#modify_id").val($("#"+id+"_id").val());
		$("#modify1_name").val($("#"+id+"_name").html());
		$('#popDiv').show();
		$('#bg').show();
		$('#popIframe').show();
	});
};

resource_attribute_mana.prototype.del = function() {
	$(".delete").click(function(){
		if(confirm("您确定要删除此属性吗？属性删除之后可能会导致已有资源找不到！！！")) {
			var type_id = $("#type_id").val();
			window.location.href="/Wms/Resource/Resourceattributemanage/delete_attribute/option_str/"+type_id+"_3/id/"+this.id;
		}
	});
};

resource_attribute_mana.prototype.close1 = function(){
	$("a[class='close_icon'],input[value='取消']").click(function() {
		$('#popDiv').hide();
		$('#bg').hide();
		$('#popIframe').hide();
		$('#popDiv1').hide();
		$('#bg1').hide();
		$('#popIframe1').hide();
		$('#popDiv3').hide();
		$('#bg3').hide();
		$('#popIframe3').hide();
		$('#popDiv2').hide();
		$('#bg2').hide();
		$('#popIframe2').hide();
	});
};

resource_attribute_mana.prototype.add1_show = function(){
	$("input[value='添加']").click(function(){
		$('#popDiv1').show();
		$('#bg1').show();
		$('#popIframe1').show();
	});
	
	$("input[id='5_1']").click(function(){
		$('#popDiv2').show();
		$('#popIframe2').show();
		$('#bg2').show();
	});
};

resource_attribute_mana.prototype.add = function() {
	var self = this;
	$("input[class='qd_btn'][value='添加']").click(function(){
		var type_id = $("#type_id").val();
		var value = $("#add1_name").val();
		if(type_id == 5) {
			var value = $("#add2_name").val();
			var product_id = $("#add_select option:selected").val();
			if(product_id == 0) {
				alert("请选择资源类型！");
				return false;
			}
		}else{
			$("#add_select").val(0);
		}
		
		if($.trim(value) == '') {
			alert("请输入属性名称");
			return false;
		}
		
		var parm = {};
		parm.type = type_id;
		parm.value = value;
		parm.product_id = product_id;
		 $.ajax({   
	           type:"post",   
	           url:"/Wms/Resource/Resourceattributemanage/add_info",   
	           dataType:"json",   
	           data:parm,   
	           success:function(msg){   
	              alert(msg.info);
	              window.location.href="/Wms/Resource/Resourceattributemanage/show_list/option_str/" + type_id +"_4";
	           }   
	        });   
	});
};

resource_attribute_mana.prototype.modify = function() {
	var self = this;
	$("input[class='qd_btn'][value='修改']").click(function(){
		var type_id = $("#type_id").val();
		var value = $("#modify1_name").val();
		var id = $("#modify_id").val();
		if(type_id == 5) {
			var value = $("#modify2_name").val();
			var product_id = $("#modify_select option:selected").val();
			if(product_id == 0) {
				alert("请选择资源类型！");
				return false;
			}
		}
		
		if($.trim(value) == '') {
			alert("请输入属性名称");
			return false;
		}
		
		var parm = {};
		parm.id = id;
		parm.type = type_id;
		parm.value = value;
		parm.product_id = product_id;
		 $.ajax({   
	           type:"post",   
	           url:"/Wms/Resource/Resourceattributemanage/modify_info",   
	           dataType:"json",   
	           data:parm,   
	           success:function(msg){   
	              alert(msg.info);
	              window.location.href="/Wms/Resource/Resourceattributemanage/show_list/option_str/" + type_id +"_4";
	           }   
	        });   
	});
};

resource_attribute_mana.prototype.modify2_show = function(){
	$(".modify2").click(function(){
		var id = this.id;
		$("#modify2_name").val($("#"+id+"_name").html());
		var product_id = $("#"+id+"_select").val();
		$("#modify_id").val($("#"+id+"_id").val());
		$("#modify_select").val(product_id);
		$('#popDiv3').show();
		$('#popIframe3').show();
		$('#bg3').show();
	});
};



$(document).ready(function(){
	new resource_attribute_mana();
});