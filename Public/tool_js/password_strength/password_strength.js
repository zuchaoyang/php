function toolCls() {};
toolCls.prototype.checkPasswordLevel=function(password) {
	//检测String的长度和不重复的字符串长度
	var ch_group = {};
	for(var i=0; i < password.length; i++) {
		var ch = password.charAt(i);
		var ch_code = ch.charCodeAt(0);
		var group_name="a"; 
		if (ch_code>=48 && ch_code <=57) {
			group_name = "a"; 
		} else if (ch_code>=65 && ch_code <=90) {
			group_name = "b"; 
		} else if (ch_code>=97 && ch_code <=122) {
			group_name = "c";  
		} else {
			group_name = "d";
		}
		if($.isEmptyObject(ch_group[group_name])) {
			ch_group[group_name] = {'str':"",'repeat_num':0};
		}
		if(ch_group[group_name].str.indexOf(ch) < 0) {
			ch_group[group_name].str += ch;
		} else {
			ch_group[group_name].repeat_num++;
		}
	}
	//重复单词的权重
	var weight = 0.8;
	var modes = 0,chars = 0;
	for(var i in ch_group) {
		chars+=Math.floor(ch_group[i].str.length + ch_group[i].repeat_num * weight);
		modes++;
	}
	var weight = Math.round(Math.sqrt(Math.pow(chars, 2) + Math.pow(modes, 2)));
	if(weight == 0) return 0;
	if(weight>0 && weight<=5) return 1;
	if(weight>5 && weight<=8) return 2;
	if(weight>8) return 3;
	return 0;
};