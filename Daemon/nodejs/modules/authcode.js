/**
 * @param string string 原文或者密文
 * @param string operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string key 密钥
 * @param int expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 *
 * @example
 *
 * a = security.code('abc', 'ENCODE', 'key'); // 加密
 * b = security.code(a, 'DECODE', 'key');  // b(abc)，解密
 *
 * a = security.code('abc', 'ENCODE', 'key', 3600);
 * b = security.code('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 * 
 *  本js对象由fvzone.com管理员实现并提供，核心算法是RC4，这里只有部分使用了DZ的代码参照
 */
var crypto = require('crypto');

var security = module.exports = {

     code:function(string, operation, key, expiry) {
        
        operation = operation || 'DECODE';
        key       = key       || 'www.wmw.cn';
        expiry    = expiry    || 0;

        // 采用 encodeURI 对字符编码
        string = encodeURI(string);
        //console.log(string);
        // 时间取得
        var now = new Date().getTime()/1000;
        // Unix 时间戳
        var timestamp = parseInt(now, 10);
        // 毫秒
        var seconds = (now - timestamp)+'';

        var fvzone_auth_key = '';
        var ckey_length = 4;
        var key = this.md5(key ? key : fvzone_auth_key);
        var keya = this.md5(key.substr(0,16));
        var keyb = this.md5(key.substr(16,16));
        var keyc = ckey_length ? (operation == 'DECODE' ? string.substr(0, ckey_length) : this.md5(seconds).substr(-ckey_length)) : '';

        cryptkey = keya + this.md5(keya + keyc);

        if(operation == 'DECODE') {
            string = this.base64_decode(string.substr(ckey_length));
        } else {
            string = (expiry ? timestamp + expiry : '0000000000') + this.md5(string+keyb).substr(0, 16)+string;
        }

        // RC4 加密原始算法函数
        result = this.rc4(cryptkey, string);
        
        if(operation == 'DECODE') {
          if((result.substr(0, 10) == 0 || (result.substr(0,10) - timestamp) > 0) && result.substr(10,16) == this.md5(result.substr(26) + keyb).substr( 0,16)) {
                // 对返回的结果使用 decodeURI 解码
                return decodeURI(result.substr(26));
            } else {
                return '';
            }
        } else {
            return keyc + this.base64_encode(result).replace('=', '');
        }
    },
    // RC4 算法函数
    rc4:function(key, text) {
        s = new Array();
        for (var i=0; i<256; i++) {
            s[i] = i;
        }
        var j = 0, x;
        for (i=0; i<256; i++) {
            j = (j + s[i] + key.charCodeAt(i % key.length)) % 256;
            x = s[i];
            s[i] = s[j];
            s[j] = x;
        }
        i = j = 0;
        var ct = [];
        for (var y=0; y<text.length; y++) {
            i = (i + 1) % 256;
            j = (j + s[i]) % 256;
            x = s[i];
            s[i] = s[j];
            s[j] = x;
            ct.push(String.fromCharCode(text.charCodeAt(y) ^ s[(s[i] + s[j]) % 256]));
        }
        return ct.join('');
    },
    // md5 生成
    md5:function(str, enc) {
        return crypto.createHash('md5').update(str).digest(enc || 'hex');
    },
    // 64位编码
    base64_encode:function(str) {
        return new Buffer(str, 'binary').toString('base64');
    },
    // 64编码
    base64_decode:function(str) {
        return new Buffer(str, 'base64').toString('binary');
    }
}

module.exports.token_decode = function(token, key){
	return security.code(token, 'DECODE', key);
}

module.exports.token_encode = function(token, key){
	return security.code(token, 'ENCODE', key);
}