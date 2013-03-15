function save_ext() {
	this.save();
}

save_ext.prototype.save= function() {
	var self=this;
	$("#save").click(function() {
		var form = $("#teacher_ext");
		form.attr("action","/Uc/Userinfos/modifyUserteacher");
		form[0].submit();
	});
};

$(document).ready(function() {
	new save_ext;
});