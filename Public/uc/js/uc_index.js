function UcIndex () {
	this.save_uid();
};

UcIndex.prototype.save_uid = function() {
	var self = this;
	$("#save_uid").click(function(){
		var client_account = $("#uid").html();
		window.location="/Uc/Index/saveaccount/client_account/"+client_account;
	});
};



$(document).ready(function() {
	new UcIndex();
});