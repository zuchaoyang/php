jQuery(document).ready(function () {			
	jQuery("#top_rb_menu ul li").click(function () {
		jQuery("#top_rb_menu ul li").attr("class","normal")
		jQuery(this).attr("class","active")
		jQuery(this).attr("c","active")
	});
	jQuery(".tab-nav").click(function(){
		jQuery(".tab-nav").removeClass("hover");
		jQuery("#"+jQuery(this).attr("id")).addClass("hover");
		jQuery(".tab-right").removeClass("hover_t");
		jQuery("#1").addClass("hover_t");
		jQuery("#hidtypeid").val(jQuery(this).attr("id"));
		getpyContentBytypeatt(jQuery(this).attr("id"),1);
	});
	jQuery(".tab-right").click(function(){
		jQuery(".tab-right").removeClass("hover_t");
		jQuery("#"+jQuery(this).attr("id")).addClass("hover_t");
		getpyContentBytypeatt(jQuery("#hidtypeid").val(),jQuery(this).attr("id"));
		
	})
	
});
function getpyContentBytypeatt(pytypeid,pyatt){
	url_g = "/Homeclass/Myclass/showpyContentData/pytype/"+pytypeid +"/pyatt/"+pyatt+ '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: url_g,
		success: function(msg){
			$("#idpycontent").html(msg);
	   }
	});
}
function getpyContentData(pytypeid){
	url_g = "/Homeclass/Myclass/showpyContentData/pytype/"+pytypeid + '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: url_g,
		success: function(msg){
			$("#idpycontent").html(msg);
	   }
	});
}

function getpyContentpyatt(pytypeatt){
	url_g = "/Homeclass/Myclass/showpybytypeatt/pytypeatt/"+pytypeatt + '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: url_g,
		success: function(msg){
			$("#idpycontent").html(msg);
	   }
	});
}

function pysearch(){
	var obj = document.getElementById("pytxt");
	if(pytxt.value==""){
		alert('请输入要搜索的关键词');
		pytxt.focus();
		return false;
	}else{
	
		paramobj = {
			pytxt:encodeURIComponent(obj.value)
				
		};
		
		param = $.param(paramobj);

		$.ajax({
		   type: "POST",
		   url: "/Homeclass/Myclass/showpyContentDataKey",
		   data: param,
		   success: function(msg){
					$("#idpycontent").html(msg);
			   }
		});
	}
}

function copyText(note)   
{ 
	window.clipboardData.setData("Text", note);
	alert('复制成功!');
} 


function scpy(pyid){
	var titlemsg;
	var titlemsgcontent;
	if(confirm("您确认要收藏此评语吗？")){
			url_g = "/Homeclass/Myclass/scpyContentData/pyid/"+pyid + '?' +  Date.parse(new Date());
			$.ajax({
				type: "GET",
				url: url_g,
				success: function(msg){
					if(msg=="moreerror"){
						alert("您的评语库已满，最多收藏30个");
						return false;
					}else{
						alert("已经收到您的评语库");
					}
					//document.getElementById("ajaxstate").value = msg;
			    }
			});
	}
	
	/*
	var dialog = art.dialog({
    title: '收藏我的评语',
    content: '您确认要收藏此评语吗？',
    icon: 'succeed',
    follow: document.getElementById('btn2'),
		ok: function(){
			//alert($("#ajaxstate").val());
			this.title("操作成功").content("已经收到您的评语库").lock().time(1);
			return false;
		}

	});*/
}
