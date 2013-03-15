<?php

class mNewsInfo extends mBase {
    
    protected $_dNewsInfo = null;
    
    public function __construct() {
        $this->_dNewsInfo = ClsFactory::Create('Data.dNewsInfo');
    }
    
    /**
     * 根据班级id获取班级的公告信息，包括了对应的添加人的信息
     * @param  $class_codes
     * @param  $filters
     */
    public function getNewsInfoByClassCode($class_codes , $filters = array()){
        if(empty($class_codes)) {
            return false;
        }
        
        $newsinfolist = $this->getNewsInfoBaseByClassCode($class_codes , $filters);
        //获取添加人的信息
        if(!empty($newsinfolist)) {
            
            $add_userlist = array();
            foreach($newsinfolist as $classcode=>$cclist) {
                foreach($cclist as $key=>$news) {
                    $add_userlist[] = $news['add_account'];
                }
            }
            
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($add_userlist);
            
            foreach($newsinfolist as $classcode => $cclist) {
                foreach($cclist as $key => $news) {
                    if(!empty($news['add_account']) && isset($userlist[$news['add_account']])) {
                    	$user = $userlist[$news['add_account']];
                        unset($user['add_date'], $user['add_account']);
                        $news = array_merge((array)$news, (array)$user);
                    }
                    $cclist[$key] = $news;
                }
                $newsinfolist[$classcode] = $cclist;
            }
            
            unset($userlist,$add_userlist);
        }
        
        return !empty($newsinfolist) ? $newsinfolist : false;
    }
    
    /**
     * 通过信息的接受者获取用户的消息信息
     * @param $touids
     */
    public function getNewsInfoByToUid($touids , $filters = array()) {
        if(empty($touids)) {
            return false;
        }
        
        $newsinfolist = $this->_dNewsInfo->getNewsInfoByToUid($touids);
        if(!empty($newsinfolist) && !empty($filters)) {
            foreach($filters as $filter=>$values) {
                $values = !empty($values) ? (is_array($values) ? $values : array($values)) : false;
                foreach($newsinfolist as $uid=>$nlist) {
                    foreach($nlist as $news_id=>$news) {
                        if(isset($news[$filter])) {
                            if(!empty($values) && !in_array($news[$filter] , $values)) {
                                unset($newsinfolist[$uid][$news_id]);
                            }
                        }
                    }
                }
            }
        }
        return !empty($newsinfolist) ? $newsinfolist : false;
    }
    
    /**
     * 根据班级id获取基本的公告信息
     * @param $class_codes
     * @param $filters
     */
    public function getNewsInfoBaseByClassCode($class_codes , $filters = array()) {
        if(empty($class_codes)) {
            return false;
        }
        
        $newsinfolist = $this->_dNewsInfo->getNewsInfoByClassCode($class_codes);
        //数据过滤,此处的过滤器和其他地方的解析方式不一样
        
        if(!empty($newsinfolist) && !empty($filters)) {
            
            foreach($filters as $field=>$values) {
                if($field == 'add_date') {
                    
                    if(empty($values) || strtotime($values) == false) {
                        $values = date("Y-m-d" , time());
                    }
                    $starttime = $values;
                    $endtime = date("Y-m-d" , strtotime($values) + 24 * 3600);
                    foreach($newsinfolist as $classcode=>$cclist) {
                        foreach($cclist as $key=>$news) {
                            if(isset($news[$field]) && ($news[$field] < $starttime || $news[$field] > $endtime)) {
                                unset($cclist[$key]);
                            }
                        }
                        $newsinfolist[$classcode] = $cclist;
                    }
                    
                } else {
                    
                    $values = (array)$values;
                    foreach($newsinfolist as $classcode=>$cclist) {
                        foreach($cclist as $key=>$news) {
                            if(isset($news[$field]) && !in_array($news[$field] , $values)) {
                                unset($cclist[$key]);
                            }
                        }
                        $newsinfolist[$classcode] = $cclist;
                    }
                    
                }
            }
        }
        
        return !empty($newsinfolist) ? $newsinfolist : false;
    }
    
    /**
     * 通过id获取信息
     * @param $news_id
     */
    public function getNewsInfoById($news_id) {
        if(empty($news_id)) {
            return false;
        }
        
        return $this->_dNewsInfo->getNewsInfoById($news_id);
    }
    
    /**
     * 增加对应的消息记录
     * @param $dataarr
     */
    public function addNewsInfo($dataarr = array(), $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        //return $this->_dNewsInfo->addNewsInfo($dataarr, $is_return_id);
        return $this->_dNewsInfo->addNewsInfo($dataarr, $is_return_id);
    }
    
    /**
     * 修改对应的消息记录
     * @param $dataarr
     * @param $news_id
     */
    public function modifyNewsInfo($dataarr = array() , $news_id) {
        if(empty($dataarr) || !is_array($dataarr) || empty($news_id)) {
            return false;
        }
        
        return $this->_dNewsInfo->modifyNewsInfo($dataarr , $news_id);
    }
    
    /**
     * 删除信息
     * @param $news_id
     */
    public function delNewsInfo($news_id) {
        if(empty($news_id)) {
            return false;
        }
        
        return $this->_dNewsInfo->delNewsInfo($news_id);
    }
    
 	/**
     * 通过id获取信息
     * @param $news_id
     */
    public function getNewsInfoByType($type) {
        if(empty($type)) {
            return false;
        }
        
        $wheresql = "news_type in('" . implode("','", (array)$type) . "')";
        return $this->_dNewsInfo->getInfo($wheresql);
    }

}
