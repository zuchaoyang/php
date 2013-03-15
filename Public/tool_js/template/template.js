var template = function(id, content) {
    return template[typeof content === 'object' ? 'render': 'compile'].apply(template, arguments);
}; 

(function(exports, global) {
    "use strict";
    exports.version = '1.4.0';
    exports.openTag = '<%';
    exports.closeTag = '%>';
    exports.parser = null;
    exports.render = function(id, data) {
        var cache = _getCache(id);
        if (cache === undefined) {
            return _debug({
                id: id,
                name: 'Render Error',
                message: 'Not Cache'
            });
        }
        return cache(data);
    };
    exports.compile = function(id, source) {
        var debug = arguments[2];
        if (typeof source !== 'string') {
            debug = source;
            source = id;
            id = null;
        }
        try {
            var Render = _compile(source, debug);
        } catch(e) {
            e.id = id || source;
            e.name = 'Syntax Error';
            return _debug(e);
        }
        function render(data) {
            try {
                return new Render(data).template;
            } catch(e) {
                if (!debug) {
                    return exports.compile(id, source, true)(data);
                }
                e.id = id || source;
                e.name = 'Render Error';
                e.source = source;
                return _debug(e);
            };
        };
        render.prototype = Render.prototype;
        render.toString = function() {
            return Render.toString();
        };
        if (id) {
            _cache[id] = render;
        }
        return render;
    };
    exports.helper = function(name, helper) {
        _helpers[name] = helper;
    };
    var _cache = {};
    var _isNewEngine = ''.trim;
    var _isServer = _isNewEngine && !global.document;
    var _keyWordsMap = {};
    var _forEach = function() {
        var forEach = Array.prototype.forEach ||
        function(block, thisObject) {
            var len = this.length >>> 0;
            for (var i = 0; i < len; i++) {
                if (i in this) {
                    block.call(thisObject, this[i], i, this);
                }
            }
        };
        return function(array, callback) {
            forEach.call(array, callback);
        };
    } ();
    var _create = Object.create ||
    function(object) {
        function Fn() {};
        Fn.prototype = object;
        return new Fn;
    };
    var _helpers = exports.prototype = {
        $forEach: _forEach,
        $render: exports.render,
        $getValue: function(value) {
            return value === undefined ? '': value;
        }
    };
    _forEach(('break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if' + ',in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with' + ',abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto' + ',implements,import,int,interface,long,native,package,private,protected,public,short' + ',static,super,synchronized,throws,transient,volatile' + ',arguments,let,yield').split(','),
    function(key) {
        _keyWordsMap[key] = true;
    });
    var _compile = function(source, debug) {
        var openTag = exports.openTag;
        var closeTag = exports.closeTag;
        var parser = exports.parser;
        var code = source;
        var tempCode = '';
        var line = 1;
        var uniq = {
            $out: true,
            $line: true
        };
        var variables = "var $helpers=this," + (debug ? "$line=0,": "");
        var replaces = _isNewEngine ? ["$out='';", "$out+=", ";", "$out"] : ["$out=[];", "$out.push(", ");", "$out.join('')"];
        var concat = _isNewEngine ? "if(content!==undefined){$out+=content;return content}": "$out.push(content);";
        var print = "function(content){" + concat + "}";
        var include = "function(id,data){" + "if(data===undefined){data=$data}" + "var content=$helpers.$render(id,data);" + concat + "}";
        _forEach(code.split(openTag),
        function(code, i) {
            code = code.split(closeTag);
            var $0 = code[0];
            var $1 = code[1];
            if (code.length === 1) {
                tempCode += html($0);
            } else {
            	$0 = _helpers['$unescapeHTML']($0);
                tempCode += logic($0);
                if ($1) {
                    tempCode += html($1);
                }
            }
        });
        code = tempCode;
        if (debug) {
            code = 'try{' + code + '}catch(e){' + 'e.line=$line;' + 'throw e' + '}';
        }
        code = variables + replaces[0] + code + 'this.template=' + replaces[3];
        try {
            var render = new Function('$data', code);
            var proto = render.prototype = _create(_helpers);
            proto.toString = function() {
                return this.template;
            };
            return render;
        } catch(e) {
            e.temp = 'function anonymous($data) {' + code + '}';
            throw e;
        };
        function html(code) {
            line += code.split(/\n/).length - 1;
            code = code.replace(/('|"|\\)/g, '\\$1').replace(/\r/g, '\\r').replace(/\n/g, '\\n');
            code = replaces[1] + "'" + code + "'" + replaces[2];
            return code + '\n';
        };
        function logic(code) {
            var thisLine = line;
            if (parser) {
                code = parser(code);
            } else if (debug) {
                code = code.replace(/\n/g,
                function() {
                    line++;
                    return '$line=' + line + ';';
                });
            }
            if (code.indexOf('=') === 0) {
                code = code.substring(1).replace(/[\s;]*$/, '');
                if (_isNewEngine) {
                    code = '$getValue(' + code + ')';
                }
                code = replaces[1] + code + replaces[2];
            }
            if (debug) {
                code = '$line=' + thisLine + ';' + code;
            }
            getKey(code);
            return code + '\n';
        };
        function getKey(code) {
            code = code.replace(/\/\*.*?\*\/|'[^']*'|"[^"]*"|\.[\$\w]+/g, '');
            _forEach(code.split(/[^\$\w\d]+/),
            function(name) {
                if (/^this$/.test(name)) {
                    throw {
                        message: 'Prohibit the use of the "' + name + '"'
                    };
                }
                if (!name || _keyWordsMap.hasOwnProperty(name) || /^\d/.test(name)) {
                    return;
                }
                if (!uniq.hasOwnProperty(name)) {
                    setValue(name);
                    uniq[name] = true;
                }
            });
        };
        function setValue(name) {
            var value;
            if (name === 'print') {
                value = print;
            } else if (name === 'include') {
                value = include;
            } else if (_helpers.hasOwnProperty(name)) {
                value = '$helpers.' + name;
            } else {
                value = '$data.' + name;
            }
            variables += name + '=' + value + ',';
        };
    };
    var _getCache = function(id) {
        var cache = _cache[id];
        if (cache === undefined && !_isServer) {
            var elem = document.getElementById(id);
            if (elem) {
                exports.compile(id, elem.value || elem.innerHTML);
            }
            return _cache[id];
        } else if (_cache.hasOwnProperty(id)) {
            return cache;
        }
    };
    var _debug = function(e) {
        var content = '[template]:\n' + e.id + '\n\n[name]:\n' + e.name;
        if (e.message) {
            content += '\n\n[message]:\n' + e.message;
        }
        if (e.line) {
            content += '\n\n[line]:\n' + e.line;
            content += '\n\n[source]:\n' + e.source.split(/\n/)[e.line - 1].replace(/^[\s\t]+/, '');
        }
        if (e.temp) {
            content += '\n\n[temp]:\n' + e.temp;
        }
        if (global.console) {
            console.error(content);
        }
        function error() {
            return error + '';
        };
        error.toString = function() {
            return '{Template Error}';
        };
        return error;
    };
})(template, this);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = template;
}

(function(exports) {
    var _helpers = exports.prototype;
    var _forEach = _helpers['$forEach'];
    var _toString = Object.prototype.toString;
    var _isArray = Array.isArray ||
    function(obj) {
        return _toString.call(obj) === '[object Array]';
    };
    exports.openTag = '{';
    exports.closeTag = '}';
    exports.parser = function(code) {
        code = code.replace(/^\s/, '');
        var args = code.split(' ');
        var key = args.shift();
        var keywords = exports.keywords;
        var fuc = keywords[key];
        if (fuc && keywords.hasOwnProperty(key)) {
            args = args.join(' ');
            code = fuc.call(code, args);
        } else if (_helpers.hasOwnProperty(key)) {
            args = args.join(',');
            code = '=' + key + '(' + args + ');';
        } else {
            //code = '=$escapeHTML(' + code + ')';
            code = '=' + code;
        }
        return code;
    };
    exports.keywords = {
        'if': function(code) {
            return 'if(' + code + '){';
        },
        'else': function(code) {
            code = code.split(' ');
            if (code.shift() === 'if') {
                code = ' if(' + code.join(' ') + ')';
            } else {
                code = '';
            }
            return '}else' + code + '{';
        },
        '/if': function() {
            return '}';
        },
        'each': function(code) {
            code = code.split(' ');
            var object = code[0] || '$data';
            var as = code[1] || 'as';
            var value = code[2] || '$value';
            var index = code[3] || '$index';
            var args = value + ',' + index;
            if (as !== 'as') {
                object = '[]';
            }
            return '$each(' + object + ',function(' + args + '){';
        },
        '/each': function() {
            return '});';
        },
        'echo': function(code) {
            return 'print(' + code + ');';
        },
        'include': function(code) {
            code = code.split(' ');
            var id = code[0];
            var data = code[1];
            return 'include(' + id + ',' + data + ');';
        }
    };
    
    exports.helper('$each', function(data, callback) {
        if (_isArray(data)) {
            _forEach(data, callback);
        } else {
            for (var i in data) {
                callback.call(data, data[i], i);
            }
        }
    });
    
    exports.helper('$unescapeHTML', function(content) {
        if (typeof content == 'string') {
            return content.replace('&lt;', '<').replace('&gt;', '>').replace('&quot;', '"').replace('&#x27;', "'").replace('&amp;', '&');
        }
        return content;
    });
    exports.helper('$escapeHTML', (function() {
        var badChars = /&(?![\w#]+;)|[<>"']/g;
        var map = {
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#x27;",
            "&": "&amp;"
        };
        var fn = function(s) {
            return map[s];
        };
        return function(content) {
            return typeof content === 'string' ? content.replace(badChars, fn) : content;
        };
    })());
})(template);

//扩展jquery的行为
(function($) {
	//简单的模板替换不支持if和循环等语法
	$.fn.simpleRenderHtml=function(datas) {
		datas = datas || {};
		//判断页面是否渲染过
		if(!this.data('is_rendered')) {
			this.data('tpl_html', this.html().toString());
			this.data('is_rendered', true);
		}
		var tpl_html = this.data('tpl_html');
		var html = tpl_html.toString().replace(/\{([^\}]+?)\}/ig, function(a, b) {
			return datas[b] || "";
		});
		this.html(html);
		
		return this;
	};
	
	//页面元素的渲染
	$.fn.renderHtml=function(datas) {
		datas = datas || {};
		//支持反复渲染，并且缓存编译文件
		var id = this.attr('id');
		if(!id || typeof id == 'undefined') {
			//如果元素的id为空，随机生成
			id = "template_" + Math.random();
			this.attr('id', id);
		}
		template = template || {};
		template.render = template.render || $.noop;
		template.compile = template.compile || $.noop;
		
		//判断页面是否渲染过
		if(!this.data('is_compiled')) {
			this.data('render', template.compile(id, (this.html() || "").toString()));
			this.data('is_compiled', true);
		}
		var render = this.data('render');
		//渲染页面
		if(typeof render == 'function') {
			this.html(render(datas));
		}
		return this;
	};
})(jQuery);