//相册全选
function xcselectall(ctype){
	if(ctype == "a"){
		if(document.getElementById("xcqx").checked){
			document.getElementById("xcqx").checked = false;
		}else{
			document.getElementById("xcqx").checked = true;
		}
	}

	var xcphotos = document.getElementsByName("xcphoto");
	if(xcphotos!=null && xcphotos!="undefined"){
		if(document.getElementById("xcqx").checked){
			for(var ichk=0;ichk<xcphotos.length;ichk++){
				xcphotos[ichk].checked = true;
			}
		}else{
			for(var ichk=0;ichk<xcphotos.length;ichk++){
				xcphotos[ichk].checked = false;
			}
		}
	}
	
}


//新建相册
function JcreateAllbum(account,ctype,classcode){
	art.dialog.open('/Homepzone/Pzonephoto/createxc/user_account/'+account+'/album_type/'+ctype+'/class_code/'+classcode);
}

//编辑相册
function JmodfileAllbum(account,xcid){
	art.dialog.open('/Homepzone/Pzonephoto/createxc/user_account/'+account+'/xcid/'+xcid);
}


//编辑照片
function Jmodfilephotos(account){
	var photoid = $("#bigphotoid").val();
	art.dialog.open('/Homepzone/Pzonephoto/modifyNamePlain/account/'+account+'/photoid/'+photoid);
}

function JSupdatephotoexplain(){

	var photoid = $("#photoid").val();
	var photoname = $("#photoname").val();
	var photoexplain = $("#photoexplain").val();
	if(photoname==""){
		alert('名称不能为空');
		return false;
	}else if(photoexplain==""){
		alert('描述不能为空');
		return false;

	}

	var paramobj;
	var param;
	paramobj = {
		photoid:photoid,
		photoname:encodeURIComponent(photoname),
		photoexplain:encodeURIComponent(photoexplain)
			
	};
	param = $.param(paramobj);
//	var origin = artDialog.open.origin;
	
		$.ajax({
			type:"POST",
			url:"/Homepzone/Pzonephoto/updatephotoexplain",
			data:param,
			success:function(msg){
				switch(msg){
					case "success" :
						alert("修改成功！");
						var origin = artDialog.open.origin;
						var aValue = photoexplain;
						var photonamevalue = photoname;
						
						var input = origin.document.getElementById('photoexplain');
						var inputphotoname = origin.document.getElementById('photoname');

						var inputHname = origin.document.getElementById('bigphotosname_'+photoid);
						var inputHcontent = origin.document.getElementById('bigphotoscontent_'+photoid);


						input.innerHTML = aValue;
						inputphotoname.innerHTML = photonamevalue;

						inputHname.value = photonamevalue;
						inputHcontent.value = aValue;
						art.dialog.close();
					break;
				}
			}
		});	


	//alert($("#ajaxstate").val());
//	this.title("操作成功").content("指定操作成功！").lock().time(1);
	
}

//点击移动弹出相册列表
function showxc(){
	if(document.getElementById("downList_div").style.display =="block"){
		document.getElementById("downList_div").style.display = "none";
	}else{
		document.getElementById("downList_div").style.display = "block";
	}
}


//移动照片前检查
function checkmovephoto(){
	var xcphotos = document.getElementsByName("xcphoto");
	var xcphotoid = document.getElementsByName("xcphotoid");
	
	if(xcphotos!=null && xcphotos!="undefined" && xcphotoid!=null && xcphotoid!="undefined"){
		for(var ichk=0;ichk<xcphotos.length;ichk++){
			if(xcphotos[ichk].checked){
				document.getElementById("movephoto").value=document.getElementById("movephoto").value + xcphotoid[ichk].value+",";
			}
		}
	}

	if(document.getElementById("movephoto").value == ""){
		alert("请选择照片！");
		return false;
	}
	return true;
}

function movephotos(){
	var objselxcid = $("#selxcid").val();
	if(objselxcid==""){
		alert("请选择目标相册！");
		return false;

	}else{
		if(checkmovephoto()){

			document.forms[0].action="/Homepzone/Pzonephoto/movephoto";
			document.forms[0].submit();
		}
	}
}

//移动照片到指定相册
function tomovephoto(movexcid,account,album_id){
	document.forms[0].action="/Homepzone/Pzonephoto/movephotopl/user_account/"+account+"/xcid/"+album_id+"/movexcid/"+movexcid;
	document.forms[0].submit();

}

function classtomovephoto(movexcid,class_code,album_id,movephoto){
	window.location = "/Homeclass/Class/movephotopl/xcid/"+album_id+"/movexcid/"+movexcid+"/class_code/"+class_code+"/movephoto/"+movephoto;
}

