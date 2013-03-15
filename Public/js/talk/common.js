function creatNew(){
	var areatext,titlepattern;
	//titlepattern = /#\uCAE4\uC8EB\uBBB0\uCCE2\uB1EA\uCCE2#/;
	areatext = $(".input_msgK_ctrl .editTextarea").text();
	if(areatext.search("#输入话题标题#")==-1){
		$(".input_msgK_ctrl .editTextarea").insertAtCaret("#输入话题标题#");
		//$(".input_msgK_ctrl .editTextarea").text().selectContents();
	}
	$(".input_msgK_ctrl .editTextarea").focus();
	return false;
}

function upLimit(solute){
	if(solute=="set"){
		limitInterval = setInterval("limitCheck()",10);
	}else{
		clearInterval(limitInterval);
	}
	
	return false;
}
function limitCheck(){
	var areatext,length,limit,more;
	areatext = $(".input_msgK_ctrl .editTextarea").val();
	length = $.trim(areatext).length;
	limit = 140-length;
	more = length-140;
	if(more>0){
		$(".sendFun .countTxt").html("超出<em>"+more+"</em>字");
		return false;
	}else{
		$(".sendFun .countTxt").html("还能输入<em>"+limit+"</em>字");
		return true;
	}
}

function pupLimit(solute){
	if(solute=="set"){
		limitInterval = setInterval("plimitCheck()",10);
	}else{
		clearInterval(limitInterval);
	}
	
	return false;
}
function plimitCheck(){
	var areatext,length,limit,more;
	areatext = $("#xcms").val();
	length = $.trim(areatext).length;
	limit = 60-length;
	more = length-60;
	if(more>0){
		$(".pcountTxt").html("超出<b><font size=3 color=red>"+more+"</font></b>字无法进行保存");
		return false;
	}else{
		$(".pcountTxt").html("还能输入<em>"+limit+"</em>字");
		return true;
	}
}


function upLimitLong(solute){
	if(solute=="set"){
		limitInterval = setInterval("limitCheckLong()",10);
	}else{
		clearInterval(limitInterval);
	}
	
	return false;
}
function limitCheckLong(){
	var areatext,length,limit,more;
	areatext = $(".input_msgK_ctrl2  .editTextarea").val();
	length = $.trim(areatext).length;
	limit = 140-length;
	more = length-140;
	if(more>0){
		$(".sendFun .countTxt").html("超出<em>"+more+"</em>字");
		return false;
	}else{
		$(".sendFun .countTxt").html("还能输入<em>"+limit+"</em>字");
		return true;
	}
}


function photoUpload(){
	var photoUp,photoName,photoExt,nameIndex,extIndex,uploadDoc;
	uploadDoc = window.uploadFrame.document;
	$("#pic", uploadDoc).click();
	
	document.getElementById("pic").value = $("#pic", uploadDoc).val();
	//pic.value=$("#pic", uploadDoc).val();
	photoUp = document.getElementById("pic").value;


	if(photoUp!=""){	
		//alert(photoUp);
		nameIndex = photoUp.lastIndexOf("\\")+1;
		photoName = photoUp.substring(nameIndex);
		extIndex = photoName.lastIndexOf(".")+1;
		photoExt = photoName.substring(extIndex);
		
		ajaxpostHandle = [0, photoName, uploadDoc];
		ajax("/Homeclass/Stalkabout/ajaxUpCheck", "fileext="+photoExt, photoUploadBack);
	}
	return false;
}
//打印对象内容
//function dump_obj(myObject) {  
//	  var s = "";  
//	  for (var property in myObject) {  
//	   s = s + "\n "+property +": " + myObject[property] ;  
//	  }  
//	  alert(s);  
//	}  
function photoUploadBack(){
	var photoName = ajaxpostHandle[1];
	var uploadDoc = ajaxpostHandle[2];
	ajaxpostHandle = 0;
	//start upload
	$(".uploadPic .ico_pic").css("display", "none");
	$(".uploadPic .fun_txt").css("display", "none");
	$(".uploadPic .loading").css("display", "");
	uploadDoc.getElementById("picform").action = "/Homeclass/Stalkabout/ajaxPhotoUpload";
	$("#picform",uploadDoc).ajaxSubmit(function(responsetext){
		uploadDoc.location.reload(true);
		switch($.trim(responsetext)){
			case "successed":
				$(".uploadPic .loading").css("display", "none");
				$(".uploadPic .ico_pic").css("display", "");
				$(".uploadPic .filename").text(photoName);
				$(".uploadPic .filename").css("display", "");
				$(".uploadPic .close").css("display", "");
				var areatext = $(".input_msgK_ctrl .editTextarea").text();
				if($.trim(areatext).length==0){
					$(".input_msgK_ctrl .editTextarea").insertAtCaret("#分享照片#");
				}
				$(".input_msgK_ctrl .editTextarea").focus();
				$(".uploadPic .close").bind('click', function(){
					document.getElementById("pic").value = "";
					$(".uploadPic .filename").text("");
					$(".uploadPic .filename").css("display", "none");
					$(".uploadPic .close").unbind("click");
					$(".uploadPic .close").css("display", "none");
					$(".uploadPic .fun_txt").css("display", "");
					areatext = $(".input_msgK_ctrl .editTextarea").text();
					if($.trim(areatext)=="#分享照片#"){
						$(".input_msgK_ctrl .editTextarea").text("");
					}
					
				});
				break;
			default:
				document.getElementById("pic").value = "";
				alert('图片过大、请选择500K以内图片重新上传');
				$(".uploadPic .loading").css("display", "none");
				$(".uploadPic .ico_pic").css("display", "");
				$(".uploadPic .filename").text('');
				$(".uploadPic .filename").css("display", "");
				$(".uploadPic .close").css("display", "none");
				$(".uploadPic .fun_txt").css("display", "");

				break;
		}
	});
	return false;
}

