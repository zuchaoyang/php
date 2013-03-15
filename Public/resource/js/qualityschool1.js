function qualityschool1(){
	this.aa();
};

qualityschool1.prototype.aa = function(){
	$(".multi_media > p > a").click(function(){
		
		$("#" + this.id).addClass('a_hover').siblings().removeClass("a_hover");
		$("#" + this.id + "_ul").show().siblings().hide();
		$(".multi_media > p").show();
		$(".multi_media > h4").show();
	});
};

$(document).ready(function(){
	new qualityschool1();
});