//检查是否有照片做封面
function checkxcfm(){
	var xcphotos = document.getElementsByName("xcphoto");
	if(xcphotos!=null && xcphotos!="undefined"){
		for(var ichk=0;ichk<xcphotos.length;ichk++){
			if(xcphotos[ichk].checked){
				document.getElementById("xcfm").value=xcphotos[ichk].value;
				break;
			}
		}
	}

	if(document.getElementById("xcfm").value == ""){
		alert("请选择图片！");
		return false;
	}
	return true;
}

//设置相册封面
function setxcfm(){
	if(checkxcfm()){
		document.forms[0].action="/Homepzone/Pzonephoto/setxcfm";
		document.forms[0].submit();
	}
}


//检查是否有照片要删除
function checkdelphoto(){
	var xcphotos = document.getElementsByName("xcphoto");
	var xcphotoid = document.getElementsByName("xcphotoid");
	var xcphotosbig = document.getElementsByName("xcphotobig");
	
	if(xcphotos!=null && xcphotos!="undefined" && xcphotoid!=null && xcphotoid!="undefined" && xcphotosbig!=null && xcphotosbig!="undefined"){
		for(var ichk=0;ichk<xcphotos.length;ichk++){
			if(xcphotos[ichk].checked){
				document.getElementById("delphoto").value=document.getElementById("delphoto").value + xcphotoid[ichk].value+",";
				document.getElementById("delphoto_str").value=document.getElementById("delphoto_str").value + xcphotos[ichk].value+","+ xcphotosbig[ichk].value+",";
			}
		}
		document.getElementById("delphoto_str").value = ","+document.getElementById("delphoto_str").value;
	}

	if(document.getElementById("delphoto").value == ""){
		alert("请选择照片！");
		return false;
	}
	
	return true;
}

//2012-3-21 by lyt
function cancelClassAlbumShare(albumid){
	if(confirm("确定要删除吗")){
		window.location="/Homeclass/Class/cancelClassAlbumShare/albumid/"+albumid;
	}

}



//删除相册照片列表使用
function deletexcphoto(){
	if(checkdelphoto()){
		if(confirm("确定删除选择的照片吗？")){
			document.forms[0].action="/Homepzone/Pzonephoto/deletexcphoto";
			document.forms[0].submit();
		}
	}
}

function deleteClassphotos(){
	if(checkdelphoto()){
		if(confirm("确定删除选择的照片吗？")){
			document.forms[0].action="/Homeclass/Class/deletexcphoto";
			document.forms[0].submit();
		}
	}
}

function deleteClasssphotos(){
	if(checkdelphoto()){
		if(confirm("确定删除当前照片吗？")){
			document.forms[0].action="/Homeclass/Class/deletexcphotopl";
			document.forms[0].submit();
		}
	}
}


/*评论相关操作**********************************************************/

//获取评论内容
function getphotopluncontent(){
	var photo_id = document.getElementById("bigphotoid").value;
	url_g = "/Homepzone/Pzonephoto/tophotoplun/delphoto/"+photo_id + '?' +  Date.parse(new Date());

	$.ajax({
		type: "GET",
		url: url_g,
		success: function(msg){
			//alert(msg);
			$("#disphotopluns").html(msg);
	   }
	});
}

function getphotopluncontent_space(spaceid){
	url_g = "/Homepzone/Pzonephoto/tophotoplun/user_account/"+spaceid+"/delphoto/"+document.getElementById("delphoto").value + '?' +  Date.parse(new Date());
	//alert(url_g);
	$.ajax({
		type: "GET",
		url: url_g,
		success: function(msg){
			$("#disphotopluns").html(msg);
	   }
	});
}


//发表评论
function btnplun(plun_user,photo_user){
	var friendaccount = photo_user;
	var user_account = plun_user;
	
	var photo_id = document.getElementById("bigphotoid").value;
	
	var ms = $.trim($("#msgcontent").val());
	if(ms==""){
			art.dialog.alert("请输入您要评论的内容");
			return false;
	
	}
	if(ms.length > 180){
			art.dialog.alert("您输入的内容过多！");
			return false;
	}else{
		$.ajax({
			type: "POST",
			url: "/Homepzone/Pzonephoto/addpl",
			data:{photoplun:ms,friendaccount:friendaccount,user_account:user_account,photo_id:photo_id},
			success: function(msg){
				//alert(msg);
				document.getElementById("msgcontent").value = "";
				//document.getElementById("myplun").focus();
				$("#disphotopluns").html(msg);
		   }
		});

	}
}



