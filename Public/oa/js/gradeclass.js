//加载更多新鲜事
function showlist(){
	var school_id = $("#school_id").val();
	var grade_id = $("#grade_id").val();
	var intlastid  = $("#nextLlimit").val();
	document.getElementById("loadlist_key").innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载更多信息.....';	
	getUrl = "/Oa/Classnaviga/classmove/school_id/"+school_id+"/grade_id/"+grade_id+"/nextLlimit/"+intlastid+ '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: getUrl,
		success: function(msg){
			if (msg!="")
			{
				$('#idpycontent').append(msg) ;
	
			}else{
				document.getElementById("loadlist_key").innerHTML='<span style="color:red;">没有可加载的班级</span>';
				return false;
				//needtoLogTip('已经没有了!');
			}
			document.getElementById("loadlist_key").innerHTML='点击加载更多班级';	
	    }
	});
}
function needtoLogTip(tipMsg){
	art.dialog.alert(tipMsg);
}
var parseJson = function (data) {
    return (new Function('return (' + data + ')'))();
}
function ggao(classcode){
	//window.location.href="/Oa/Classnaviga/classggao/class_code/"+classcode;
	var work = document.getElementById("work"+classcode);
	var move = document.getElementById("move"+classcode);
	var ggao = document.getElementById("ggao"+classcode);
	var bjgg = document.getElementById("bjgg"+classcode);
	var bjdt = document.getElementById("bjdt"+classcode);
	var bjzy = document.getElementById("bjzy"+classcode);
	bjzy.className = "bjgg";
	bjdt.className = "bjgg";
	bjgg.className = "bjdt";
	ggao.innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载信息.....';
	ggao.style.display='block';
	//ggao.style.height='150px';
	work.style.display = "none";
	move.style.display = "none";
	getUrl = "/Oa/Classnaviga/classggao/class_code/"+classcode+'?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		url: getUrl,
		success: function(msg){
			if (msg!="")
			{
				var content = parseJson(msg);
	            var str = "<h3>"+content.news_title+"</h3>";
	                str +="<p>"+content.news_content+"</p>";
	                str +="<ul>"+content.add_date+"</ul>";
	                str +="<ul>"+content.add_account_name+"</ul>";
	            ggao.innerHTML=str;
			}else{
				ggao.style.height="34px";
				ggao.innerHTML="没有公告";
			}
	    }
	});
}
function work(classcode){
	var work = document.getElementById("work"+classcode);
	var move = document.getElementById("move"+classcode);
	var ggao = document.getElementById("ggao"+classcode);
	var bjgg = document.getElementById("bjgg"+classcode);
	var bjdt = document.getElementById("bjdt"+classcode);
	var bjzy = document.getElementById("bjzy"+classcode);
	bjzy.className = "bjdt";
	bjdt.className = "bjgg";
	bjgg.className = "bjgg";
	work.innerHTML='<img src="/Public/local/images/new/iconLoading.gif" align="absmiddle"/>正在加载信息.....';
	work.style.display = "block";
	move.style.display = "none";
	ggao.style.display = "none";
	getUrl = "/Oa/Classnaviga/classwork/class_code/"+classcode+ '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		dataType:"json",
		url: getUrl,
		success: function(msg){
			var selectObj = msg.select;
			if(selectObj == null){
				selectObj = '';
			}
		//var select_arr = select_array(selectObj);
			var contentObj = msg.content;
			var subidstr = msg.subids;
			$("#subid"+classcode).val(subidstr);
		    var subid_arr= new Array(); 
		    subid_arr = subidstr.split(",");
		    $("#work"+classcode).html(selectObj+contentObj);
		    document.getElementById('subcon'+classcode+subid_arr[0]).style.display = "block";
	    }
	});
}
function select(classcode){
	var sub_ids_str = $("#subid"+classcode).val();
	var select_id = $("#select"+classcode).val();
	var subid_arr= new Array(); 
    subid_arr = sub_ids_str.split(",");
    for(var i=0;i<subid_arr.length;i++){
    	if(subid_arr[i]==select_id){
    		document.getElementById('subcon'+classcode+select_id).style.display = "block";
    	}else{
    		document.getElementById('subcon'+classcode+subid_arr[i]).style.display = "none";
    	}
    }
}
function move(classcode){
	var work = document.getElementById("work"+classcode);
	var move = document.getElementById("move"+classcode);
	var ggao = document.getElementById("ggao"+classcode);
	var bjgg = document.getElementById("bjgg"+classcode);
	var bjdt = document.getElementById("bjdt"+classcode);
	var bjzy = document.getElementById("bjzy"+classcode);
	getUrl = "/Oa/Classnaviga/oneclassmove/class_code/"+classcode+ '?' +  Date.parse(new Date());
	$.ajax({
		type: "GET",
		dataType:"json",
		url: getUrl,
		success: function(msg){
			var contentObj = msg.content;
			$("#move"+classcode).html(contentObj);
			bjzy.className = "bjgg";
			bjdt.className = "bjdt";
			bjgg.className = "bjgg";
			work.style.display = "none";
			ggao.style.display = "none";
			move.style.display = "block";
			
	    }
	});
}
/*
function content_array(arr) {
	var i = 1;
	var contentArr = new Array();
	var keys;
	var str='';
	for(var key in arr) { // 这个是关键
		if(typeof(arr[key]) == 'array' || typeof(arr[key]) == 'object') {// 递归调用
			content_array(arr[key]);
		} else {
			if(i == 1){
				keys = arr[key];
				contentArr[keys] = new Array();
			}
			contentArr[keys][key] = arr[key];
			if(i==6){
				i = 1;
			}else{
				i++;
			}
		}
	}
	return contentArr;
}
function select_array(arr1) {
	var contentArr1 = new Array();
	for(var key1 in arr1) { // 这个是关键
		if(typeof(arr1[key1]) == 'array' || typeof(arr1[key1]) == 'object') {// 递归调用
			select_array(arr1[key1]);
		} else {
			contentArr1[key1]=arr1[key1];
		}
	}
	return contentArr1;
}
*/