function faceList(){
	var offset,facehtml;
	facehtml = $("#facelist").html();
	$("#showface").html(facehtml);
	$("#showface").css("display","");
	$("#showface").css({top:25,left:0});
	$("#showface").bind("click", function(){
		facehide();
	});
	$("#showface .close img").bind("mouseover", function(){
		$(this).attr("src","/Public/local/images/face/close_hover.jpg");
	});
	$("#showface .close img").bind("mouseout", function(){
		$(this).attr("src","/Public/local/images/face/close.jpg");
	});
	
	$("#showface li").each(function(){
		$("img",this).bind("click", function(){
			title = $(this).attr("alt");
			$(".input_msgK_ctrl .editTextarea").insertAtCaret("/"+title);
		});
	});
	
	return false;
}
function facehide(){
	$("#showface li").each(function(){
		$("img",this).unbind("click");
	});
	$("#showface .close img").unbind("mouseover");
	$("#showface .close img").unbind("mouseout");
	
	$("#showface").unbind("click");
	$("#showface").html("");
	$("#showface").css("display","none");
	return false;
}
function msgSend(objthis){

	var msg,param,paramobj,class_code;
	var gettype;
	msg = $.trim($("#msgcontent").val()).replace("#输入话题标题#","");
	objpic = $.trim($("#pic").val());
	if(!(msg.length>0)){
		needtoLogTip("请输入您要说说内容");
		return false;
	}else if( $.trim(msg).length > 140){
		needtoLogTip("错误：您输入的内容超出限制！");
		return false;
	
	}
	class_code = $("#class_code").val();
	paramobj = {
		msg:encodeURIComponent(msg),
		pic:encodeURIComponent(objpic),
		sendwhere:objthis,
		sendclass_code:class_code
			
	};
		
	param = $.param(paramobj);

	$.ajax({
	   type: "POST",
	   url: "/Homeclass/Stalkabout/sTalkSaveComplete",
	   data: param,
	   success: function(status){
		   status = $.trim(status);
		  
		   switch(status){
		       case "successed":
				   document.getElementById("msgcontent").value = "";
				   document.getElementById("pic").value = "";
					
					$(".uploadPic .filename").text("");
					$(".uploadPic .filename").css("display", "none");
					$(".uploadPic .close").unbind("click");
					$(".uploadPic .close").css("display", "none");
					$(".uploadPic .fun_txt").css("display", "");
					//update content list
					$.ajax({
					   type: "POST",
					   url: "/Homeclass/Stalkabout/getNewTalkMsg",
					   data: {gettype:objthis},
					   success: function(data){
						   var dataPattern = /<m_id>(.*)<m_id><m_userid>(.*)<m_userid><m_msgcontent>(.*)<m_msgcontent><m_subdate>(.*)<m_subdate><m_plnum>(.*)<m_plnum><m_headimg>(.*)<m_headimg><m_client_name>(.*)<m_client_name>/i;
						   var arr = data.match(dataPattern);
						   var msgid,userid,username,msgcontent,msgphoto,msgdate,msgtime,msgplnum;
						   msgid = arr[1]; userid = arr[2]; msgcontent = URLdecode(arr[3]);
						   msgdate = arr[4];  msgplnum = arr[5];msgheadimg = arr[6];msgclient_name = arr[7];
							switch(objthis){
								case "sTalk":
								case "spacehome" :
										var newnode ="<span class='blank10'></span><div class=\"m_message\" id=\"msgitem_"+msgid+"\">";
											newnode +="<div class=\"m_message_l\">";
												newnode +="<a href='/Homeuser/Index/spacehome/spaceid/"+userid+"' title='访问Ta的空间' target='_blank'><img src=\""+msgheadimg+"\" width=\"60\" height=\"60\" style='padding-left:10px;' onerror=\"this.src='/Public/local/images/head_pics.jpg'\"/></a> ";
											newnode +="</div>";
											newnode +="<div class=\"m_message_r\">";
												newnode +="<h3 style='padding-left:10px;'><strong>"+msgclient_name;
												newnode +=msgdate+"：刚刚发布了新鲜事</strong>";
												newnode +="</h3>";
												newnode +="<div class=\"m_message_rt\"><p>";
													newnode +=msgcontent;
													newnode +="</p>";
													//newnode +="<div class=\"m_message_rm\">";
													//	newnode +="<span><a href=\"#\" title=\"删除\" onclick=\"return deleteSay('"+msgid+"','space');\" ><font color='#889DB6'>删除</font></a></span>";
													//	newnode +="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" title=\"评论\" onclick=\"return commentXXX(this, "+msgid+",'"+userid+"','sTalk');\" ><font color='#889DB6'>发表评论</font></a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" class='link1' title=\"查看评论\"  onclick=\"return listcomment(this, "+msgid+");\">评论</a>(<span id=\"pcount_"+msgid+"\">"+msgplnum+"</span>)↓";
													//	newnode +="<div id=\"plist_"+msgid+"\" class=\"plist\" style='display:none'></div>";
													//newnode +="</div>";
												newnode +="</div>";
											newnode +="</div>";
											newnode +="<div class=\"kong\"></div>";
										newnode +="</div>";


									break;
								
								case "sTalkBJ" :
									var newnode ="<div class='m_message' id=\"msgitem_"+msgid+"\">";
									newnode +="<div class='m_message_l'><a href='/Homeuser/Index/spacehome/spaceid/"+userid+"' title='访问Ta的空间' target='_blank'><img src=\""+msgheadimg+"\" onerror=\"this.src='/Public/local/images/head_pics.jpg'\" width=60 height=60 /></a></div>";
									newnode +="<div class='m_message_r'>";
									newnode +="<h3 style='padding-left:10px;'><strong>";
									newnode +="&nbsp;&nbsp;<a href='/Homeuser/Index/spacehome/spaceid/"+userid+"' title='访问Ta的空间' target='_blank'>"+msgclient_name+"</a> ";
									newnode +=""+msgdate+" ：在班级发布了新鲜事！</strong></h3>";
									newnode +="<div class='m_message_rt'>";
									newnode +="<p>"+msgcontent;
									newnode +="</p>";
									newnode +="</div>";
											//newnode +="<div class=\"m_message_rm\">";
												//newnode +="<span><a href=\"#\" title=\"删除\" onclick=\"return deleteSay('"+msgid+"','home');\" ><font color='#889DB6'>删除</font></a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
											//	newnode +="<a href=\"#\" title=\"评论\" onclick=\"return commentXXX(this, "+msgid+",'"+userid+"','sTalkBJ');\" ><font color='#889DB6'>发表评论</font></a>&nbsp;&nbsp;&nbsp;<a href=\"#\" class='link1' title=\"查看评论\"  onclick=\"return listcomment(this, "+msgid+");\">评论</a>(<span id=\"pcount_"+msgid+"\">"+msgplnum+"</span>)↓";
											//newnode +="</div>";
										//	newnode +="<div id=\"plist_"+msgid+"\" class=\"plist2\" style='display:none'></div>";
	
									newnode +="</div>";
									newnode +="<div class='kong'></div>";
									newnode +="</div>";
									break;
							}
							//alert(newnode);
						  $("#content").prepend(newnode);
						  $("#msgitem_"+msgid).fadeTo(1, 0);
						  $("#msgitem_"+msgid).show(200);
						  $("#msgitem_"+msgid).fadeTo(2000, 1);
						 // $(".m_message:last").remove();
						   //alert("发布成功");
					   }
					});
			   		break;
				case "nologin":
			   		alert("请登录");
			   		break;
				case "nomsg":
			   		alert("请至少填写点内容");
			   		break;
				case "nocookie":
			   		alert("cookie错误,请重试");
			   		break;
				case "failed":
				default:
					alert("发布失败,请重试");
					break;
		   }
	   }
	});
	return true;
}

