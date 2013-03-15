/*bms 学校附件*/
function showScanPic(scan){
	if(scan == "no"){
		con = "没有找到附件！";
	}else{
		con = '<div style="padding: 0 1em;OVERFLOW-Y:auto;SCROLLBAR-FACE-COLOR:#ffffff;FONT-SIZE:11pt;PADDING-BOTTOM:0px;SCROLLBAR-HIGHLIGHT-COLOR:#ffffff;OVERFLOW:auto;WIDTH:600px;SCROLLBAR-SHADOW-COLOR:#919192;COLOR:blue;SCROLLBAR-3DLIGHT-COLOR:#ffffff;LINE-HEIGHT:100%;SCROLLBAR-ARROW-COLOR:#919192;PADDING-TOP:0px;SCROLLBAR-TRACK-COLOR:#ffffff;FONT-FAMILY:宋体;SCROLLBAR-DARKSHADOW-COLOR:#ffffff;LETTER-SPACING:1pt;HEIGHT:500px;TEXT-ALIGN:left"><img src="'+scan+'"/></div>'
	}
	var throughBox = art.dialog.through;
	throughBox({
	    content: con,
	    lock: true
	});
}