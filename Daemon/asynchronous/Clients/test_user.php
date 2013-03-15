<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');

$result = Gearman::send('default_usr_login', '11070004', PRIORITY_NORMAL, false);



    //登录初始化测试
//$m = ClsFactory::Create('RModel.mUserVm');
//
//    //班主任帐号
//print_r($m->init_user_data('56067742'));
//
//    //老师帐号
////print_r($m->init_user_data('45448225'));
//
//    //学生帐号
//print_r($m->init_user_data('14456343'));
//
//    //家长帐号
//print_r($m->init_user_data('12676938'));
//print_r($m->init_user_data('12676938'));



//    //获取用户基本信息
//echo "=================getClientAccountById===================================== \n";    
//print_r($m->getClientAccountById('56067742'));
//
//echo "=================getUserBaseByUid===================================== \n";    
//print_r($m->getUserBaseByUid('56067742'));
//
//    //在线用户测试
//$m = ClsFactory::Create('RModel.Common.mSetLiveUser');
//    //在线用户集合
//
//$m->ping('56067742');
//$m->ping('12441111');
//
//echo "=================getLiveUserSet===================================== \n";
//print_r($m->getLiveUserSet());
//
//echo "=================getLiveClassUserSet===================================== \n";
//print_r($m->getLiveClassUserSet(696));
//print_r($m->getLiveClassUserSet(146));
//echo "=================getLiveUseUserSet===================================== \n";
//print_r($m->getLiveUserFriendsSet(56067742));


//    //活跃用户测试
//$m = ClsFactory::Create('RModel.Common.mSetActiveUser');
//    
//
//$m->addActiveUser('56067742');
//
////活跃用户集合
//echo "=================getLiveUserSet===================================== \n";
//print_r($m->getActiveUserSet());

////获取用户所有关系用户测试
//$id = '11070004';
//$m_user = ClsFactory::Create('RModel.mUserVm');
//
//$all_friends = $m_user->getUserAllFriends($id);
//
//if (empty($all_friends)) return false;
//print_r($all_friends);
//// 获取活跃用户库
//$m_active_user = ClsFactory::Create('RModel.Common.mSetActiveUser');
//
//$active_uids = $m_active_user->getActiveUserSet();
//
//print_r($active_uids);
//$active_friends =  array_intersect($all_friends, $active_uids);
//print_r($active_friends);