function commentXXX(obj, msgid,accountid,sendwhere){
	var offset;
	$("#comment #pl").val("");
	$("#comment #msgid").val(msgid);
	$("#comment #placcount").val(accountid);
	$("#comment #sendwhere").val(sendwhere);
	
	offset = $(obj).offset();
	$("#comment").hide(200, function(){
		$("#comment").css({top:offset.top+15, left:offset.left-300});
		$("#comment").show(200, function(){
			$("#comment #pl").focus();
		});
	});
	return false;
}

/*说说统一评论发布及回复JS代码**************************************************************************/
function commentsubmit(){
	var msg,msgid,param,paramobj,placcount,sendwhere;
	msg = $.trim($("#comment #pl").val());
	if(!(msg.length>0)){
		needtoLogTip("请输入评论内容");
		return false;
	}else if( $.trim(msg).length > 140){
		needtoLogTip("错误：您输入的内容超出限制，不多于200字！");
		return false;
	
	}
	msgid = $("#comment #msgid").val();
	placcount = $("#comment #placcount").val();
	sendwhere = $("#comment #sendwhere").val();

	paramobj = {
		msg:encodeURIComponent(msg),
		msgid:msgid,
		placcount:placcount,
		sendwhere:sendwhere
	};
	param = $.param(paramobj);
	ajaxpostHandle = [0, "pcount_"+msgid];

	ajax("/Homeclass/Stalkabout/sTalkCommentSub", param, commentsubmitBack);
	return true;
}
function commentsubmitBack(){
	var showid = ajaxpostHandle[1];

	//ajax update plnum
	var num = parseInt($("#"+showid).text());
	num = num+1;
	$("#"+showid).text(num);
	art.dialog.tips('您的评论发表成功');
	ajaxpostHandle = 0;
	hidecomment();
	return false;
}
function listcomment(obj, msgid){
	if(ajaxpostHandle != 0) {
		return false;
	}
	var ajaxForm = createForm("/Homeclass/Stalkabout/commentlist");
	var input = addInput("hidden", "msgid", msgid, ajaxForm);
	ajaxpostHandle = ["listcommentBack", "plist_"+msgid];
	ajaxpost();
	destroyForm(ajaxForm);
	return false;
}

