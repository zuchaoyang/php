
var settings = require('./settings.js');
var cluster = require('node-cluster');

var master = new cluster.Master({
	  							'max_fatal_restart'   : 2,
	  							'restart_time_window' : 60
								});
master.register(settings.appPort, __dirname + '/app.js');
master.on('restartgiveup', function(port, msg) {
    // alert:
}).dispatch();