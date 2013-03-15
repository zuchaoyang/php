function textCls(){
	this.mouse_out  = false;
	this.textObj = $("#text_div");
	this.faceObj = $("#facelist");
	this.default_text_val = "你在干嘛?";
	
//	if(typeof this.text === "undefined") {
//		return ;
//	}
//	this.text.prepend('<textarea id="text_content" name="text_content"  class="text_class">你在干嘛?</textarea>');
	
	this.attachEvent();
}

textCls.prototype.attachEvent=function(){
	var self = this;
	$('#text_content').focus(function(){
		if($.trim($('#text_content').val()) == self.default_text_val){
			$('#text_content').val("");
		}
		
		$('#text_content').removeClass().addClass('status-content inputing');
		$('#char_num').css('display', 'block');
		$('#face_box').css('display', 'block');
		
	});
	//失去光标
	$('#text_content').blur(function(){

		if(self.mouse_out && !$.trim($('#text_content').val())){
			$('#text_content').removeClass().addClass('status-content').val(self.default_text_val);
			$('#char_num').css('display', 'none');
			$('#face_box').css('display', 'none');
		}
	});
	
	$('#text_box').mouseout(function(){
		self.mouse_out = true;
	});
	$('#text_box').mouseover(function(){
		self.mouse_out = false;
	});
	
	$('#text_content').keyup(function() {

		var maxtext = 140;
		var areatext = $.trim($('#text_content').val().toString());
		if(areatext.length > maxtext) {
			$('#text_content').val(areatext.substring(0, maxtext));
		}
		//alert(maxtext - areatext.length);
		$('#char_num').html("还可以输入" + Math.max(maxtext - areatext.length, 0) + "个字。");
	});
	
	$('#_face').click(function(){
		self.faceList(); 

	});
	
	$('#imgUpload').click(function(){
		
		self.imgUpload();
	});
	
	
};


textCls.prototype.faceList=function(){
	var self = this;
	var offset,facehtml;
	facehtml = $("#facelist").html();
	
	$("#showface").html(facehtml);
	$("#showface").css("display", "block");
	$("#showface").css({top:25,left:0});
	$("#showface").bind("click", function(){
		facehide();
	});
	$("#showface .close img").bind("mouseover", function(){
		$(this).attr("src","{$smarty.const.IMG_SERVER}__PUBLIC__/huodong/wenhuachuancheng/images/face/close_hover.jpg");
	});
	$("#showface .close img").bind("mouseout", function(){
		$(this).attr("src","{$smarty.const.IMG_SERVER}__PUBLIC__/huodong/wenhuachuancheng/images/face/close.jpg");
	});
	
	//给每个表情动态绑定 click事件 
	$("#showface li").each(function(){
		$("img", this).bind("click", function(){
			
			var origin = artDialog.open.origin;
			var aValue = this.name;
			var value_old= origin.document.getElementById('text_content').value;  

			self.insertAtCaret(aValue);
			//input.select();
			art.dialog.close();
		});
	});
	
	return false;
};

textCls.prototype.facehide=function(){
	$("#showface li").each(function(){
		$("img",this).unbind("click");
	});
	$("#showface .close img").unbind("mouseover");
	$("#showface .close img").unbind("mouseout");
	
	$("#showface").unbind("click");
	$("#showface").html("");
	$("#showface").css("display","none");
	return false;
};



//在光标处插入表情的标示符
textCls.prototype.insertAtCaret=function(myValue) {
	var $t=$('#text_content');
	var $dome_obj = document.getElementById('text_content');
	if (document.selection) {
		$t.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		$t.focus();
	} else {

		if ($dome_obj.selectionStart || $dome_obj.selectionStart == '0') {
			
			var startPos = $dome_obj.selectionStart;
			var endPos = $dome_obj.selectionEnd;
			var scrollTop = $dome_obj.scrollTop;
			$t.val($t.val().substring(0, startPos) + myValue + $t.val().substring(endPos, $t.val().length));
			$t.focus();
			$t.selectionStart = startPos + myValue.length;
			$t.selectionEnd = startPos + myValue.length;
			$t.scrollTop = scrollTop;
		} else {

			$t.val($t.val() + myValue);
			$t.focus();
		}
	}
};

/*图片上传*/
textCls.prototype.imgUpload=function() {
	var photoUp,photoName,photoExt,nameIndex,extIndex,uploadDoc;
	uploadDoc = window.uploadFrame.document;
	//alert(uploadDoc);
	$("#pic", uploadDoc).click();
                  
};

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


$(document).ready(function(){
  new textCls();
});