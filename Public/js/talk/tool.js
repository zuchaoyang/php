var limitInterval = 0;
var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
var is_safari = (userAgent.indexOf('webkit') != -1 || userAgent.indexOf('safari') != -1);
var ajaxpostHandle = 0;
var ajaxFormObj = 0;
var floatBox = 0;
function URLdecode(str) {
    var ret = "";
    for(var i=0;i<str.length;i++) {
        var chr = str.charAt(i);
        if(chr == "+") {
            ret += " ";
        }else if(chr=="%") {
            var asc = str.substring(i+1,i+3);
            if(parseInt("0x"+asc)>0x7f) {
                ret += decodeURI("%"+ str.substring(i+1,i+9));
                i += 8;
            }else {
                ret += String.fromCharCode(parseInt("0x"+asc));
                i += 2;
            }
        }else {
            ret += chr;
        }
    }
    return ret;
}
function ajax(url, param, callback){
	if(!param){
		param = "";
	}
	$.ajax({
	   type: "POST",
	   url: url,
	   data: param,
	   success: function(status){
			status = $.trim(status);
			switch(status){
					case "successed":
						setTimeout(callback, 0);
						break;
					default:
						destroyBox();
						noteBox(status, 2);
						break;
			}
	   }
	});
	return true;
}
function createBox(boxOptions){
	//global box id
	floatBox = "float_box";

	var defaultOptions = {
		obj: null,
		toolbar: true,
		content: "",
		width: "300px",
		height: "20px",
		overflow: "auto"
	};
	var basebox,toolbox,boxObj,screenBox,screenObj;

	boxObj = document.getElementById("float_box");
	if(boxObj != null){
		return false;
	}

	screenObj = document.getElementById("boxScreen");
	if(screenObj != null){
		return false;
	}

	screenBox = "<div id=\"boxScreen\"></div>";
	basebox = "<div id=\"float_box\" class=\"float_box\"><div class=\"float_box_content\"></div></div>";

	$("body", document).append(screenBox);
	$("body", document).append(basebox);

	screenObj = $("#boxScreen");
	boxObj = $("#float_box");

	//box params
	var boxParams = {
		obj: (boxOptions.obj!=null && boxOptions.obj!="undefined")?boxOptions.obj:defaultOptions.obj,
		toolbar: (boxOptions.toolbar==false)?boxOptions.toolbar:defaultOptions.toolbar,
		content: (boxOptions.content!=null && boxOptions.content!="undefined")?boxOptions.content:defaultOptions.content,
		width: (boxOptions.width!=null && boxOptions.width!="undefined")?boxOptions.width:defaultOptions.width,
		height: (boxOptions.height!=null && boxOptions.height!="undefined")?boxOptions.height:defaultOptions.height,
		overflow: (boxOptions.overflow!=null && boxOptions.overflow!="undefined")?boxOptions.overflow:defaultOptions.overflow
	};
	//box params check
	if((boxParams.obj==null) && (boxOptions.height=="auto" || boxOptions.width=="auto")){
		alert("height or width must be fixed");
		return false;
	}

	//screen params
	if(is_ie){
		var screenWidth = document.documentElement.offsetWidth;
		var screenHeight = document.documentElement.offsetHeight;
	}else{
		var screenWidth = document.documentElement.clientWidth;
		var screenHeight = document.documentElement.clientHeight;
	}
	var screenScrollTop = document.documentElement.scrollTop;
	var screenScrollLeft = document.documentElement.scrollLeft;
	//style set

	//base style
	screenObj.css({
		position: "absolute",
		width: screenWidth,
		height: screenHeight
	});
	screenObj.css("z-index", 999);
	screenObj.css({top: screenScrollTop+"px", left: screenScrollLeft+"px"});
	//box style
	boxObj.css({
		position: "absolute",
		width: boxParams.width,
		height: boxParams.height,
		overflow: boxParams.overflow,
		padding: "5px",
		background: "C9F1FF",
		border: "#23A8CB solid 2px",
		display: "none"
	});
	boxObj.css("z-index", 999999);
	if(boxParams.obj!=null){
		var offset = $(boxParams.obj).offset();
		boxObj.css({top: offset.top+"px", left: offset.left+"px"});
	}else{
		var bleft = (screenWidth-boxObj.width())/2+screenScrollLeft;
		var btop = (screenHeight-boxObj.height())/2+screenScrollTop;
		boxObj.css({top: btop+"px", left: bleft+"px"});
	}
	$(window).bind("resize", function(){
		boxMove(screenObj, boxObj, boxParams.obj);
	});
	$(window).bind("scroll", function(){
		boxMove(screenObj, boxObj, boxParams.obj);
	});

	//toolBar set
	if(boxParams.toolbar){
		toolbox = "<div class=\"float_box_toolbox\"><input type=\"button\" value=\"关闭\" class=\"close\"/></div>";
		boxObj.prepend(toolbox);
		$(".float_box_toolbox input", boxObj).css({
			float: "right",
			width: "auto",
			height: "auto",
			padding: "2px",
			background: "23A8CB",
			border: "0px",
			cursor: "pointer"
		});
		$(".float_box_toolbox input", boxObj).css("font-weight", "800");
		$(".float_box_toolbox input.close", boxObj).bind("click", function(){
			destroyBox();
		});
	}
	//content
	$(".float_box_content", boxObj).html(boxParams.content);
	//bind full screen click event
	screenObj.bind("click", function(){
		listenBox();
	});
	
	return boxObj;
}
function boxMove(screenObj, boxObj, obj){
	if(obj==null){
		moveBox(screenObj, boxObj);
	}else{
		moveScreen(screenObj);
	}
	return false;
}
function moveScreen(screenObj){
	var left = document.documentElement.scrollLeft;
	var top = document.documentElement.scrollTop;
	screenObj.css({top: top+"px", left: left+"px"});
	return false;
}
function moveBox(screenObj, boxObj){
	if(is_ie){
		var screenWidth = document.documentElement.offsetWidth;
		var screenHeight = document.documentElement.offsetHeight;
	}else{
		var screenWidth = document.documentElement.clientWidth;
		var screenHeight = document.documentElement.clientHeight;
	}
	var left = document.documentElement.scrollLeft;
	var top = document.documentElement.scrollTop;
	screenObj.css({top: top+"px", left: left+"px"});
	var bleft = (screenWidth-boxObj.width())/2+left;
	var btop = (screenHeight-boxObj.height())/2+top;
	boxObj.css({top: btop+"px", left: bleft+"px"});
	return false;
}
function listenBox(){
	destroyBox();
	return true;
}
function destroyBox(){
	$("#"+floatBox).hide(200);
	$("#"+floatBox+" .float_box_toolbox input.close").unbind("click");
	$("#boxScreen").unbind("click");
	$("#"+floatBox).empty();
	$("#"+floatBox).remove();
	$("#boxScreen").remove();
	$(window).unbind("resize");
	$(window).unbind("scroll");
	return true;
}
function $$(id){
	return document.getElementById(id);	
}
function ajaxpost() {	
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $$(ajaxframeid);

	if(ajaxframe == null) {
		if (is_ie && !is_opera) {
			ajaxframe = document.createElement("<iframe name='" + ajaxframeid + "' id='" + ajaxframeid + "'></iframe>");
		} else {
			ajaxframe = document.createElement("iframe");
			ajaxframe.name = ajaxframeid;
			ajaxframe.id = ajaxframeid;
		}
		ajaxframe.style.display = 'none';
		$$('append_parent').appendChild(ajaxframe);
	}
	$$("ajax_form").target = ajaxframeid;
	$$("ajax_form").action = $$("ajax_form").action + '?inajax=1';
	if(ajaxframe.attachEvent) {
		ajaxframe.detachEvent ('onload', ajaxpost_load);
		ajaxframe.attachEvent('onload', ajaxpost_load);
	} else {
		document.removeEventListener('load', ajaxpost_load, true);
		ajaxframe.addEventListener('load', ajaxpost_load, false);
	}
	
	//alert($$("ajax_form").innerHTML);
	$$("ajax_form").submit();
	return false;
}
function ajaxpost_load() {	
	var fnProcess = ajaxpostHandle[0];
	var s = $$('ajaxframe').contentWindow.document.body.innerHTML;
	if(!s){
		s = $$('ajaxframe').contentWindow.document.documentElement.firstChild.nodeValue;
	}
//	if(is_ie || is_moz){
//		var s = $$('ajaxframe').contentWindow.document.body.innerHTML;
//	} else {
//		//var s = $$('ajaxframe').contentWindow.document.body.innerHTML;
//		var s = $$('ajaxframe').contentWindow.document.documentElement.firstChild.nodeValue;
//	}
	s = escape(s);
	setTimeout(fnProcess+'(\''+s+'\')', 0);
	return true;
}
function createForm(action){
	var ajaxForm = document.createElement("form");      
	document.body.appendChild(ajaxForm);
	ajaxForm.method = "post";
	ajaxForm.id = "ajax_form";
	ajaxForm.name = "ajax_form";
	ajaxForm.action = action;
	return ajaxForm;
}
function addInput(type, name, value, formObj){
	var input = formObj.elements["ajax_form_"+name];
	if(input==null){
		input=document.createElement("input");
		input.type = type;
		formObj.appendChild(input);
	}    
	
	input.value = value;
	input.id = "ajax_form_"+name;
	input.name = name;
	return input;
}
function removeInput(inputObj, formObj){
	for(i=0;i<formObj.elements.length;i++){
		formObj.removeChild(formObj.elements[i]);
	}
	formObj.removeChild(inputObj);
	return true;
}
function destroyForm(formObj){
	document.body.removeChild(formObj);
	return true;
}
function noteBox(msg, timeer){
	var boxOptions = {
		toolbar: false,
		content: msg,
		width: "300px",
		height: "25px"
	};
	newbox = createBox(boxOptions);
	$(newbox).show(200);
	setTimeout(destroyBox, timeer+'000');
	return false;
}