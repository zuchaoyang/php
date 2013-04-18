<?php
return array(
        //'配置项'=>'配置值'
        'UC_DOMAIN'				=> 'vm1.wmw.cn',    			//正式环境配置 UC域名
      
        'COOKIEDOMAIN'			=> '.wmw.cn',        			//UC域名        
      	
      	'WMW_SERVER'			=> 'vm1.wmw.cn',				//主站网址
      	
      	'ZSCY_SERVER'			=> 'http://www.zscy.cn',				//雏鹰网址
      	  
        'APP_DOMAINS'			=> array('vm1.wmw.cn', 'www.zscy.cn'),       
        
        //'SHOW_ERROR_MSG'        => true,

        //'ERROR_MESSAGE'         =>'发生错误！',

		//'ERROR_PAGE'            =>'/Public/web_site_error',                 
        
        'APP_DEBUG' 			=> false , 		// 开启调试模式 

        'URL_CASE_INSENSITIVE' 	=> false, 		//不区分大小写

        'DB_TYPE'				=> 'Pdo',    	// 数据库类型 

        'DB_PREFIX'				=> 'wmw_', 		// 数据表前缀 

        'URL_DISPATCH_ON'		=> true,		// 是否启用Dispatcher

        'URL_MODEL' 			=>	2,			//启用REWRITE模式 
        
        'APP_GROUP_LIST'		=>'Adminapplication,Adminbase,Admingroup,Adminlogin,Adminuser,Amscontrol,Api,Basecontrol,Homeclass,Homefriends,Homepage,Homepzone,Homeuser,Homeusernews,Oa,Public,Resource,Smssend,Sso,Thirdapp,Uc,Unicominterface,Sns,Wms,Message,ClassManage	',//分组列表 

        'DEFAULT_GROUP'			=>'Sns', 	//默认分组

        'TMPL_FILE_DEPR'		=>'_', 			//改变模板文件位置的显示（例如:Tpl/default/Home/Index/index.html，改变后为Tpl/default/Home/Index_index.html） 

        'URL_HTML_SUFFIX'		=> '.shtml',  	// URL伪静态后缀设置

        'TMPL_ENGINE_TYPE' 		=> 'Smarty',	//模板引擎类型
        
        'TMPL_CACHE_ON' 		=> false,    	//关闭页面的静态缓存
        
        'CLIENT_ID'				=> '10001',		//主应用ID
        
		'CLIENT_SECRET'			=> '9386acaf3dcad63c9347',		//主应用ID    
		
        'SUPPORTED_OAUTH2_TYPE' => array('qzone'=> array('client_id'     => '100314837',
                                                         'client_secret' => '195f266d454a15d2dd977ddcec2cb5f4',
                                                         'callback'		 => 'http://my.wmw.cn'
                                                        )
                                        ),                           //支持的社会化登录,比如QQ,新浪等		
		    
        'GEARMAN_INFO' => array(
              'master'=> array(
                    'host' => '127.0.0.1',
                    'port' => '4730'
                    ),
              'slave'=> array(
                    'host' => '127.0.0.1',
                    'port' => '4731'
                    ),
                    
        ),                                  
                                        
 		'LOG_RECORD'            => true,   // 默认不记录日志

		'LOG_FILE_SIZE'         => 2097152, // 日志文件大小限制

        'LOG_RECORD_LEVEL'      => array('EMERG','ALERT','CRIT','ERR'),// 允许记录的日志级别                 		    
		    
                
        //如果需要修改以下资源配置，请联系杨益
        //DB配置
        'DB_INFO' => array(
                //默认db资源
                'main' => array(
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        ),
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        )
                    ),
                'sgip_sms' => array(
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        ),
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        )
                    ),
                'bms' => array(
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        ),
                    array(
                        'host' => '192.168.254.88',
                        'port' => '3306',
                        'user' => 'root',
                        'password' => 'root',
                        'db_name' => 'wmw_aries'
                        )
                    ),
                'wm_cy' => array(
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'cy_online'
                            ),
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'cy_online'
                            )
                        ),
                        
               'old_resource' => array(
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'xiaoyuan-ziyuan'
                            ),
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'xiaoyuan-ziyuan'
                            )
                        ),
                'resource' => array(
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'study_resource'
                            ),
                        array(
                            'host' => '192.168.1.254',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'www.wmw.cn',
                            'db_name' => 'study_resource'
                            )
                        ),
                'oa' => array(
                        array(
                            'host' => '192.168.254.88',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'root',
                            'db_name' => 'wmw_aries'
                            ),
                        array(
                            'host' => '192.168.254.88',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'root',
                            'db_name' => 'wmw_aries'
                            )
                        ),        
                'user' => array(
                        array(
                            'host' => '192.168.254.88',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'root',
                            'db_name' => 'wmw_aries'
                            ),
                        array(
                            'host' => '192.168.254.88',
                            'port' => '3306',
                            'user' => 'root',
                            'password' => 'root',
                            'db_name' => 'wmw_aries'
                            )
                        ),
                ),
                
                'REDIS_INFO' => array(
                    'host' => '127.0.0.1',
                    'port' => '6379',
                ),
                
                //如果需要修改以下资源配置，请联系杨益
                //MC配置
                'MC_INFO' => array(
                        'main' => array(
                            'host' => '192.168.1.254',
                            'port' => '11211'
                            )
                        ),
                 //页面请求别名配置
                 'REQUEST_ALIAS' => array(
                        array(
                            'class_code',
                            'classCode',
                            'cid',
                        ),
                        array(
                            'school_id',
                            'schoolid',
                        ),
                        array(
                            'grade_id',
                            'gradeid'
                        ),
                  ),       
                );
