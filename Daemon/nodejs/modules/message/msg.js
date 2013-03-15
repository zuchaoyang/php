module.exports.msg = function(io){	
    io.of("/msg").on("connection", function(socket){
        console.log("msg : user connect");
        socket.on("connect", function(){
            console.log("msg : user connect");
        });

        socket.on("disconnect", function(){
            console.log("msg : user connect");
        });
        
        var settings = require('./../../settings.js');
        
        var redis = require('redis'); 
        redis_obj = redis.createClient(settings.REDIS_PORT, settings.REDIS_HOST);
        msg_sender = redis.createClient(settings.REDIS_PORT, settings.REDIS_HOST);
        
        //定时获取redis中的数据提交至页面，再通过js处理
        redis_obj.on("message", function(uid, message){
            console.log('send one message to ' + uid );
            socket.emit('message', message);
        });
        
        socket.on('on_load',function(uid){
            this.uid = uid;
            
            
            
            var channl_list = [];
        	for(i=0;i<settings.channl.length;i++){
        		channl_list[i] = 'msg:' + uid + ":" + settings.channl[i];
        	}
            msg_sender.mget(channl_list, function (err, response) {
            		var news_arr = {};
    	        		news_arr.homework = (response[0] == null) ? 0 : response[0];
    	        		news_arr.comments = (response[1] == null) ? 0 : response[1];
    	        		news_arr.req = (response[2] == null) ? 0 : response[2];
    	        		news_arr.notice = (response[3] == null) ? 0 : response[3];
    	        		news_arr.exam = (response[4] == null) ? 0 : response[4];
    	        		news_arr.res = (response[5] == null) ? 0 : response[5];
    	        		news_arr.privatemsg = (response[6] == null) ? 0 : response[6];
    	        		console.log(news_arr);
            		socket.emit('get_msg', news_arr);
            });
        });
        
        socket.on('clear_msg', function(uid, msg_type){
        	var channl = 'msg:' + uid + ":" + msg_type;
        	msg_sender.del(channl);
        });
        
        
        socket.on('sub_msg', function(uid){
        	for(i=0;i<settings.channl.length;i++){
        		redis_obj.subscribe('msg:' + uid + ":" + settings.channl[i]);
        	}
        });
    });	
};