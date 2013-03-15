//加载更多班级动态
function showlist(){
	var intclass_code = $("#class_code").val();
	var intnextLlimit  = $("#nextLlimit").val();
	document.getElementById("loadlist_key").innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载更多信息.....';	
	getUrl = "/Homeclass/Classspace/morespacefeedlist/class_code/"+intclass_code+"/nextLlimit/"+intnextLlimit+ '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: getUrl,
		success: function(msg){
			if (msg!="")
			{
				$('#idpycontent').append(msg) ;

			}else{
				needtoLogTip('已经没有了!');
			}
			document.getElementById("loadlist_key").innerHTML='点击加载更多动态';	
	   }
	});
}

//加载更多新鲜事
function showlistXXX(){
	var intdatacount = $("#datacount").val();
	var intnextLlimit  = $("#nextLlimit").val();
		document.getElementById("loadlist_key").innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载更多信息.....';	
		getUrl = "/Homeclass/Stalkabout/morefeedlist/nextLlimit/"+intnextLlimit+ '?' +  Date.parse(new Date());
		$.ajax({
			type: "GET",
			url: getUrl,
			success: function(msg){
				if (msg!="")
				{
					$('#idpycontent').append(msg) ;

				}else{
					needtoLogTip('已经没有了!');
				}
				document.getElementById("loadlist_key").innerHTML='点击加载更多动态';	
		   }
		});
}

//加载更多新鲜事
function showlistXXXspace(){
	var intspaceid = $("#account").val();
	var intlastid  = $("#nextLlimit").val();
		document.getElementById("loadlist_key").innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载更多信息.....';	
		getUrl = "/Homeuser/Index/morefeedlist/account/"+intspaceid+"/nextLlimit/"+intlastid+ '?' +  Date.parse(new Date());
		$.ajax({
			type: "GET",
			url: getUrl,
			success: function(msg){
				if (msg!="")
				{
					$('#idpycontent').append(msg) ;

				}else{
					needtoLogTip('已经没有了!');
				}
				document.getElementById("loadlist_key").innerHTML='点击加载更多动态';	
		   }
		});
}

//加载更多新鲜事
function showlistotherXXX(){
	var intspaceid = $("#spaceid").val();
	var intlastid  = $("#nextLlimit").val();
		document.getElementById("loadlist_key").innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载更多信息.....';	
		getUrl = "/Homeuser/Index/morexxxlist/account/"+intspaceid+"/nextLlimit/"+intlastid+ '?' +  Date.parse(new Date());
		$.ajax({
			type: "GET",
			url: getUrl,
			success: function(msg){
				if (msg!="")
				{
					$('#idpycontent').append(msg) ;

				}else{
					needtoLogTip('已经没有了!');
				}
				document.getElementById("loadlist_key").innerHTML='点击加载更多动态';	
		   }
		});
}

function deleteSay(sayId,place){

	art.dialog.confirm('你确定要删除这条消息吗？', function () {
		location.href="/Homeclass/Stalkabout/deleteComment/sayid/"+sayId+"/place/"+place;
		return true;

	}, function () {
		art.dialog.close();
	});
}