function listcommentBack(content){
	var showid = ajaxpostHandle[1];
	var formstatus = '__csubmit';
	var showcontent = unescape(content);
	if(showcontent == 'null') {
		showcontent = "暂无评论";
	}
	$("#"+formstatus+" .content").html(showcontent);
	$("#"+formstatus+" .toolbar span").html('<a href="#" title="close" onclick="return hideplist(\''+showid+'\');">关闭</a>');
	var inserthtml = $("#"+formstatus).html();
	
	$("#"+showid).html(inserthtml);
	$("#"+showid).show(200);
	ajaxpostHandle = 0;
	return false;
}
/*说说统一评论发布及回复JS代码结束**********************************************************************/


function comment(obj, msgid,accountid){
	var offset;
	$("#comment #pl").text("");
	$("#comment #msgid").val(msgid);
	$("#comment #placcount").val(accountid);
	
	offset = $(obj).offset();
	$("#comment").hide(200, function(){
		$("#comment").css({top:offset.top+15, left:offset.left});
		$("#comment").show(200, function(){
			$("#comment #pl").focus();
		});
	});
	return false;
}



function hidecomment(){
	$("#comment").hide(200);
	$("#comment #pl").text("");
	return false;
}
function hideplist(showid){
	$("#"+showid).html("");
	$("#"+showid).hide(200);
	return false;
}


