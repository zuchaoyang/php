function Tips(){
	this.timeout = 5;
	this.update_time(this.timeout);
	this.backurl = '/Uc/Login';
}

Tips.prototype.update_time=function(timeout) {
	var self = this;
	if(timeout > 0) {
		$('#showtime').text(timeout--);
		setTimeout(function(){
			self.update_time(timeout);
		}, 1000);
	} else {
		self.skip();
	}
};

Tips.prototype.skip=function (){
	
	window.location = this.backurl;
};
$(document).ready(function() {
	new Tips();
});