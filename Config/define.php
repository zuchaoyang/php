<?php

/*重要的常量定义 *****************************************************************************/
define('AUTH_KEY' , 'www.wmw.cn');					//token key
define('LIBRARIES_DIR' , 'Libraries');					//librarirs目录
define('IMG_SERVER' , '');								//图片服务器地址

define('MD5_PASSWORD' , md5('000000'));      			//md5默认密码
define('NO_MD5_PASSWORD' , '000000');      				//明文的默认密码

define('SNS_SESSION_TOKEN' , 'snssessiontoken');		//前台session token名称
define('WMS_SESSION_TOKEN' , 'wmssessiontoken');	    //后台ssession token名称
define('BMS_SESSION_TOKEN' , 'bmssessiontoken');		//基地ssession token名称
define('AMS_SESSION_TOKEN' , 'amssessiontoken');		//基地ssession token名称

define('COOKIE_TIME_OUT', 3600*12);                     //COOKIE过期时间 = 12小时 

define('OPERATION_STRATEGY_DEFAULT' , 1);  				//学校运营策略  1:默认无策略
define('OPERATION_STRATEGY_HLJ' , 2);    	 			//学校运营策略  2:黑龙江联通
define('OPERATION_STRATEGY_CZ' , 3);    				//学校运营策略  3:常州电信
define('OPERATION_STRATEGY_JL' , 4);    	 			//学校运营策略  4:吉林联通
define('OPERATION_STRATEGY_GD' , 5);    	 			//学校运营策略  5:广东联通
define('OPERATION_STRATEGY_LN' , 6);    	 			//学校运营策略  6:辽宁联通
define('OPERATION_STRATEGY_CQ' , 7);    	 			//学校运营策略  7:重庆联通

define('COPYRIGHT' , '版权所有 Copyright&copy; www.wmw.cn 京ICP备05067399号 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;地址：北京中青网脉科技发展有限公司 北京市东城区前门东大街五号 邮编：100006');
define('COPYRIGHTBR' , '版权所有 Copyright&copy; www.wmw.cn 京ICP备05067399号<br />地址：北京中青网脉科技发展有限公司 北京市东城区前门东大街五号 邮编：100006');

//UC之后的版权，以后用下边的定义，上边的版权定义会删掉
define('WMW_COPYRIGHT' , '版权所有 Copyright&copy; www.wmw.cn 京ICP备05067399号');
define('WMW_COMPANY' , '单位：北京中青网脉科技发展有限公司');
define('WMW_ADDRESS' , '地址：北京市东城区前门东大街五号');
define('WMW_ZIP_CODE' , '邮编：100006');

define('WMW_CS_PHONE' , '400-616-0300');				//WMW客服电话
define('WMW_CS_EMAIL' , 'service@wmw.cn');				//WMW客服邮箱
define('WMW_APPEAL_EMAIL' , 'wmsd@wmw.cn');				//客户申诉邮箱



define('IS_SET_OLDACCOUNT_IMPORT' , -1); 				//是否开启老账号导入按钮 1：开启 -1：关闭


define('SORT_TIME_OUT', 60); 							//发送邮件最少间隔时间 60秒 重复发送平凡为恶意发送
define('SETEMAIL_TIME_OUT', 3600*48); 					//设置邮箱 验证邮件过期时间 48 小时有效
define('FINDPWD_EMAIL_TIME_OUT', 3600*12); 				//邮箱找回密码 验证邮件过期时间 12 小时有效
define('FINDPWD_PHONE_TIME_OUT', 300);					//手机找回密码 短信验证码过期时间 5分钟有效

define('UPGRADE_MONTH' , 8);							//班级的年级升迁月份
define('UPGRADE_SECRET_KEY', 'class_upgrade_secret_key_5381');   //班级升级密钥

/*我们网常量定义 *********************************************************************************/


define('ACTIVE_MINUTE', 20);                            //设定多少分钟内的用户为在线用户
define('ACTIVE_DATE', 7);                            //设定多少分钟内的用户为在线用户
define('REDIS_USER_MAX_LIFE', 10080);                   //设定用户缓存数据的过期时间  7天 * 24小时 * 60分钟  

