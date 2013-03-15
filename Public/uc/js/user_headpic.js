function user_headpic() {}
user_headpic.prototype.jsUpdateUserLogo = function(obj){
	if(obj.f){
		$("#pUserLogo").attr("src", obj.url + "?" + (new Date()).getTime());
	}
};
user_headpic.prototype.reflash = function (){
	window.location.reload();
};

$(document).ready(function() {
	var obj = new user_headpic();
	jsUpdateUserLogo = obj.jsUpdateUserLogo;
});