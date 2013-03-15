/**
 * 动态的时间格式化
 * timestamp  是11为的时间 ef:13693849555
 * 1. 如果日期为今天  则    ：   今天    14：29
 * 2. 如果日期为昨天  则    ：   昨天    14：29
 * 3. 如果为年内           则    :  12-19 14:29
 * 4. 如果超过一年     则     ：   2012-12-29 14:29
 * 
 * 调用$.dateFormat(13693849555);
 */
(function($) {
	
var dateFormat = {
	timedesc:function(timestamp){
		var nowDate = new Date();
		var timestampDate = new Date(timestamp*1000);
		var nowY = nowDate.getFullYear();
		var Y = timestampDate.getFullYear();
		var m = timestampDate.getMonth();
		var d = timestampDate.getDate();
		var H = timestampDate.getHours();
		var i = timestampDate.getMinutes();
		var formatdate = "";
		
		H = H > 10 ? H : "0" + H;
		i = i > 10 ? i : "0" + i;
		
		if(nowY > Y) {
			formatdate += Y + "-";
		}
		
		if(dateFormat.isToday(timestamp)) {
			formatdate += "今天 ";
		}else if(dateFormat.isYesday(timestamp)){
			formatdate += "昨天 ";
		}else{
			formatdate += m + "-" + d + " ";
		}
		
		formatdate += H + ":" + i;
		
		return formatdate;
	},
	
	isToday:function(timestamp){
		var nowDate = new Date().valueOf();
		
		return (parseInt(nowDate) - parseInt(timestamp*1000)) > 86400000 ? false : true;
	},
	
	isYesday:function(timestamp){
		var nowDate = new Date().valueOf();
		
		return ((parseInt(nowDate) - parseInt(timestamp*1000)) < 86400000*2) && !dateFormat.isToday(timestamp) ? true : false;
	}
};

$.dateFormat=function(timestamp) {
	return dateFormat.timedesc(timestamp);
};

})(jQuery);