function toAddfriend(){
	//art.dialog.open('/Homepzone/Pzonephoto/createxc/user_account/');
	var contentValue = $("#friendAdd").html();
	var dialog = art.dialog({
		follow: document.getElementById('btnaddfriend'),
		title: '添加好友',
		content: contentValue
	});	
}


function tofriendAddlist(userid,thisUserType){
	
	document.getElementById("to_account").value = userid;
	document.getElementById("UserType").value = thisUserType;
	var contentValue = $("#friendAddlist").html();
	
	var dialog = art.dialog({
		follow: document.getElementById('btnaddfriend'+userid),
		title: '添加好友',
		content: contentValue
	});	
}



//添加好友发送
function addFriendSubmit(userid,thisUserType){
	if (userid=="")
	{
		userid = document.getElementById("to_account").value;
		thisUserType = document.getElementById("UserType").value;

	}

	var msg,param,paramobj;
	msg = $.trim($("#msg").val());
	if(msg==""){
		art.dialog.alert("请输入一点附加信息吧");
		return false;
	}else{
		paramobj = {
			msg:encodeURIComponent(msg),
			reacccount:userid,
			thisUseType:thisUserType
		};
		param = $.param(paramobj);
		$.ajax({
			type:"POST",
			url:"/Homefriends/Friends/addfriendrequest",
			data:param,
			success:function(msg){
				switch(msg){
					case "disabled" :
						art.dialog.alert("已经是你好友了、不要重复请求了");
						break;
					case "haverequest" :
						art.dialog.alert("请不要重复发送好友请求...");
						break;
					case "addfriendok" :
						art.dialog.alert("好友请求已经发送、等待好友确认...");
						break;
					case "error" :
						art.dialog.alert("系统未知错误、请联系管理员...");
						break;

				}
				
			}
		});	
	}
}





function addFriendBox(obj, logined,thisUserType){
	if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}
	var newbox, content;
	content = $("#friendAdd").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}




function addFriendBack(){
	var strNote;
	strNote = "好友请求发送成功,两秒后关闭页面。。。";
	$("#"+floatBox+" .float_box_content").html(strNote);
	setTimeout(destroyBox, 2000);
	return false;
}



/*班级相册使用评论回复*/
function classPhotoplunsing(obj, plun_id,add_account){
	if(!add_account){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}
	var newbox, content;
	
	document.getElementById("curhfuser").value = add_account;
	document.getElementById("plid").value = plun_id;
	content = $("#classPhotoplunsing_box").html();
	
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}
/*
function Photoplunsingsubmit(){
	var msg,param,paramobj;
	
	msg = $.trim($("#"+floatBox+" .textarea").text());
	//alert(msg);
	var plid = $.trim($("#plid").val());
	var friendaccount = $.trim($("#friendaccount").val());
	var account = $.trim($("#account").val());
	var photo_id = $.trim($("#photo_id").val());
	var xcid = $.trim($("#xcid").val());
	var class_code = $.trim($("#class_code").val());
	var curhfuser = $.trim($("#curhfuser").val());
	
	if(curhfuser==friendaccount){
			alert('您不能回复自己！');
		return false;
	}

	paramobj = {
		photoplsing:encodeURIComponent(msg),
		plid:plid,
		friendaccount:friendaccount,
		account:account,
		photo_id:photo_id,
		xcid:xcid,
		class_code:class_code
	};
	param = $.param(paramobj);
	ajax("/Homeclass/Class/classphotoplhf/", param, publicBack);

	return true;
}tododel*/

function publicBack(){

	var strNote;
	strNote = "发送成功,两秒后关闭页面。。。";
	$("#"+floatBox+" .float_box_content").html(strNote);
	setTimeout(destroyBox, 2000);
	window.loation.reload();
	return false;
}


/*相册使用*/

function MovephotoBox(obj, logined,thisUserType){
	/*if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}*/
	var newbox, content;
	content = $("#move_photo_ajax").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}



function mod_exName_ajax(obj, logined){
	if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}
	var newbox, content;
	content = $("#mod_exName_ajax").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}


/*相册使用*/
function ajax_classChange(obj, logined){
	if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}
	var newbox, content;
	content = $("#ajax_classChange").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}

function modifyphotoExBack(){
	var strNote;
	strNote = "修改完成,两秒后关闭页面。。。";
	$("#"+floatBox+" .float_box_content").html(strNote);
	setTimeout(destroyBox, 2000);
	return false;
}

/*************************************/


