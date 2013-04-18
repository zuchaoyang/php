/**
 * Galleria WMW Plugin 2013-03-27
 * AUTHOR lnczx
 *
 * Licensed under the MIT license
 * https://raw.github.com/aino/galleria/master/LICENSE
 *
 */

(function($) {

/*global jQuery, Galleria, window */

Galleria.requires(1.25, 'The WMW Plugin requires Galleria version 1.2.5 or later.');

// The script path
var src = $('script[src*="galleria.wmw.js"]:first').attr('src').toString();
var galleriaWmwRoot = src.substring(0, src.indexOf('galleria.wmw.js') - 1);

/**

    @class
    @constructor

    @example var wmw = new galleriawmw();

    @author lnczx

    @requires jQuery
    @requires Galleria

    @returns Instance
*/

function galleriaWmw (galleria, options, params, galleriaOptions){
    this.options = {
        showSize : 0,								// 总个数
        preloadSize : 10,							// 预先加载
        theme : 'classic/galleria.classic.min.js', 	// 默认样式
        imageSize: 'big',              				// 大图数据获取字段
        thumbSize: 'thumb',            				// 小图数据获取字段
        callback : {}
    };
    
    this.galleriaOptions = {
    	autoplay:5000,								// 自动播放    
    	transition : 'fade',						//
    	description: false            				// set this to true to get description as caption
    };
    
    this.params = {
    	page: 1                       				// default page  = 1           
    };
    
    this.dataSource = [];
    
    this.show_index = 0;

    this.require_optios = ['url', 'theme', 'imageSize', 'thumbSize'];
    
    this.init(galleria, options, params, galleriaOptions);
};

galleriaWmw.prototype = {
    
	//初始化函数
	init:function(galleria, options, params, galleriaOptions) {
		//合并默认参数信息
		this.setOptions(options || {});
		//检查必要的参数
		this.checkOptions();
		//设置查询参数信息
		this.setParams(params || {});
		//加载皮肤
		this.loadTheme(this.options.theme);
		//设置Galleria参数
		this.setGalleraiOptions(galleriaOptions || {});

		this._find();	
		
		this._findIndex();
		//Galleria控件初始化
		this.galleriaInit();
		//默认读取第一页数据
	
	},
	
    /**
     * Set wmw options
     * @param 设置参数options
     * @returns Instance
    */

	setOptions: function( options ) {
	    $.extend(this.options, options);
	    return this;
	},
	
    /**
     * Set wmw params
     * @param 设置ajax查询参数params
     * @returns Instance
    */

	setParams: function( params ) {
	    $.extend(this.params, params);
	    return this;
	},
	
    /**
     * Set wmw params
     * @param 设置ajax查询参数params
     * @returns Instance
    */

	setGalleraiOptions: function( options ) {
	    $.extend(this.galleriaOptions, options);
	    return this;
	},		
	
    /**
     * check wmw require options
     * 检查必须参数，不满足抛出Galleria.raise();
     * @returns boolean
    */	
	checkOptions:function() {
		var required_params = this.require_optios;
		for(var i in required_params) {
			var param = required_params[i];
			if(!this.options[param]) {
				Galleria.raise('Galleria Wmw checkOptions failed:配置参数:' + param + "是必须的!");
				return false;
			}
		}
		return true;
	},		

    /**
     * Load Galleria Theme
     * @param 设置参数options
     * @returns Instance
    */

	loadTheme: function(themeName) {
		if (typeof themeName == 'undefine') {
			themeName = 'classic/galleria.classic.min.js';
		}
		Galleria.loadTheme(galleriaWmwRoot + '/themes/' + themeName);
	    return this;
	},

    /**
     * galleriaInit
     * Galleria初始化及事件绑定。
    */	
	
    galleriaInit:function() {
    	var self = this;
    	Galleria.configure({
    		dataSource: self.dataSource,
    		show : self.show_index
    	});
		Galleria.run('#galleria', this.gelleriaOptions);
	    
		Galleria.ready(function() {		
				
		    $('.galleria-total').text(self.options.showSize); //update total # of slides
		    
		    // 全屏模式
		    $('#fullscreen_btn').click(function() {
		    	Galleria.get(0).toggleFullscreen(); // toggles the fullscreen
		    });
		    
		    this.addElement('exit').appendChild('container','exit');
		    var btn = this.$('exit').hide().text('关闭').click(function(e) {
		    	Galleria.get(0).exitFullscreen();
		    });
		    this.bind('fullscreen_enter', function() {
		        btn.show();
		    });
		    this.bind('fullscreen_exit', function() {
		        btn.hide();
		    });      
		    
		    //键盘事件
			this.attachKeyboard({
			    left: this.prev, // applies the native prev() function
			    right: this.next,
			    13: function() {
			        // start playing when return (keyCode 13) is pressed:
			        this.play(3000);
			    }
			});		    
		    
		    //图片切换事件
			this.bind('image', function(e) {
			    var photo_id = 0;
			    var title = '';
			    var description = '';
			    var cur_index = e.index;
			    
			    
			    //针对两种数据结构的取值
			    //1.从 img = src   属性 photo_id 中获取，在 e.galleriaData.original.attributes 中存在
			    //2.从data.push 中获得，在  e.galleriaData.id中获取
			    if (e.galleriaData) {
			    	var galleriaData = e.galleriaData;
			    	title = galleriaData.title;
			    	description = galleriaData.description;
			    	
			    	
			    	if (galleriaData.original) {
			    		var attributes = galleriaData.original.attributes;
				    	photo_id = attributes.photo_id.value;
				    } else if (galleriaData.id) {
				    	photo_id = galleriaData.id;
				    }
			    }
			    
			    var data = {photo_index : cur_index,
			                photo_id:photo_id,
			                title : title,
			                description: description};
			    

			    if (self.options.callback) {
			    	self.options.callback(data);
			    }
			     
			});		    
		    
			//左右箭头事件
//		    this.bind("loadstart", function(e) {
//		        $('.galleria-total').text(self.options.showSize); //update total # of slides

//		        //check e.index and see if more needs loaded
//		        //compare dataLength with (self.options.preloadSize+e.index)
//
//		        var pos = e.index + 1;
//		        var preloadSize = self.options.preloadSize;
//		        var dataLength = Galleria.get(0).getDataLength();
//		        
//		        if(pos%preloadSize == 0 && e.index!=0 && (dataLength < preloadSize + pos)){
//		            if(self.options.showSize==Galleria.get(0).getDataLength()) return; //show is done loading
//		            self.params.page = self.params.page + 1;
//		            self._find();
//		        }
//		    });	 			

		    //下一组右箭头事件
//			this.$('thumb-nav-right').click(function(e) {
//			        if ($('div').hasClass('galleria-thumb-nav-right disabled')){
//			            if(self.options.showSize==Galleria.get(0).getDataLength()) return; //show is done loading
//			            //load more images
//			            //get total loaded and add next self.options.preloadSize worth
//			            
//			            self.params.page = self.params.page + 1;
//			            self._find();         
//			        }
//			        //update total # of slides 
//			        // the .push is asynchronous, so total count will show total loaded
//			        // for a short time.
//			        $('.galleria-total').text(self.showSize); 
//			});	
			
		});		
		$("#galleria").css('display','block');  
		
    },
    
    /**
     * _find
     * 查找相册数据，依赖与设置的参数
    */	
	
    _find:function() {

    	var self = this;

   		var params = this.params;
		var url_params = "";
		for(var name in params) {
			if(!params[name]) {
				continue;
			}
			url_params += "/" + name + "/" + params[name];
		};

		$.ajax({
			type:"get",
			url:self.options.url + url_params,
			dataType:"json",
			async:false,
			success:function(json) {
				if(json.status < 0) {
					self.unbind("loadstart");
					return this;
				}
//				var gallery = Galleria.get(0);
				var photo_list = json.data;
				var data = [];
				for(var i in photo_list) {
						var photo = photo_list[i] || {};
						data.push({id : photo.photo_id,
								   thumb: photo.img_path + self._getSize(photo, self.options.thumbSize),
								   image: photo.img_path + self._getSize(photo, self.options.imageSize),
								   big: photo.img_path + self._getSize(photo, self.options.imageSize),
								   title: photo.name,
								   description: photo.description
								  
									   
						});
				}
				self.dataSource = data;
//				gallery.push(data);

			}
		});           
    },
    
    /**
     * _find_index
     * 根据photo_id 来查找定位显示
    */	
	
    _findIndex:function() {
    	var self = this;
    	if (!self.options.photo_id) return false;
    	if (self.options.photo_id == 0) return false;
    	if (self.dataSource ) {
    		var len = self.dataSource.length;
    		for(var i = 0; i < len; i++) {
    			var item = self.dataSource[i];

    			if (item.id == self.options.photo_id) {
    				self.show_index = i;
    				break;
    			}
    		}
    	};
    },    

// get image size by option name

    _getSize: function( photo, size ) {

        var img;

        switch(size) {

            case 'thumb':
                img = photo.file_small;
                break;

            case 'small':
                img = photo.file_big;
                break;

            case 'big':
                img = photo.file_big;
                break;

            default:
                img = photo.file_small || photo.file_middle;
                break;
        }
        return img;
    },

	//销毁对象
	destory:function() {
		Gallria.get(0).destroy();
	} 
};

$.fn.galleriaWmw=function(options, params, galleriaOptions, reset) {
	var successArr = [];
	this.each(function() {
		var elem = $(this).get(0);
		//重置galleriaWmw
		
		if(typeof reset == 'boolean' && reset) {
			if(!$.isEmptyObject(elem.galleriaWmw)) {
				elem.galleriaWmw.destory();
				elem.galleriaWmw = {};
			}
		}
		
		//避免重复初始化
		if(!$.isEmptyObject(elem.galleriaWmw)) {
			return elem.galleriaWmw;
		}

		var _galleriawmw = new galleriaWmw(this, options, params, galleriaOptions);
		elem._galleriawmw = _galleriawmw;
		successArr.push(_galleriawmw);
	});
	
	if(successArr.length > 0) {
		return successArr[0];
	}
	
	return false;
};

}( jQuery ) );