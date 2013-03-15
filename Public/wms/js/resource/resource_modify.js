function resource_modify() {
	this.cache = {};
	
	this.init();
	this.attachEventForSearch();
	this.attachEventForResource();
	this.attachEventForPage();
	this.attachEventForPopDiv();
}

resource_modify.prototype.init=function() {
	var product_id = $('#product_id').val();
	$(':input[name="product_id"][value="' + product_id + '"]').attr('checked', 'checked');
};

resource_modify.prototype.attachEventForSearch=function() {
	var context = $('form:first');
	$('#submit_btn', context).click(function() {
		$('form:first').submit();
	});
	
	$('form:first').submit(function() {
		var partTitle = $.trim($('#partTitle').val());
		if(!partTitle) {
			alert('请输入检索关键词!');
			$('#partTitle').focus();
			return false;
		}
		return true;
	});
};

resource_modify.prototype.attachEventForResource=function() {
	var self = this;
	
	$('a[id^="show_resource_"]').click(function() {
		var resource_id = $(this).attr('id').toString().match(/(\d+)/)[1];
		if(!self.cache[resource_id]) {
			$.ajax({
				type:'get',
				url:'/Wms/Resource/Resourcemodify/getResourceAjax',
				async:false,
				data:{'resource_id':resource_id},
				dataType:'json',
				success:function(json) {
					self.cache[resource_id] = json;
				}
			});
		}
		
		var json = self.cache[resource_id];
		if(json.status > 0) {
			$('#title').children('span').html(json.data.title);
			$('#grade_name').children('span').html(json.data.grade_name);
			$('#subject_name').children('span').html(json.data.subject_name);
			$('#version_name').children('span').html(json.data.version_name);
			$('#file_type_name').children('span').html(json.data.file_type_name);
			
			if(json.data.product_id == 1) {
				$('#chapter_name').children('span').html(json.data.chapter_name);
				$('#section_name').children('span').html(json.data.section_name);
				$('#chapter_name,#section_name').show();
			} else {
				$('#chapter_name,#section_name').hide();
			}
			if(json.data.thumb_img) {
				$('#thumb_img').children('img').attr('src', json.data.thumb_img);
				$('#thumb_img').show();
			} else {
				$('#thumb_img').hide();
			}
		}
		
		$('#popDiv').show();
	});
	
	$('a[id^="delete_resource_"]').click(function() {
		var resource_id = $(this).attr('id').toString().match(/(\d+)/)[1];
		
		var md5_key = $('#md5_key_' + resource_id).val();
		var title = $('#title_' + resource_id).val();
		
		if(confirm("确定删除资源:" + title + "?")) {
			$.ajax({
				type:'get',
				url:'/Wms/Resource/Resourcemodify/deleteResource',
				data:{'resource_id':resource_id, 'md5_key' : md5_key},
				dataType:'json',
				success:function(json) {
					alert(json.info);
					if(json.status > 0) {
						$('tr[id="rid_' + resource_id + '"]').remove();
					}
				}
			});
		}
	});
};

resource_modify.prototype.attachEventForPopDiv=function() {
	var context = $('#popDiv');
	$('#close_div_a').click(function() {
		$('#bg,#popIfrme,#popDiv').hide();
	});
};

resource_modify.prototype.attachEventForPage=function() {
	var self = this;
	$('#pre_page').click(function() {
		var pre_page = parseInt($('#page').val()) - 1;
		if(pre_page > 0) {
			self.buildQuery({'page' : pre_page});
		}
	});
	$('#next_page').click(function() {
		var next_page = parseInt($('#page').val()) + 1;
		self.buildQuery({'page' : next_page});
	});
	$('#jumpto_btn').click(function() {
		var jump_page = $('#jumpto').val();
		if(jump_page > 0) {
			self.buildQuery({'page' : jump_page});
		}
	});
};

resource_modify.prototype.buildQuery=function(options) {
	var settings = {
		'partTitle' : $('#partTitle').val(),
		'product_id' : $('#product_id').val(),
		'page' : $('#page').val()
	};
	$.extend(true, settings, options || {});
	
	var formObj = $("<form method='post'></form>").attr('action', $('form:first').attr('action'));
	for(var i in settings) {
		if(settings[i]) {
			$('<input type="hidden" name="' + i + '" value="' + settings[i] + '"/>').appendTo(formObj);
		}
	}
	formObj.appendTo($('body')).submit();
};

$(document).ready(function() {
	new resource_modify();
});
