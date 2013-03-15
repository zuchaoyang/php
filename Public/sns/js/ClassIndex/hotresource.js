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
				$('h3>a', context).attr('href',json.data.more_url);
				var j = 1;
				for(var i in json.data.resource_info) {
					var data = json.data.resource_info[i];
					$('ol', context).append("<li><a  target='_blank' href='"+ data.file_path +"'>" + j + ". " +data.title +"</a></li>");
					j++;
				}
			}
		}
	});
};
$(function(){
	new hotresource();
});