define('CLIENT_TYPE_STUDENT' , 0);						//用户类型为学生
define('CLIENT_TYPE_TEACHER' , 1);						//用户类型为老师
define('CLIENT_TYPE_FAMILY' , 2);						//用户类型为家长
//define('CLIENT_TYPE_ADMIN' , 4);                        //用户类型为管理员
//define('CLIENT_TYPE_SCHOOLADMIN' , 5);					//用户类型学校管理员

define('CLIENT_STOP_FLAG', -1);        					//用户冻结标记
define('CLIENT_STOP_FLAG_NORMAL', 0);        			//用户冻结标记
//define('CLIENT_STOP_FLAG_VALID', 1);        			//用户有效冻结标记
define('CLIENT_STOP_FLAG_FOREVER', 2);        			//用户永久冻结标记
//define('CLIENT_STOP_FLAG_MANAGE' , 3);        			//管理员或者老师冻结标记

define('BUSINESS_ENABLE_NO' , 0);           			//业务凭证号(手机号)状态 ：未激活（未开通）
define('BUSINESS_ENABLE_YES' , 1);          			//业务凭证号(手机号)状态 ：已激活（已开通）
define('BUSINESS_ENABLE_CLOSE' , 2);        			//业务凭证号(手机号)状态 ：激活后撤销了（开通后又关闭了业务）

define('SCHOOL_STATUS_UNTREATED' , 0);               	//学校申请为处理
define('SCHOOL_STATUS_PASS' , 1);                    	//学校通过审核
define('SCHOOL_STATUS_REFUSE' , 2);                  	//学校申请被拒绝

define('TEACHER_CLASS_ROLE_CLASSADMIN' , 1);			//班级班主任
define('TEACHER_CLASS_ROLE_CLASSTEACHER' , 2);			//班级任课老师
define('TEACHER_CLASS_ROLE_CLASSBOTH' , 3);				//班级任和课老师

define('NO_CLASS_ADMIN' , 0);           				//不是班级管理员
define('IS_CLASS_ADMIN' , 1);          					//是班级管理员

define('CLASS_FEED_NOTICE' , 1); 						//班级通告
define('CLASS_FEED_WORK' , 2); 							//班级作业
define('CLASS_FEED_MARK' , 3); 							//班级成绩
define('CLASS_FEED_CURRICULUM' , 4); 					//班级课程表
define('CLASS_FEED_LOG' , 5); 							//班级日志
define('CLASS_FEED_ALBUM' , 6); 						//班级相册
define('CLASS_FEED_TALK' , 7); 							//班级说说

define("FEED_TYPE_SIGN" , 0);               			//个人说说
define("FEED_TYPE_LOG" , 1);            				//个人日志
define("FEED_TYPE_PHOTO" , 2);            				//个人相册
define("FEED_TYPE_GUESTBOOK" , 3);						//个人留言

define('ALBUM_USER_CREATE' , 1);						//用户创建相册
define('ALBUM_SYS_CREATE' , 2);							//系统创建相册

define('LOG_USER_CREATE' , 1);							//用户创建日志
define('LOG_SYS_CREATE' , 2);							//系统创建日志

define('CLIENT_CLASS_ROLE_PT' , 1);    					//普通学生
define('CLIENT_CLASS_ROLE_BZ' , 2);    					//班长
define('CLIENT_CLASS_ROLE_XW' , 3);    					//学习委员
define('CLIENT_CLASS_ROLE_JW' , 4);    					//纪律委员
define('CLIENT_CLASS_ROLE_TW' , 5);    					//体育委员

define('GUESTBOOK_TYPE_PERSON' , 2);    				//个人留言
define('GUESTBOOK_TYPE_CLASS' , 1);     				//班级留言