//删除相片评论
function deletephotoplun(plunid){
	//user_account/{$account}
	if(confirm("确定删除该条评论吗？")){
		$.ajax({
			type: "GET",
			url: "/Homepzone/Pzonephoto/delphotoplun/photo_id/"+document.getElementById("bigphotoid").value+"/plun_id/"+plunid,
			success: function(msg){
				$("#disphotopluns").html(msg);
		   }
		});
	}
}
/*评论相关操作**********************************************************/



function plushalbum(albumid,cmd){
	var titlemsg;
	var titlemsgcontent;
	var origin = artDialog.open.origin;
	var msgtitle,msgcntent;

   if(cmd==1){
		msgtitle= '将相册分享到班级';
		msgcntent= '您确认要将相册分享到班级?';
   }else{
		msgtitle= '取消相册分享';
		msgcntent= '您确认要取消相册分享吗?';
	}

	var origin = artDialog.open.origin;
	var dialog = art.dialog({
    title: msgtitle,
    content: msgcntent,
    icon: 'succeed',
    follow: document.getElementById('pushalbumbtn'),
		ok: function(){
			paramobj = {
				albumid:albumid,
				cmd:cmd
			};
				
			param = $.param(paramobj);
				$.ajax({
					type:"POST",
					url:"/Homepzone/Pzonephoto/thisAlbumShareDo",
					data:param,
					success:function(msg){
						switch(msg){
							case "success" :
								//alert('指定操作成功！');
							window.parent.location.reload();
								origin.document.getElementById("pushalbumbtn").disabled = false;
								origin.document.getElementById("btnsumshare").disabled = false;
								

								var list = art.dialog.list;
								for (var i in list) {
									list[i].lock().time(2);
									list[i].close();
								};
							break;
						}
					}
				});	

			
	
			this.title("操作成功").content("指定操作成功！").lock().time(1);
			

			return false;
		}

});
}


function plushalbumToclass(){

	var contentValue = $("#classAlbumShare_box").html();
	var dialog = art.dialog({
		follow: document.getElementById('pushalbumbtn'),
		title: '分享相册',
		content: contentValue
	});	

}


function tearchsharealbum(albumid){
	var obj = document.getElementsByName("teacher_push_class[]");
	var objLen= obj.length; 
	var objYN;
	var i;
	var chkvalues="";
	var tvalues="";
	objYN=false;
	for (i = 0;i< objLen;i++){
		if (obj[i].checked==true) {
			chkvalues = obj[i].value;
			if(chkvalues!="undefined" && chkvalues!="" ){
				if(tvalues==""){
					tvalues = chkvalues;
				}else{
					tvalues = tvalues+","+chkvalues;
				}
			}
		}
	}
	
	var origin = artDialog.open.origin;
	paramobj = {
		classcode:tvalues,
		albumid:albumid,
		cmd:1
			
	};
	
	param = $.param(paramobj);
		$.ajax({
			type:"POST",
			url:"/Homepzone/Pzonephoto/thisAlbumShareDo",
			data:param,
			success:function(msg){
				switch(msg){
					case "cancel" :
						alert('没有选择班级、无法执行分享');
						break;
					case "success" :
						alert('相册分享成功');
					origin.document.getElementById("pushalbumbtn").disabled = false;
					origin.document.getElementById("btnsumshare").disabled = true;
					window.parent.location.reload();
					break;
				}
					var list = art.dialog.list;
					for (var i in list) {
						list[i].lock().time(2);
						list[i].close();
					};

			}
		});	


	/*
	if(tvalues==""){
		alert('请选择要分享的班级');
		return false;
	}else{
			
	}*/

}

function showphotosdeatial(photoid){
	$("#photoslist").hide();
	$("#photoslisdeatial").show();
		$(function() {
			$('#foo2').carouFredSel({
				prev: '#prev2',
				next: '#next2',
				pagination: "#pager2",
				auto: false
			});
		});

	var objbigphotos = $("#bigphotosh_"+photoid).val();
	var objbigphotosname = $("#bigphotosname_"+photoid).val();
	var objbigphotoscontent = $("#bigphotoscontent_"+photoid).val();
	$("#spanbigphotos").empty();
	var image = new Image();
	image.src = objbigphotos;
	image.onload = function() {
		if(image.width > 500) {
			var width = image.width;
			image.width = 500;
			image.height = (500.0 / width) * image.height;
		}
		$(image).attr({
			'id':'bigtu'
		}).bind('error', function(){
			$(this).src = "/Public/images/head_pics.jpg";
		}).appendTo($("#spanbigphotos"));
	};
	
	$("#photoname").html(objbigphotosname);
	$("#photoexplain").html(objbigphotoscontent);
	document.getElementById("bigphotoid").value = photoid;
	
	getphotopluncontent();
	
			
}