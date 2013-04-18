function photoShow() {
	this.page = 1;
	this.is_edit = false;
	this.attachEventUserDefine();
};
photoShow.prototype.attachEventUserDefine = function(){
	var self = this;
	$('#xpxq_div').bind({
		openEvent: function(evt, options) {
			options = options || {};
			$(this).data('options',options);
			//获取配置项
			self.callback_url = options.callback_url || {};
			self.is_edit = options.is_edit;
			self.init(options);
		}
	});
};

photoShow.prototype.init=function(options) {
	var self = this;
	var galleria_config = options.galleria_config || {};
	var param_data = options.param_data || {};
	var galleriaObj = $('#galleria').galleriaWmw(
		 //首先是配置参数
		{
			autoplay:false,
			transition : 'fade',		
			showSize : galleria_config.showSize,
			preloadSize : galleria_config.preloadSize,
			photo_id : galleria_config.photo_id,
			url : galleria_config.url,
			theme : 'wmw/galleria.wmw.js', 	// 自定义样式
			callback : function(json){ self.callback(json); }
		},
		// 再次是查询数据的参数
		param_data
	);

};

photoShow.prototype.callback=function(json) {
	/*console.log("photoShow.callback");
	console.log(json);
	console.log(json.photo_id);*/
	var self = this;
	var photo_id = json.photo_id || {};
	var photo_info = {};
	$.ajax({
		type:'get',
		url:self.callback_url+'/photo_id/'+photo_id,//'/Sns/Album/Classphoto/getPhotoInfoByPhotoId/photo_id/'+photo_id,
		dataType:"json",
		async:false,
		success:function(json_data) {
			if(json_data.status<0) {
				$.showError(json_data.info);
			}
			photo_info = json_data.data || {};
			photo_info = $.extend(
				photo_info,
				{'callback':function() {
					self.remove(json.photo_index);
				}}
			);
		}
	});
	$("#photo_name",$("#xpxq_div")).html(photo_info.name);
	$("#add_date",$("#xpxq_div")).html(photo_info.add_date);
	$("#photo_edit").data('datas',photo_info);
	if(self.is_edit) {
		if(json.description != '') {
			$("#description",$("#xpxq_div")).html('<span>描述：</span><font id="description_font">'+photo_info.description+'</font>');
		}else{
			$("#description",$("#xpxq_div")).html('<a href="javascript:;">点此输入照片描述</a>');
		}
	}else{
		if(json.description != '') {
			$(".description_no",$("#xpxq_div")).html('<span>描述：</span><font>'+photo_info.description+'</font>');
		}else{
			$(".description_no",$("#xpxq_div")).html('');
		}
	}
	//评论
	$('#comment_list_div').trigger('loadEvent', [{
		data:{
			photo_id:photo_id
		}
	}]);
};

photoShow.prototype.remove=function(index) {
	Galleria.get(0).splice(index, 1);
	Galleria.get(0).next();
};

$(document).ready(function() {
	new photoShow();
});