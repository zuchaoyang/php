
//调式模式，影响写日志和直接打印
exports.debug = false;

//是否为多进程方式
exports.isCluster = true;

//全局端口
exports.appPort = process.env.IG_APP_PORT || 3000;

//SNS COOKIES NAME
exports.SNS_SESSION_TOKEN = 'snssessiontoken';

//token key
exports.AUTH_KEY = 'www.wmw.cn';	

//redis配置
exports.REDIS_PORT = 6379;
exports.REDIS_HOST = '127.0.0.1';
exports.REDIS_DEBUG_MODE = false;


//消息频道列表
exports.channl = channl = [];
	channl[0] = "homework";
//	channl[1] = "comments";
	channl[2] = "req";
	channl[3] = "notice";
	channl[4] = "exam";
//	channl[5] = "res";
	channl[6] = "privatemsg";
