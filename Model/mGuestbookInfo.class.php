<?php

class mGuestbookInfo extends mBase{
	protected $_dGuestbookInfo = null;
	
	public function __construct() {
		$this->_dGuestbookInfo = ClsFactory::Create('Data.dGuestbookInfo');
	}
    protected $_filters = array(
        GET_GUESTBOOK_TYPE_PERSON => array('guestbook_type' => GUESTBOOK_TYPE_PERSON), 
        GET_GUESTBOOK_TYPE_CLASS => array('guestbook_type' => GUESTBOOK_TYPE_CLASS),
    );
    
    //过滤器的合并处理办法
    public function parseFileters($filters = array()) {
        if(empty($filters) || empty($this->_filters)) {
            return false;
        }
        
        $allow_vocation_list = array_keys($this->_filters);
        //过滤允许的业务号
        $allow_filters = array();
        foreach($filters as $vocation=>$value) {
            if($value === true && in_array($vocation , $allow_vocation_list)) {
                $allow_filters[$vocation] = $vocation;
            }
        }
        $new_filters = array();
        if(!empty($allow_filters)) {
            foreach($allow_filters as $filter) {
                $expressionarr = $this->_filters[$filter];
                foreach($expressionarr as $field=>$values) {
                    if(empty($values)) {
                        $values = 0;
                    }
                    $values = is_array($values) ? $values : array($values);
                    if(isset($new_filters[$field])) {
                        $news_filters[$field] = array_merge((array)$news_filters[$field] , (array)$values);
                    } else {
                        $new_filters[$field] = $values;
                    }
                }
            }
        }
        
        return !empty($news_filters) ? $news_filters : false;
    }
    
    /**
     * 通过主键获取留言信息
     * @param $liuyan_ids
     */
    function getGuestbookInfoById($guestbook_ids) {
        if(empty($guestbook_ids)) {
            return false;
        }
        $this->_dGuestbookInfo->getGuestbookInfoById($guestbook_ids);
        return $this->_dGuestbookInfo->getGuestbookInfoById($guestbook_ids);
    }
    


    /**
     * 根据添加人的账号信息获取留言信息
     * @param $uids
     */
    public function getGuestbookByAddUid($uids) {
        if(empty($uids)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->getGuestbookInfoByAddUid($uids);
    }
    
    /**
     * 根据用户的账号信息获取属于自己的留言信息
     * @param $uids
     */
    public function getGuestbookInfoByToUid($uids) {
        if(empty($uids)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->getGuestbookInfoByToUid($uids);
    }
    
    /**
     * 通过班级编号获取对应的班级动态
     * @param $class_codes
     * @param $filters
     */
    public function getGuestbookInfoByClassCode($class_codes , $filters = array(),$page) {
        if(empty($class_codes)) {
            return false;
        }
        
        $start = empty($page) ? 0 : ($page - 1) * 30;
        $guestbooklist = $this->_dGuestbookInfo->getGuestbookInfoByClassCode($class_codes,$start); 
         //过滤器要统一的在最原始的数据基础上进行，保证问题的简洁性
        if(!empty($guestbooklist) && !empty($filters)) {
            foreach($filters as $field=>$values) {
                $values = !empty($values) ? (is_array($values) ? $values : array($values)) : false;
                foreach($guestbooklist as $classcode=>$gblist) {
                    foreach($gblist as $gid=>$guestbook) {
                        if(isset($guestbook[$field])) {
                            if(!empty($values) && !in_array($guestbook[$field] , $values)) {
                                unset($gblist[$gid]);
                            } elseif($values == false && !empty($guestbook[$field])) {
                                unset($gblist[$gid]);
                            }
                        }
                    }
                    $guestbooklist[$classcode] = $gblist;
                }
            }
        }
        
        return !empty($guestbooklist) ? $guestbooklist : false;
    }
    
    
    /**
     * 增加留言信息
     * @param $dataarr
     */
    public function addGuestbookInfo($dataarr, $is_return_id) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->addGuestbookInfo($dataarr, $is_return_id);
    }
    /**
     * 根据留言主键删除留言信息
     * @param $guestbook_ids
     */
    public function delGuestbookInfo($guestbook_ids) {
        if(empty($guestbook_ids)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->delGuestbookInfo($guestbook_ids);
    }

    public function getGuestbookHfInfoByToUid($guestbook_ids) {
        if(empty($guestbook_ids)) {
            return false;
        }
        
        $wheresql = "upid in(" . implode("," , (array)$guestbook_ids) . ")";
        $result = $this->_dGuestbookInfo->getInfo($wheresql);
        
        return !empty($result) ? $result : false;
    }


    /**
     * 根据用户ID获取留言信息
     * @param $guestbook_ids
     */
    public function getGuestBookCountByGId($account) {
        if(empty($account)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->getGuestBookCountByGId($account);
    }

    public function getGuestBookByaccount($account,$CountDataNums,$gtype,$pagecount) {
        if(empty($account)) {
            return false;
        }
        
        return $this->_dGuestbookInfo->getGuestBookByaccount($account,$CountDataNums,$gtype,$pagecount);
    }

	//最新一条留言
    public function getGuestbookInfoByToAccount($account) {
        if(empty($account)) {
            return false;
        }
        
        
       $result = $this->_dGuestbookInfo->getGuestbookInfoByToAccount($account);
       
       return !empty($result) ? $result : false;
    }


}





























?>