/*************/
function addFriendGroup(obj, logined){
	if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}

	var newbox, content;
	content = $("#addFriendGroup").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}
/***************/



function modifyMsgBox(obj, logined){
	if(!logined){
		noteBox("请先登录再进行此操作。。。", 2);
		return false;
	}
	var newbox, content;
	content = $("#modifyMsg").html();
	var boxOptions = {
		obj: obj,
		toolbar: true,
		content: content,
		height: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(600);
	return false;
}


function skinBox(obj){
	var content = $("#skinlist").html();
	var boxOptions = {
		obj: obj,
		toolbar: false,
		content: content,
		width: "60px",
		height: "auto",
		overflow: "auto"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	return false;
}
function skinChange(styleid,spaceid){
	var param,paramobj;
	paramobj = {
		styleid:styleid,
		spaceid:spaceid
	};
	param = $.param(paramobj);
	$.ajax({
	   type: "POST",
	   url: "/Homeuser/Index/styleChange",
	   data: param,
	   success: function(status){
			status = $.trim(status);
			switch(status){
					case "success":
						destroyBox();
						window.location.reload(true);
						break;
					case "nologin":
						alert("请登录");
						break;
					case "failed":
					default:
						alert("操作失败,请重试");
						break;
			}
	   }
	});
	return false;
}

function editSay(sayId){
	alert(sayId)
}

/*
function deleteSay(sayId,place){
	if(confirm("确认删除此消息？")){
		location.href="/Homeclass/Stalkabout/deleteComment/sayid/"+sayId+"/place/"+place;
		return true;
	}else{
		return false;
	}
}
*/





//空间添加留言
function ajaxguestbook(spaceid_account){
	var objcontent = $.trim($("#msgcontent").val());

	if(objcontent==""){
		needtoLogTip("请输入要留言的内容");
		return false;
	}
	else if( $.trim(objcontent).length > 140){
		needtoLogTip("错误：您输入的内容超出限制！");
		return false;
	
	}
	else{
		$.ajax({
			type:"POST",
			url:"/Homeuser/Index/addGuestbook",
			data:{msgcontent:$("#msgcontent").val(),spaceid_account:$("#spaceid_account").val()},
			success:function(msg){
			
				if(msg=="nologin"){
						art.dialog.alert("您还没有登录、不能发送留言信息");
					return false;
				}else{
						var origin = artDialog.open.origin;
						origin.document.getElementById("msgcontent").value = "";
					
						art.dialog.tips('留言成功');
					$.ajax({
					type:"POST",
					url:"/Homeuser/Index/newguestbook",
					data:{spaceid:$("#spaceid_account").val()},
						success:function(data){
						
							 var dataPattern = /<guestbook_id>(.*)<guestbook_id><guestbook_type>(.*)<guestbook_type><class_code>(.*)<class_code><to_account>(.*)<to_account><guestbook_content>(.*)<guestbook_content><upid>(.*)<upid><add_account>(.*)<add_account><add_date>(.*)<add_date><plunheadimg>(.*)<plunheadimg><client_name>(.*)<client_name>/i;
							 var arr = data.match(dataPattern);
							 var guestbook_id,guestbook_type,class_code,to_account,upid,add_account,add_date,plunheadimg,client_name;
							   guestbook_id = arr[1];
							   guestbook_type = arr[2];
							   class_code = arr[3];
							   to_account = arr[4];
							   guestbook_content = arr[5];
							   upid = arr[6];
							   add_account = arr[7];
							   add_date = arr[8];
							   plunheadimg = arr[9];
							   client_name = arr[10];
							
							var newnode ="<table width=\"640\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"2\">";
								newnode +="<tr>";
								newnode +="<td width=\"5%\" rowspan=\"2\"><Img  width=\"60\" height=\"60\"src=\""+plunheadimg+"\" onerror=\"this.src='/Public/local/images/head_pics.jpg'\"/></td>";
								newnode +="<td align='left'>"+client_name+"&nbsp;&nbsp;发布时间："+add_date+"</td>";
								newnode +="<td  align=\"right\"><span></span></td>";
								newnode +="<tr style='line-height:22px;'>";
								newnode +="<td colspan=\"3\" align=\"left\"><div class='wordwrap' style=\"white-space:normal;word-break:break-all;width:560px;\">"+guestbook_content+"</div>";
								newnode +=" </td>";
								newnode +="</tr>";
								if (add_account==to_account)
								{
									newnode +="<tr style='line-height:22px;'>";
									newnode +="<td colspan=\"3\" align=\"right\">";
									newnode +="<a href=\"javascript:void(0);\" onclick=\"javascript:spaceGuesthf('"+guestbook_id+"','"+to_account+"');\" id='followTestBtn' >【回复】</a>";
									newnode +="&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' onclick=\"javascript:spaceGuestDel('"+guestbook_id+"','"+to_account+"');\">【删除】</a>";
									newnode +=" </td>";
									newnode +="</tr>";
								}
								newnode +="</table>";
								newnode +="<div class='divline'></div>";
								  $("#contentbooknew").prepend(newnode);
								  $("#msgitem_"+msgid).fadeTo(1, 0);
								  $("#msgitem_"+msgid).show(200);
								  $("#msgitem_"+msgid).fadeTo(2000, 1);


						}
					});
				

				}
			}
		});
	}
}

//设置空间名称
function setupSpaceName(){
	var contentValue = "新名称：<input type='text' name='spaceName' id='spaceName' style='height:20px;' maxlength='10'>&nbsp;<input type='button' name='btnspaceName' id='btnspaceName' value='保存' onclick='javascript:spaceNamesave();'>";
	var dialog = art.dialog({
		follow: document.getElementById('spaceNameBtn'),
		content: contentValue
	});	
}

function spaceNamesave(){
	var param,paramobj;
	var objName = $.trim($("#spaceName").val());

	if(objName=="") {
		needtoLogTip("名称不能为空");
		$("#spaceName").focus();
		return false;
	} else {
		paramobj = {
			spancename:encodeURIComponent(objName)
		};
		param = $.param(paramobj);
		$.ajax({
			type:"POST",
			data:param,
			url:"/Homeuser/Index/setUpdateSpaceName",
			success: function(msg){
				var origin = artDialog.open.origin;
				if(msg=="success"){
					art.dialog.tips('保存成功');

					origin.document.getElementById("idspacename").innerHTML = objName ;
				}else{
					art.dialog.tips('保存失败、数据处理异常请联系管理员');
				}

					var list = art.dialog.list;
					for (var i in list) {
						list[i].lock().time(2);
						list[i].close();
					};
			}
		});
	}
	
}

//回复留言
function spaceGuesthf(guestbookid,to_account){
	document.getElementById("bookcurhfuser").value = to_account;
	document.getElementById("guestbookid").value = guestbookid;
	var contentValue = $("#spaceGuestbookhf").html();
	var dialog = art.dialog({
		follow: document.getElementById('followTestBtn'+guestbookid),
		title: '回复评论',
		content: contentValue
	});	
}

//回复验证
function chkGuestBookContent(){
var objcontent = $.trim($("#photoplsing").val());

	if(objcontent==""){
		art.dialog.alert('请输入您要回复的内容');
		$("#photoplsing").focus();
		return false;
	}else{
		var objcurhfuser  = document.getElementById("bookcurhfuser").value
		var objguestbookid  = document.getElementById("guestbookid").value;
		paramobj = {
			msg:encodeURIComponent(objcontent),
			guestbookid:objguestbookid,
			curhfuser:objcurhfuser
		};
		param = $.param(paramobj);
		$.ajax({
			type:"POST",
			data:param,
			url:"/Homeuser/Index/ajaxbackguestbook",
			success: function(msg){
				//alert(msg);return false;
				var origin = artDialog.open.origin;
				if(msg=="success"){
					
					origin.art.dialog.close();
					art.dialog.tips('您的回复发表成功');
					window.location.reload();

				}else{
					
					art.dialog.tips('发送失败、数据处理异常请联系管理员');
					art.close();
				}
			}
		});
	}
}

//删除留言
function spaceGuestDel(guestbook_id,spaceId){
	
	if(confirm('您确定要删除此留言信息吗？')) {
		paramobj = {
			guestbook_id:guestbook_id,
			spaceId:spaceId
		};
		param = $.param(paramobj);

		$.ajax({
			type:"POST",
			data:param,
			url:"/Homeuser/Index/ajaxguestbookDel",
			success: function(msg){
				if(msg=="success"){
					art.dialog.tips('留言已经删除！');
					window.location.reload();

				}else{
					art.dialog.tips('发送失败、数据处理异常请联系管理员');
					art.close();
				}
			}
		});
	}

}