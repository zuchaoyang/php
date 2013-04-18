function hotresource() {
	this.getResource();
};

hotresource.prototype.getResource = function() {
	var me = this;
	var context = $("#hot_resource");
	$.ajax({
		type:'post',
		url:'/Sns/Resource/Resource/getresource',
		dataType:'json',
		async:true,
		success:function(json) {
			if(json.status>0) {
				var j = 1;
				for(var i in json.data.resource_info) {
					var cloneObj = $('.clone',context).clone().removeClass('clone').show();
					var data = json.data.resource_info[i];
					var num_class = 'num_bj_hot';
					var title_class = 'rmzytj_hot';
					if(j>3) {
						num_class = 'num_bj';
						title_class = '';
					}
					data = $.extend(data,{num:j,num_class:num_class,title_class:title_class});
					cloneObj.renderHtml({
						data:data || {}
					});
					$('table',context).append(cloneObj);
					j++;
				}
			}
		}
	});
};
$(function(){
	new hotresource();
});