define('NEWS_INFO_XT' , 'XT');         					//系统消息
define('NEWS_INFO_YY' , 'YY');        					//应用消息
define('NEWS_INFO_BJYQ' , 'BJYQ');    					//班级邀请
define('NEWS_INFO_BJSQ' , 'BJSQ');    					//班级申请
define('NEWS_INFO_HY' , 'HY');        					//好友消息
define('NEWS_INFO_XT_FRIEND' , 'FXT');        			//系统消息

/*用于动态内容归类*/
define('NEWS_INFO_BJTG' , 'BJTG');     					//班级通告
define('NEWS_INFO_BJZY' , 'BJZY');     					//班级作业

define('PERSON_FEED_TALK' , 1); 						//个人动态新鲜事类型
define('PERSON_FEED_LOG' , 2); 							//个人动态日志类型
define('PERSON_FEED_ALBUM' , 3); 						//个人动态相册类型
define('PERSON_FEED_LEAVE' , 4); 						//个人动态留言板类型

define('FEED_NEW' , 0); 								//新动态（新添加的动态）
define('FEED_UPD' , 1); 								//修改的动态
define('FEED_DEL' , 2); 								//删除的动态

define('WMW_XXS_LIMIT' , 5);							//默认显示新鲜事数量

define('WMW_ALLUSER' , 0);                                    //所有人
define('WMW_FRIENDS' , 1);                                    //仅好友空间
define('WMW_MYSELEF' , 2);                                    //仅自己

/*班级成绩模块*/
define("IS_SMS", 1);                                     //全部发送短信
define("NO_SMS", 0);                                     //全部没有发短信
define("PORTION_SMS", 2);                                //部分发送短信

define("IS_PUBLISHED", 1);                               //发布 （非草稿）
define("NO_PUBLISHED", 0);                               //未发布 （草稿）
define("EXAM_EXCEL_EXPIRY_TIME", 3600 * 24);             //成绩导入模板 过期时间 
define("EXAM_EXCEL_NAME_PREFIX", "exam_excel_");         //成绩导入模板文件名前缀 

//成绩通知家长短信内容模板 %s 分别表示（学生姓名，科目名，考试名，本次考试成绩，老师评语，平均分，最高分，最低分）
//eg:阿成的家长，您的孩子在语文期中考试的成绩为:75，一切只能靠你自己。 班平均分:80.8，最高分100，最低分59
define("EXAM_SMS_TEMPLET", "%s的家长，您的孩子在%s%s的成绩为:%s，%s班平均分:%s，最高分%s，最低分%s"); 
   
     

/*中少雏鹰网常量定义 *****************************************************************************/
define('TEAM_DUTUDIES_TEAM_HEAD' , 1);             		//小队队长
define('TEAM_DUTUDIES_TEAM_ASSISTANT' , 2);       		//小队副队长
define('TEAM_DUTUDIES_TEAM_MEMBER' , 3);           		//小队队员

define('GHGL_NAME', 1);//关怀鼓励
define('XGWJ_NAME' , 2);//相关文件
define('GRT_NAME' , 3);//光荣台
define('CYBK_NAME' , 4);//雏鹰百科
define('TZGG_NAME' , 5);//通知公告



/*OA常量定义 ************************************************************************************/
define('SCHEDULE_IS_DRAFT_FALSE' , 0); 					//日程草稿状态 0 不是草稿 1 为草稿
define('SCHEDULE_IS_DRAFT_TRUE' , 1); 					//日程草稿状态 0 不是草稿 1 为草稿



/*学习资源常量定义 ************************************************************************************/
define('RESOURCE_FEED_STATE_DEL' , 0);            		//资源导入删除的状态
define('RESOURCE_FEED_STATE_REAL' , 1);           		//资源导入真实有用的状态
define('RESOURCE_FEED_STATE_USELESS' , 2);           	//资源导入附加的状态

/**
 * 动态类型（feed_type）
 */
define('FEED_MOOD', 1);
define('FEED_BLOG', 2);
define('FEED_ALBUM', 3);

define('FEED_ACTION_PUBLISH', 1);                       //发布产生的动态
define('FEED_ACTION_COMMENT', 2);                       //评论产生的动态

