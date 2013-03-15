function sns_class_header(){
	this.init();
	this.show_list();
	this.hide_list();
	this.change_class();
};

sns_class_header.prototype.init = function(){

};

sns_class_header.prototype.show_list = function(){
	var me = this;
	$("a:first", $("#sns_class_header")).mouseover(function(){
		me.set_position_show('class_list', 0, 10);
		$("#class_list").css('z-index', 999).show();
	});
	
	$("#class_list").mouseover(function(){
		me.set_position_show('class_list', 0, 10);
		$("#class_list").css('z-index', 999).show();
	});
};

sns_class_header.prototype.hide_list = function(){
	$("#class_list").mouseleave(function(){
		$("#class_list").css('z-index', 999).hide();
	});
	$("a", $("#sns_class_header")).mouseleave(function(){
		$("#class_list").css('z-index', 999).hide();
	});
};

sns_class_header.prototype.change_class = function(){
	$("a", $("#class_list")).click(function(){
		var class_code = this.id;
		
		window.location.href="/Sns/ClassIndex/Index/index/class_code/" + class_code;
	});
};


sns_class_header.prototype.set_position_show = function() {
	var height = $("div[class='tip_main']", $("#sns_class_header")).css('height');
    var show_x = parseInt($("a", $("#sns_class_header")).outerHeight()) + parseInt($("a", $("#sns_class_header")).position().top);
    var show_y = $("a", $("#sns_class_header")).position().left;
    $("#class_list").css("position","absolute"); 
    $("#class_list").css("left",show_y + "px"); 
	$('#class_list').css('top',show_x + "px");
};

$(document).ready(function(){
	new sns_class_header();
});