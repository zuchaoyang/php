function resource_syn() {
	this.img_server = $('#img_server').val() || "/Public";
	this.attachEvent();
	this.init();
};
resource_syn.prototype.init=function() {
	var self = this;
	if($('#selected_chapter').length) {
		$('.know_center_unit').hide();
		$('#img_show').attr({
			'src':self.img_server + '/resource/images/zk.gif',
			'title':'展开'
		});
	}
};
resource_syn.prototype.attachEvent=function() {
	var self = this;
	$('#img_show').click(function() {
		var src = $(this).attr('src') || "";
		if(src.indexOf('ss.gif') >= 0) {
			$('.know_center_unit').hide();
			$(this).attr({
				'src':self.img_server + '/resource/images/zk.gif',
				'title':'展开'
			});
		} else {
			$('.know_center_unit').show();
			$(this).attr({
				'src':self.img_server + '/resource/images/ss.gif',
				'title':'收起'
			});
		}
	});
};

$(document).ready(function() {
	new resource_syn();
});