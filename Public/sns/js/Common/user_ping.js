function user_ping() {
	this.time_out = 300000;
	this.max_retry_attempts = 10;
	this.fail_connected = 0;
	this.init();
};

user_ping.prototype.init = function(){
	var self = this;
	self.pingInterval = setInterval( function() {
		self.ping();
	}, self.time_out);

};

user_ping.prototype.ping = function(){
	
	var self = this;
	if (self.fail_connected > self.max_retry_attempts) {
		clearInterval(pingInterval);
		return false;
	}

	var pingSuccess = false;
	$.ajax({
		type: "POST",
		url: "/api/Uclient/ping?"+ new Date().getTime(),
		dataType: "json",
		data: {},
		timeout: 10000,
		success: function(data,status,xhr) {
//			alert("Hurrah!");
			pingSuccess = true;
	    },
	    error: function(xhr, status, error){
//	    	alert("Error!" + xhr.status);
	    },
	    complete: function(){
	        if(!pingSuccess){
	             self.fail_connected++;
	        }
	    }
	});	


};

$(document).ready(function(){
	new user_ping();
});