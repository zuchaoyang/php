function userbaseCls() {
	this.baseUrl = "/Public/uc/images/user_baseinfo/";
	this.init();
}
userbaseCls.prototype.init=function() {
	this.attachEvent();
	this.fillSxAndStar();
	this.checkInput();
	
	var sex = $('#client_sex').val();
	if(sex == 1) {
		$('#sex_boy').trigger('click');
	} else {
		$('#sex_girl').trigger('click');
	}
};
userbaseCls.prototype.attachEvent=function() {
	var self = this;
	$('#sex_boy,#sex_girl').bind('click', function() {
		var sex_id = $(this).attr('id');
		var suffix = sex_id.split('_').pop();
		$(this).css('background', 'url(' + self.baseUrl + suffix + '_blue.jpg) no-repeat scroll 0 0 transparent');
		self.toggleSex(sex_id);
		$('#client_sex').val(suffix == 'boy' ? 1 : 0);
	});
	$('#birthday').bind('click', function() {
		WdatePicker({maxDate:'%y-%M-%d'});
	}).bind('blur', function() {
		self.fillSxAndStar();
	});
	$('#base_save_btn').bind('click', function() {
		$('form:first').submit();
	});
};
userbaseCls.prototype.fillSxAndStar=function() {
	var self = this;
	var date_s=$('#birthday').val();
    if(date_s.length > 0){
        var sd=Share.dateProcess;
        var sxObj=sd.getSX_d(date_s);
        var astro=sd.getAstro_d(date_s);
        //更改背景图片
        var sx = "0" + sxObj.value.toString();
        $('#psx').css('background', 'url("' + self.baseUrl + 'icon' + sx.substring(sx.length-2) + '.jpg") no-repeat scroll 0 0 transparent').attr('title', sxObj.show);
        $("#psx_inp").val(sxObj.value);
        var star = "0" + astro.value.toString();
        $('#pAstro').css('background', 'url("' + self.baseUrl + 'star_icon' + star.substring(star.length-2) + '.jpg") no-repeat scroll 0 0 transparent').attr('title', astro.show);
        $("#pAstro_inp").val(astro.value);
    } else {
    	//加载默认的图片
    	$('#psx,#pAstro').css('background', 'url("' + self.baseUrl + 'icon_none.gif") no-repeat scroll 0 0 transparent').attr('title', '无');
        $("#psx_inp,#pAstro_inp").val(0);
    }
};
userbaseCls.prototype.toggleSex=function(select_id) {
	var self = this;
	$('#sex_boy,#sex_girl').each(function() {
		if($(this).attr('id') != select_id) {
			var suffix = this.id.split('_').pop();
			$(this).css('background', 'url(' + self.baseUrl + suffix + '_gray.jpg) no-repeat scroll 0 0 transparent');
		}
	});
};

userbaseCls.prototype.checkInput = function() {
	$.formValidator.initConfig({
		formid:"user_contact",
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist){

	}
});
	
	$("#client_name").formValidator({
		onshow:"请输入姓名",
		onfocus:"姓名不能为空",
		oncorrect:"&nbsp;"
	}).inputValidator({
		min:2,
		max:10,
		onerror:"姓名长度不能超过2~10位"
	}).regexValidator({
		regexp:"chinese_letter",
		datatype:"enum",
		onerror:"姓名是6-12位英文字母或汉字"
	});
};

$(document).ready(function() {
	new userbaseCls();
});