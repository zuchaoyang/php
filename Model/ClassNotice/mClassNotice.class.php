<?php
class mClassNotice extends mBase {
    protected $_dClassNotice = null;
	
	public function __construct() {
		$this->_dClassNotice = ClsFactory::Create('Data.ClassNotice.dClassNotice');
	}
	
	
	/**
	 * 根据主键id获取班级公告
	 */
	public function getClassNotice($ids) {
	   if(empty($ids)) {
	       return false;
	   }  
	   
	   $resault = $this->_dClassNotice->getClassNotice($ids);
	    
	   return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 根据主键id获取班级公告
	 */
	public function getClassNoticeById($notice_ids) {
	   if(empty($notice_ids)) {
	       return false;
	   }  
	   
	   return $this->_dClassNotice->getClassNoticeById($notice_ids);
	}
	
	/**
	 * 添加公告信息
	 */
	public function addClassNotice($dataarr,$is_return_id) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNotice->add($dataarr,$is_return_id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	
	
	/**
	 * 根据主键修改班级公告
	 */
	public function modifyClassNotice($dataarr,$id) {
	    if(empty($id) || empty($dataarr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNotice->modify($dataarr,$id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 根据班级code查询最新的公告信息
	 */
	public function getLastNoticeByClassCode($class_code) {
	    if(empty($class_code)) {
	        return false;
	    }
	    
	    $wherearr = array(
	        'class_code = ' . $class_code,
	    );
	    
	    import('@.Common_wmw.WmwString');
	    $resault = $this->_dClassNotice->getLastNoticeByClassCode($wherearr,'notice_id desc',$offset=0,$limit=1);
	    foreach($resault as $notice_id => $notice_info) {
	        $notice_info['notice_content'] =  WmwString::delhtml(htmlspecialchars_decode($notice_info['notice_content']));
	        $resault[$notice_id] = $notice_info;
	    }
	    
	    return !empty($resault) ? $resault : false;
	}
	
	
	/**
	 * 根据主键删除公告信息
	 */
	public function delClassNotice($id) {
	    if(empty($id)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNotice->delete($id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 根据时间条件进行搜索
	 */
	
	public function getClassNoticeByClassCodeAndDate($where_appends,$orderby,$offset,$limit) {
	    if(empty($where_appends)) {
	        return false;
	    }
	    
	    $notice_list = $this->_dClassNotice->getInfo($where_appends,$orderby,$offset,$limit);
//	    dump($this->_dClassNotice->getLastSql());die;
	    return !empty($notice_list) ? $notice_list : false;
	}
	
}