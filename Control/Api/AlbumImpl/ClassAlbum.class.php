<?php
import('@.Control.Api.AlbumImpl.AlbumInfo');
/**
 * @author sailong
 *功能：班级相册API
 *说明：关于班级相册，相片的增删改查
 */
class ClassAlbum {
    
    protected $_mAlbumClassRelation = null;
    protected $_AlbumInfo = null;
    
    public function __construct() {
        $this->_mAlbumClassRelation = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        
        $this->_AlbumInfo = new AlbumInfo();
    }
    
    /**
     * 添加班级相册
     * 
     * @return $album_id
     */
    public function addClassAlbum($album_data) {
        if(empty($album_data)) {
            return false;
        }
        
        $album_id = $this->_AlbumInfo->addAlbum($album_data);
        if(empty($album_id)) {
            return false;
        }
        //添加关系信息
        $relation_data = array(
            'album_id'   => $album_id,
            'class_code' => $album_data['class_code'],
        	'grant'      => $album_data['grant']
        );
        $rel_id = $this->_mAlbumClassRelation->addAlbumClassRel($relation_data, true);
        if(empty($rel_id)) {
            $this->_AlbumInfo->delAlbum($album_id);
            return false;
        }
        
        return $album_id;
    }
    
    /**
     * 获取相册信息
     * 
     * @return $album_info
     */
    public function getClassAlbumByAlbumId($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
        
        //获取班级相册信息
        
        $rel_info = $this->check_class_album_rel($album_id, $class_code);
        
        $rel_info = reset($rel_info);
        if(empty($rel_info)) {
            return false;
        }
        $album_info = $this->_AlbumInfo->getAlbum($album_id);
        //相册权限
        $grant_id = $rel_info['grant'];
        $grant_id = empty($grant_id) ? 0 : $grant_id;
        $grant_name = $this->grant_arr($grant_id);
        $album_info[$album_id]['grant'] = $grant_id;
        $album_info[$album_id]['grant_name'] = $grant_name;
        
        return !empty($album_info) ? $album_info : false;
    }
    
    /**
     * 获取相册列表
     * 
     * @return $album_list
     */
    public function getClassAlbumListByClassCode($class_code, $offset = null, $limit = null) {
        if(empty($class_code)) {
            return false;
        }
        //班级相册关系列表
        $rel_list = $this->_mAlbumClassRelation->getAlbumClassRelByClassCode($class_code, $offset, $limit);
        $rel_list = $rel_list[$class_code];
        if(empty($rel_list)) {
            return false;
        }
        $album_ids = array();
        $album_id_to_key_rel_list = array();
        foreach($rel_list as $rel_key=>$rel_val) {
            $album_ids[$rel_val['album_id']] = $rel_val['album_id'];
            $album_id_to_key_rel_list[$rel_val['album_id']] = $rel_val;
        }
        unset($rel_list);
        //获取相册信息列表
        $album_list = $this->_AlbumInfo->getAlbum($album_ids);
     
        if(empty($album_list)) {
            return false;
        }
        
        //相册权限
        
        if(!empty($album_id_to_key_rel_list)) {
            foreach($album_list as $album_id=>$album_info) {
                $grant_id = $album_id_to_key_rel_list[$album_id]['grant'];
                $grant_id = empty($grant_id) ? 0 : $grant_id;
                $grant_name = $this->grant_arr($grant_id);
                $album_list[$album_id]['grant_id'] = $grant_id;
                $album_list[$album_id]['grant_name'] = $grant_name;
            }
        }
        
        return !empty($album_list) ? $album_list : false;
    }
    /**
     * 相册权限
     */
    /**
     * 检测班级相册关系信息
     * 
     * @return $rel_list
     */
    private function check_class_album_rel($album_id, $class_code) {
        if(empty($class_code) || empty($album_id)) {
            return false;
        }
        
        $rel_list = $this->_mAlbumClassRelation->getAlbumClassRelByClassAlbumId($album_id, $class_code);
        
        if(empty($rel_list)) {
            return false;
        }
     
        return $rel_list;
    }
    
    /**
     * 删除班级相册
     */
    public function delClassAlbum($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
        
        //获取班级相册关系信息
        
        $rel_info = $this->check_class_album_rel($album_id, $class_code);
        $rel_info = reset($rel_info);
        if(empty($rel_info)) {
            return false;
        }
        //删除班级相册关系信息
        $affect_rows = $this->_mAlbumClassRelation->delAlbumClassRelById($rel_info['id']);
        if(empty($affect_rows)) {
            return false;
        }
        
        //删除实体
        $affect_rows = $this->_AlbumInfo->delAlbum($album_id);
        
        return $affect_rows;
    }
    
    /**
     * 修改班级相册
     * 
     * @return $affect_rows
     */
    public function updClassAlbum($album_data, $album_id, $class_code) {
        if(empty($album_data) || empty($album_id) || empty($class_code)) {
            return false;
        }
        
        //获取班级相册关系信息
        $rel_info = $this->check_class_album_rel($album_id, $class_code);
        $rel_info = reset($rel_info);
        if(empty($rel_info)) {
            return false;
        }
        
        //修改相册信息
        $affect_rows = $this->_AlbumInfo->updAlbum($album_data, $album_id);
        //修改班级权限信息
        if(empty($affect_rows)) {
            return false;
        }
        $this->_mAlbumClassRelation->modifyAlbumClassRelById(array('grant'=>$album_data['grant']), $rel_info['id']);
       
        return !empty($affect_rows) ? $affect_rows : false;
    }
    
    /**
     * 相册权限常量         提取到公共配置文件中*******************************************
     * @param int $grant_id
     * 
     * @return Array || String
     */
    public function grant_arr($grant_id) {
        $grant_arr = array(
            0=>"公开（所有人可见）",
            1=>"本班",
            2=>"本学校"
        );
        if($grant_id === NULL) {
            return $grant_arr;
        }
        
        return $grant_arr[$grant_id];
    }
    
    
}