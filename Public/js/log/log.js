
function changePager(bgurl){
	$("#content").xheditor(false);
	document.getElementById("ContentBg").value=bgurl;
	$(pageInit);
	$('#downList_div3').hide();
}


function showlatterlist(_width,_height){
	var lastModified="Sat,1 Jan 2005 00:00:00 GMT";
	var nowTime=new Date().getTime();
	var url="/Homepzone/Pzonelog/getlatterbg";
	$.get(url,{time:nowTime},function(data){
		var boxHeader='<div id="downList_Title3"><span><img src="/Public/local/images/new/close_it.gif"/></span>选择信纸</div>';
		var _html;
		_html="<span class='blank10'></span><ul class='city-list3'>"+data+"</ul>";
		html_DownList('paperMore',boxHeader,_html,_width,_height);
		$("#downList_div3").show();
		$("#downList_Title3").click(function(){
			$('#downList_div3').hide();
		});
	});
}

function html_DownList(objID,Title,Body,_width,_height){
	var Pos=$.getPos(document.getElementById(objID));
	Pos.y=Pos.y+$('#'+objID).height()+3;
	Pos.x=Pos.x-_width+55;	

	if($('#downList_div3').length==0){
		$('body').append('<div id="downList_div3"></div>');
	}
	$('#downList_div3').html(Title+Body);
	$('#downList_div3').width(_width).height(_height);
	$('#downList_div3').css({
		top:Pos.y,left:Pos.x
	}).fadeIn("slow");
}

function hiddlatterlist(d){
	$("#content").xheditor(false);
	document.getElementById("ContentBg").value=d;
	$(pageInit);
}

//日志分享显示班级列表
function showChkclass(){
	objpush_class = document.getElementById("push_class");
	if(objpush_class.checked){
		$("#chkClass").show();
	}else{
		$("#chkClass").hide();
	}

}



function logwritechk(account){

	var objbln = true;
	window.frames["HtmlEditor"].AttachSubmit();
	var objtitle = $("#log_nametitle").val();
	var objcontent = $("#content").val();
	//alert("editor = " + window.frames["HtmlEditor"]);

	objcontent = objcontent.replace("<P>","");
	objcontent = objcontent.replace("</P>","");
	objcontent = objcontent.replace("<BR>","");
	objcontent= delHtmlTag(objcontent);
	objcontent=objcontent.replace(/&nbsp;/ig, "");
	objcontent = objcontent.replace(/(^\s*)(\s*$)/g,"");

	if($.trim(objtitle)==""){
		alert('请输入日志标题');
		$("#log_nametitle").focus();
		objbln = false;
	}
	
	else if($.trim(objcontent)==""){
		alert('请输入日记内容');
		objbln = false;
	}
	else if (objcontent.length < 20)
	{
		alert("内容太少了");
		return false;
	}	
	return objbln;
}


function dellog(log_id,log_type,pagetype,class_code){
	if(confirm("确定要删除吗")){
		window.location="/Homepzone/Pzonelog/del_log/class_code/"+class_code+"/log_id/"+log_id+"/log_type/"+log_type+"/log_status/"+pagetype;
	}
}

function delClasslog(log_id){
	if(confirm("确定要删除吗？")){
		window.location="/Homeclass/Class/del_log/log_id/"+log_id;
	}
}

function cancelClassLogShare(log_id,class_code){
	if(confirm("确定要删除吗")){
		window.location="/Homeclass/Class/cancelClassLogShare/class_code/"+class_code+"/log_id/"+log_id;
	}

}

function logplunadd(){
	var msg,param,paramobj;
	msg = $.trim($("#msgcontent").val());
	if(!(msg.length>0)){
		needtoLogTip("评论内容不能为空");
		return false;
	}else if( $.trim(msg).length > 140){
		needtoLogTip("错误：您输入的内容超出限制！");
		return false;
	
	}
}




function changelog(liid){
	if(liid == "log"){
		window.location.href="/Homepzone/Pzonelog/mylogindex/log_account/{$log_account}/pagetype/log";			
	}else{
		window.location.href="/Homepzone/Pzonelog/mylogindex/log_account/{$log_account}/pagetype/cgao";
	}
}

//删除相册
function deletexc(xcid){
	if(confirm("确定删除该相册吗？")){
		window.location.href="/Homepzone/Pzonephoto/deletexc/user_account/{$account}/xcid/"+xcid;
	}
}

//创建相册
function createxc(){

	window.location.href="/Homepzone/Pzonephoto/createxc/user_account/{$account}";
}

//上传照片
function upphoto(xcid,userId){
	var str="";
	if(xcid!=""){
		var str="/xcid/"+xcid;
	}
	window.location.href="/Homepzone/Pzonephoto/uploadphoto/user_account/"+userId+str;
}
//删除评论
function delphotoplun(plun_id,account){
	if(confirm("确定删除该条评论吗？")){
		window.location.href="/Homepzone/Pzonephoto/delphotonewplun/plun_id/"+plun_id+"/user_account/"+account;
	}
}

