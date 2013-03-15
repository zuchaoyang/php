function Tips() {
	this.attachEvent();
	var timeout = $('#showtime').text();
	this.update_time(timeout);
}

Tips.prototype.attachEvent=function() {
	var href = $('#back_url').attr('href').toString().toLowerCase();
	if(href.match(/javascript/)) {
		$('#back_url').click(function() {
			window.history.back(-1);
			return false;
		});
	}
};

Tips.prototype.update_time=function(timeout) {
	var self = this;
	if(timeout > 0) {
		$('#showtime').text(timeout--);
		setTimeout(function(){
			self.update_time(timeout);
		}, 1000);
	}
};
$(document).ready(function() {
	new Tips();
});



