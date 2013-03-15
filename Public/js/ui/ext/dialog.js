function DialogCls() {
	this.init();
}
DialogCls.prototype.init=function() {
	this.createDiv();
	this.setOptions();
};
DialogCls.prototype.createDiv=function() {
	$('<div id="dialog" title="我们网温馨提示"></div>').appendTo($('body'));
	$('<p id="show_msg_p"></p>').appendTo($('#dialog'));
};
DialogCls.prototype.setOptions=function() {
	var options = {
		autoOpen:false,
		bgiframe:true,
		buttons:{
			'确定':function() {
				$(this).dialog('close');
			}
			/*  ,'取消':function() {
				$(this).dialog('close');
			}*/
		},
		draggable:true,
		resizable:false,
		minWidth:500,
		minHeight:100,
		modal:true,
		zIndex:9999,
		stack:true,
		position:'center',
		dialogClass: 'alert'
	};
	$('#dialog').dialog(options);
};
DialogCls.prototype.resetPosition=function() {
	$('#dialog').dialog('option', 'position', 'center');
};
DialogCls.prototype.show=function(msg) {
	this.resetPosition();
	$('#show_msg_p').text(msg);
	$('#dialog').dialog('open');
	return false;
};
$(document).ready(function() {
	var addLink=function(hrefUrl) {
		var oHead = document.getElementsByTagName('HEAD').item(0);
	    var oLink= document.createElement("link");
	    oLink.rel="stylesheet";
	    oLink.type = "text/css";
	    oLink.href=hrefUrl;
	    oHead.appendChild(oLink);
	};
	if($('link[src="/Public/local/js/ui/css/jquery-ui-min.css"]').length == 0) {
		addLink("/Public/local/js/ui/css/jquery-ui-min.css");
	}
	
	var Dialog = new DialogCls();
	window.alert=function(msg) {
		Dialog.show(msg);
	};
});