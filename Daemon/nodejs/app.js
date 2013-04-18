//========================变量定义===============================
/**
 * Module dependencies.
 */
var http			= require('http'),
	app 			= http.createServer(handler),
	sio   			= require('socket.io'),
	node_cluster 	= require('node-cluster'),
    path 			= require('path'),
    settings 		= require('./settings'),
    fs 				= require('fs'),
    cookie 			= require('cookie'),
    authcode		= require('./modules/authcode.js')
    
    
if (!settings.isCluster) {
	app.listen(3000);
} else {

	var worker = node_cluster.Worker();
	worker.ready(function(socket) {
	    app.emit('connection', socket);
	});

}

//========================首页及测试页定义========================
function handler(req, res) {
	fs.readFile(__dirname + '/index.html', function (err, data) {
		if (err) {
		  res.writeHead(500);
		  return res.end('Error loading index.html');
		}
		res.writeHead(200);
		res.end(data);
	});
}

//=================配置socket.io=================================
/**
 * socket.io configure
 * 
 * see https://github.com/LearnBoost/Socket.IO/wiki/Configuring-Socket.IO
 */
	
var io = sio.listen(app);
 
 //设置session
io.set('authorization', function(handshakeData, callback){
	// 通过客户端的cookie字符串来获取其session数据
//	console.log(handshakeData);
	
	if (handshakeData.headers.cookie) {

		var cookies = cookie.parse(handshakeData.headers.cookie);
		var snscookie = cookies[settings.SNS_SESSION_TOKEN];
		
		if (!snscookie) {
			return callback('No cookie transmitted.', false);
		}

//		console.log("=========================encode================================");
//		console.log(snscookie);
//		var decode = authcode.token_decode(snscookie, 'DECODE', settings.AUTH_KEY);
//		console.log("=========================decode================================");
//		console.log(decode);
//		console.log("=========================test encode================================");
//		console.log(authcode.token_encode('abc', settings.AUTH_KEY));
//		console.log("=========================test decode================================");
//		console.log(authcode.token_decode(authcode.token_encode('abc', settings.AUTH_KEY), settings.AUTH_KEY));		
		
	} else {
       return callback('No cookie transmitted.', false);
    }
	
	callback(null, true);
});

io.set('transports', ['websocket', 'flashsocket', 'htmlfile', 'xhr-polling', 'jsonp-polling']);

io.configure('production', function(){
	io.enable('browser client etag');
	io.set('log level', 1);
//	io.enable('browser client minification');  // send minified client
//	io.enable('browser client etag');          // apply etag caching logic based on version number
//	io.enable('browser client gzip');          // gzip the file
});

io.configure('development', function(){
  io.set('log level', 3);
});

//=================socket.io监听处理==============================

//var ping = require("./modules/user/ping.js");
//ping.ping(io);

 //todo  消息机制的加入

var msg = require("./modules/message/msg.js");
msg.msg(io);

 


//=================多进程处理=====================================
