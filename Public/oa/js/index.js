$(function() {
	$("#up_page").click(function(){
		var schedule_name = $("#schedule_name_json").val();
		var page = $("#page_json").val()-1;
		var datatime = $("#datatime_json").val();
		$.ajax({
			type : "POST",
			url : "/Oa/Index/jsonsearchScheduleinfo",
			dataType : "json",
			data : param,
			success : function(jsonarr) {
				var data = jsonarr.data;
				var err = jsonarr.error;
				if(err.code < 0){
					alert(jsonarr.error.message);
					return false;
				}
			}
		});
	});
	
	$("#exit_btn").click(function(){
		$("#popDiv1,#popIframe1,#bg1").hide();
	});
	
	$("#schedule_title").focus(function(){
		var text = $("#schedule_title");
		if(text.val()=="输入标题内容..."){
			text.val("");
		}
	});
	
	$("#schedule_title").blur(function(){
		var text = $("#schedule_title");
		if(text.val() == ""){
			text.val("输入标题内容...");
		}
	});
	
	$("#ksjs_btn").click(function() {
		if ($("#ksjs").val() == "快速记事哦..." || $("#ksjs").val() == "") {
			alert("请填写你要记录是内容！");
			return false;
		} else {
			var param={};
			var ksjs = $("#ksjs").val();
			if(ksjs.length >600){
				alert("内容不能大于600个字！");
				return false;
			}
			param.ksjs = ksjs;
			$(function() {
				$.ajax( {
					type : "POST",
					url : "/Oa/Index/QuickNotes",
					dataType : "json",
					data : param,
					success : function(data) {
						alert(data.error.message);
						if (data.error.code == 1) {
							location.reload();
						}
					}
				});
			});
		}
	});
	
	$("#ksjs").focus(function() {
		if("快速记事哦..." == $("#ksjs").val()){
			$("#ksjs").val("");
		}
		
	});
	$("#ksjs").blur(function() {
		var ksjs = $("#ksjs").val();
		if (ksjs == "") {
			$("#ksjs").val("快速记事哦...");
		}

	});	
});

