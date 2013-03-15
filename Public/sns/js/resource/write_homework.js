function writehomework() {
	this.publish();
	this.submitForm();
}

writehomework.prototype.publish = function() {
	var self=this;
		var bj = document.getElementById("ContentBg").value;
		document.getElementById('content').style.background = "url("+bj+")";
		$('#content').xheditor({skin:'vista',tools:'Separator,BtnBr,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,Align,List,Outdent,Indent,Link,Unlink,Emot'});
};


writehomework.prototype.submitForm = function() {
	$("#submitted").click(function(){
		$("#form").submit();
	});
	
	$("#dateimg").click(function() {
		WdatePicker({el:'date',minDate:'%y-%M-%d'});
	});
};


$(document).ready(function(){
	new writehomework();
});