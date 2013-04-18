function header() {
	this.init();
	this.setNavPos();
};

header.prototype.init = function(){
	$("#small_head_pic").error(function(){
		this.src = '/Public/images/head_pic.jpg';
	});
	
	var me = this;	
	$('.user_msg_ul > li').bind('mouseenter', openSubMenu);
	$('.user_msg_ul > li').bind('mouseleave', closeSubMenu);
	
	$("#show_msg_on_load").click(function(){
		$("#show_msg_on_load").hide();
	});	

	function openSubMenu() {
		
		$(this).find('ul').css('visibility', 'visible');
		$("#show_msg_on_load").trigger('click');
	};
	
	function closeSubMenu() {
		$(this).find('ul').css('visibility', 'hidden');	
	};
	
	$('#head_nav a').click(function() {
		$.cookie("head_nav", this.id, {domain:".wmw.cn", path:"/"});
		me.setNavPos();
	});
	
};

header.prototype.setNavPos=function(obj) {
	var head_nav = $("#head_nav");
	var cur_nav = $.cookie('head_nav');
	var url = window.location.href; 

	var is_homepage = false;
	if (url.indexOf('Sns/HomePage') >= 0) {
		is_homepage = true;
	}
	if (cur_nav && !is_homepage) {

		$('#head_nav a').each(function(i) {
			if (this.id == cur_nav) {
				$(this).addClass("ha" + (i + 1));
			} else {
				$(this).removeClass("ha" + (i + 1));
			}
		});
	} else {
		$('#ha1').addClass("ha1");
	}
};

$(document).ready(function(){
	new header();
});