$(document).ready(function(){


});

//显示模板层
	function divTplShow(obj,class_code){
	var Pos=$.getPos(document.getElementById('show'+obj));
		Pos.y=Pos.y;
		Pos.x=Pos.x-1;
	$('#downList_div2').width(600).height(400);
		$('#downList_div2').css({
			top:Pos.y,left:Pos.x
		});
		$("#downList_div2").show();
		$("#downList_Title2>span").click(function(){
			$('#downList_div2').hide();
		});
		//调取课程表里模板图片
		$.ajax({
			type:"POST",
			url:"/Homeclass/Curriculum/ajaxGetCurrSkinList",
			data:"class_code="+class_code,
			success:function(msg){
				msg=msg.substr(0,msg.length-1);
				if(msg){
					var skinData;
					$("#tempShow").empty();
					var option = msg.split(",");
					$("#tempShow").append("<ul class='city-list'>");
					for(i=0;i<option.length;i++){
						 skinData = option[i].split("|"); 
						 
						$("#tempShow").append("&nbsp;<a href=\"#\" onclick=\"javascript:changeTemp('"+skinData[0]+"','"+skinData[1]+"');\"><img src="+skinData[0]+" width=\"120\" height=\"120\" border=\"0\"/>&nbsp;</a>");
					}
					$("#tempShow").append("</ul>");
				}
			}
		});
}

//更改模板
function changeTemp(skinUrl,skinId){
	$("#PicSelect").attr("style","background:url("+skinUrl+")");
	$("#inputbg").attr("value",skinId);
	$.ajax({
		type:"POST",
		url:"/Homeclass/Curriculum/ajaxSaveTemplate",
		data:"skinId="+skinId,
		success:function(msg){
			if(msg=="success"){
				needtoLogTip("模板更换成功！");
			}
		}
	});

	$('#downList_div2').hide();
}

function setCurriculum(subjectname,position,rowsid,dataSubject)	{
	var origin = artDialog.open.origin;
	var aValue = subjectname;

	switch(position){
		case "am" :
			var input = origin.document.getElementById('am'+rowsid);
			origin.$("#clspan"+rowsid).attr("value",dataSubject);
			input.innerHTML = aValue;
			break;
		case "pm" :
			var input = origin.document.getElementById('pm'+rowsid);
			origin.$("#rgspan"+rowsid).attr("value",dataSubject);
			input.innerHTML = aValue;

		break;
	
	}
	art.dialog.close();	
	
}
//提交科目方法
function ajaxSubject(){
	if($("#subject").val()==''){
		needtoLogTip('科目名称不能为空');return false;
	}
	//添加科目
	$.ajax({
			type:"POST",
			url:"/Homeclass/Curriculum/ajaxSubjectAdd",
			data:"subject="+$("#subject").val(),
			success:function(msg){
				if(msg=='fail'){
					needtoLogTip('科目已存在');
					return false;
				}else{
				if(msg=='success')
					window.location.reload();
				}
			}
		});
}

function ajaxSubjectDelete(delsubjectName,classcode){
	if(confirm('确认删除当前自定义课程吗？')){
			paramobj = {
				subjectName:encodeURIComponent(delsubjectName),
				classcode:classcode
			};
				
			param = $.param(paramobj);

			$.ajax({
			type:"POST",
			url:"/Homeclass/Curriculum/ajaxSubjectDelete",
			data: param,
			success:function(msg){
				alert('科目已被删除!');
				window.location.reload();
			}
		});
	}
}

function divShow(obj,position){
	art.dialog.open('/Homeclass/Curriculum/ajaxCurriculum/rowsid/'+obj+'/position/'+position);
}

//为td赋值并把值保存到隐藏域里
function set_tdKvalue(obj,objsetK){
	//把选中的值放在span里
	document.getElementById("clspan"+objsetK).innerHTML = obj;
	//为隐藏域赋值
	$("#span"+objsetK).attr("value",obj);
	$("#rgspan"+objsetK).attr("value",obj);
	$('#downList_div').hide();
}

function classKcb_box(){
	var contentValue = $("#classKcb_box").html();
	var dialog = art.dialog({
		follow: document.getElementById('followTestBtn'),
		title: '调整课节数',
		content: contentValue
	});	
}

//追加课节数
function Uladd(){
	var ulam=document.getElementById('ulam');
	var ulamInput=document.getElementById('am_input_elements');
	var ulamLength = ulam.childNodes.length;
	if(ulamLength >= 20 ){
		needtoLogTip('最多四节课程、不能再加了！');return false;
	}
	var strData="" ;
	var rowsId;
	for (var i=1;i<=5 ;i++ )
	{
		rowsId = parseInt(ulamLength) + parseInt(i);
		strData += "<li onclick=\"javascript:return divShow('"+rowsId+"');\"><span id='am"+rowsId+"'>空</span><input type='hidden' name='amContent[]' id='amContent"+rowsId+"' value='空'></li> ";
	}
	var ula = $("#ulam");
	ula.append(strData);

}

function classNumsSetup(obj,cmd,class_code){
	var ulam=document.getElementById('ulam');
	var ulpm=document.getElementById('ulpm');
	switch(obj){
		case "am" :
			var currentNumsLength = ulam.childNodes.length;
			break;
		case "pm" :
			var currentNumsLength = ulpm.childNodes.length;
			break;
	}
	
	if(cmd=="add"){
		if(currentNumsLength >= 20 ){
			needtoLogTip('最多四节课程、不能再加了！');return false;
		}
	} else if(cmd=="less"){
		if(currentNumsLength <= 15 ){
			needtoLogTip('最少三节课程、不能再减了！');return false;
		}
	}

	$.ajax({
		type:"POST",
		url:"/Homeclass/Curriculum/classCurriculumNums",
		data:"numsCmd="+currentNumsLength+"|"+cmd+"|"+obj+"|"+class_code,
		success:function(msg){
			if(msg=="success"){
				/*switch(obj){
					case "am" :
						alert("上午课程节数调整成功");
						break;
					case "pm" :
						alert("下午课程节数调整成功");
						break;
				}*/
				
				window.location.reload();
			}
		}
	});
}