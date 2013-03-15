<?php
import('@.Control.Api.AlbumImpl.Album');
/**
 * 
 * @author sailong
 *功能：班级相册API
 *说明：关于班级相册，相片的增删改查
 */
class ByClass extends Album {
    /**
     * 创建班级相册
     * @param Array $datas
     * 
     * @return int 相册ID
     */
    public function create($datas, $class_code) {
        //判断是否为空
        if(empty($datas)) {
            return false;
        }
        
        //检查班级是否存在
        $this->class_is_exist($class_code);
        
        //添加相册信息
        $album_id = $this->add($datas);
        if(empty($album_id)) {
            return false;
        }
        $datas['album_id'] = $album_id;
        //添加相册关系
        $rel_id = $this->add_rel($datas, $class_code);
        if(empty($rel_id)) {
            //删除相册信息
            $this->delete($album_id);
            return false;
        }
        
        //添加相册权限
        $grant_id = $this->add_grant($datas, $class_code);
        if(empty($grant_id)) {
            //删除相册信息
            $this->delete($album_id);
            //删除班级相册关系
            $this->del_rel($rel_id);
            return false;
        }
        
        return $album_id;
    }
    
    
    
    /**
     * 根据班级class_code只获取相册表信息
     * 
     * @param int $class_code
     * 
     * @return array album_list
     */
    public function getOnlyAlbumListByClassCode($class_code) {
        if(empty($class_code)) {
            return false;
        }
        //班级是否存在
        $this->class_is_exist($class_code);
        //从关系表中得到相册信息
        $album_ids = $this->get_album_ids_by_rel($class_code);
       
        //该班级是否存在相册
        if(empty($album_ids)) {
            return false;
        }
        //通过相册album_id获取相册信息
        $album_list = $this->get($album_ids);
        
        return !empty($album_list) ? $album_list : false;
    }
    
    
    /**
     * 通过班级class_code获取相册列表信息
     * @param $class_code
     * @param $offset
     * @param $limit
     * 
     * @return Array
     */
    public function getListByClass($class_code, $offset = null, $limit = null) {
        if(empty($class_code)) {
            return false;
        }
        //班级是否存在
        $this->class_is_exist($class_code);
        //从关系表中得到相册信息
        $album_ids = $this->get_album_ids_by_rel($class_code, $offset, $limit);
        //该班级是否存在相册
        if(empty($album_ids)) {
            return false;
        }
        //通过相册album_id获取相册信息
        $album_list = $this->get($album_ids);
        //是否有相关相册的信息
        if(empty($album_list)) {
            return false;
        }
        
        $album_ids = array_keys($album_list);
        //获取相册权限
        $grant_list = $this->get_grant_by_album_id($album_ids);
        //合并权限和相册信息
        $album_list = $this->merge_album_rel_data($album_list, $grant_list);
        $album_list = $this->parse($album_list);
        
        return !empty($album_list) ? $album_list : false;
        
    }
    /**
     * 输出格式
     * @param $album_list
     * 
     * @return Array
     */
    private function parse($album_list) {
          import('Model.mUser');
          $mUser = new mUser();
          
          foreach($album_list as $album_id=>$val) {
              $user_info = $mUser-> getUserBaseByUid($val['add_account']);
              $user_info = reset($user_info);
              $album_list[$album_id]['client_name'] =$user_info['client_name'];
          }
          
          return $album_list;
    }
   
    
    /**
     * 通过班级class_code和相册album_id获取相册信息
     * @param $album_id
     * @param $class_code
     * 
     * @return Array
     */
    public function getAlbumByClassAlbumId($album_id, $class_code) {
        if(empty($class_code) || empty($album_id)) {
            return false;
        }
        //班级是否存在
        $this->class_is_exist($class_code);
        //通过相册album_id获取相册信息
        $album_list = $this->get($album_id);
        //是否有相关相册的信息
        if(empty($album_list)) {
            return false;
        }
        //检测相册关系是否存在
        $rel = $this->get_rel_by_album_id($album_id, $class_code);
       
        //该班级是否存在相册
        if(empty($rel)) {
            return false;
        }
        //获取相处权限
        $grant_list = $this->get_grant_by_album_id($album_id);
        
        $album_list = $this->merge_album_rel_data($album_list,$grant_list);
        $album_list = $this->parse($album_list);
        
        return !empty($album_list) ? $album_list : false;
        
    }
    
    /**
     * 通过相册album_id和班级class_code获取相册关系信息
     * @param $album_id
     * @param $class_code
     * 
     * @return Array
     */
    public function get_rel_by_album_id($album_id, $class_code) {
        if(empty($album_id) && empty($class_code)) {
            return false;
        }
        $mAlbum = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        $rel = $mAlbum->getAlbumClassRelByClassAlbumId($album_id, $class_code);
        
        return !empty($rel) ? $rel : false;
    }
    
    /**
     * 删除班级相册信息
     * @param $album_id
     * @param $class_code
     * 
     * @return bool
     */
    public function delAlbumByClass($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
        
        //删除班级相册关系
        $mAlbumClassRelation = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        $rs = $mAlbumClassRelation->delByAlbumId($album_id);
        if(empty($rs)) {
            echo "删除班级相册关系失败";
            return false;
        }
        //删除相册权限
        $mAlbumClassGrants = ClsFactory::Create('Model.Album.mAlbumClassGrants');
        $rs = $mAlbumClassGrants->delByAlbumId($album_id);
        if(empty($rs)) {
           echo "删除相册权限失败";
           return false;
        }
        
        $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        //删除相册中照片的评论信息
        $photo_list = $mAlbumPhotos->getPhotosByAlbumId($album_id);
        if(!empty($photo_list)) {
            $photo_ids = array_keys($photo_list[$album_id]);
            $mAlbumPhotoComments = ClsFactory::Create('Model.Album.mAlbumPhotoComments');
            $comments_list = $mAlbumPhotoComments->getAlbumPhotoCommentByPhotoId($photo_ids);
            
            if(!empty($comments_list)) {
                $rs = $mAlbumPhotoComments->delByPhotoId($photo_ids);
                if(empty($rs)) {
                    echo "删除相册评论失败";
                    return false;
                }
            }
        }
        
        //删除照片信息
        $photo_lists = $mAlbumPhotos->getPhotosByAlbumId($album_id);
        if(!empty($photo_lists)) {
            $rs = $mAlbumPhotos->delByAlbumId($album_id);
            if(empty($rs)) {
                echo "删除相册照片信息失败";
                return false;
            }
        }
        
        //删除相册信息
        $rs = $this->delete($album_id);
        if(empty($rs)) {
            echo "删除相册信息失败";
            return false;
        }
        //删除照片实体
        
        return true;
    }
    
    /**
     * 设置相册封面
     * @param String $album_img 图片名称
     * @param Int $album_id
     * 
     * @return bool
     */
    public function setAlbumImg($album_img, $album_id) {
        $data = array(
            'album_img'=>$album_img,
            'upd_time'=>time()
        );
        return $this->upd($data,$album_id);
    }
    
    /**
     * 添加照片信息
     * @param array $dataarr
     * 
     * @return boolean
     */
    
    public function addClassPhoto($dataarr,$is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->addPhoto($dataarr,$is_return_id);
    }
    
    /**
     * 根据相册ID获取相片列表
     * @param int $album_id
     * 
     * @return array $photo_list
     */
    public function getClassPhotoListByAlbumId($album_id, $offset=null, $limit=null) {
        if(empty($album_id)) {
            return false;
        }
        
        return $this->getPhotoListByAlbumId($album_id, $offset, $limit);
    }
    
    /**
     * 根据相册ID获取相片列表
     * @param int $album_id
     * 
     * @return array $photo_list
     */
    public function getClassPhotoByPhotoId($photo_ids) {
        if(empty($photo_ids)) {
            return false;
        }
        
        return $this->getPhotoByPhotoId($photo_ids);
    }
    
    /**
     * 根据相册id获取相册中的相册数量
     * @param int $album_id
     * 
     * @return int $count;
     */
    public function getClassPhotoCountByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        
        return $this->getPhotoCountByAlbumId($album_id);
    }
    /**
     * 修改相片信息
     * @param array photo_info
     * @param int   photo_id
     * 
     * @return boolean
     */
    public function updClassPhotoByPhotoId($datas,$photo_id) {
        if(empty($datas) || empty($photo_id)) {
            return false;
        }
        return $this->updPhotoByPhotoId($datas,$photo_id);
    }
    
    /**getClassPhotoCountByAlbumId
     * 删除照片信息
     * @param int $photo_id
     * 
     * @return bool
     */
    public function deletePhotoByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        
        return $this->delPhotoByPhotoId($photo_id);
    }
    
    /**
     * 移动相片到另一相册
     * @param $album_id
     * @param $photo_id
     * 
     * @return bool
     */
    public function movePhoto($album_id, $photo_id) {
        if(empty($album_id) && empty($album_id)) {
            return false;
        }
        $data = array(
            'album_id'=>$album_id
        );
        $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        $rs = $mAlbumPhotos->modifyPhotoByPhotoId($data,$photo_id);
        
        return $rs;
    }
    
    /**
     * 修改相册信息
     * @param $data
     * @param $album_id
     * @param $class_code
     * 
     * @return bool
     * 
     */
    public function updAlbum($data, $album_id, $class_code) {
        if(empty($data) || empty($album_id)) {
            return false;
        }
        //检测相册是否存在
        $album_list = $this->get($album_id);
        if(empty($album_list)) {
            return false;
        }
        $mAlbum = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        $rel = $mAlbum->getAlbumClassRelByClassAlbumId($album_id, $class_code);
        //该班级是否存在相册
        if(empty($rel)) {
            return false;
        }
        $data = $this->remove_empty($data);
        if(empty($data)) {
            return false;
        }
        $album_rs = $this->upd($data, $album_id);
        
        //修改相册权限
        $grant_list = $this->get_grant_by_album_id($album_id);
        if(empty($grant_list)) {
            return false;
        }
        $grant_list = reset($grant_list);
        $grant_id = key((array)$grant_list);
        if(empty($grant_id)) {
            return false;
        }
        $grant_list['grant'] = $data['grant'];
        $mAlbumClassGrants = ClsFactory::Create('Model.Album.mAlbumClassGrants');
        $grant_rs = $mAlbumClassGrants->modifyAlbumClassGrantById($grant_list, $grant_list['id']);
       
        if((!empty($grant_rs) || !empty($album_rs)) || (!empty($grant_rs) && !empty($album_rs))) {
            return true;
        }
        
        return false;
    }
    /**
     * 获取相册评论
     */
    public function getClassPhotoCommentByUpId($up_id,$offset=null,$limit=null) {
        if(empty($up_id)) {
            return false;
        }
        $level = 1;
        $comment_list = $this->getCommentListByUpId($up_id,$level,$offset,$limit);
        
        return $comment_list;
    }
    /**
     * 获取二级评论
     */
    public function getClassPhotoSecCommentByUpId($up_id,$offset=null,$limit=null) {
        if(empty($up_id)) {
            return false;
        }
        $level = 2;
        $comment_list = $this->getCommentListByUpId($up_id,$level,$offset,$limit);
        
        return $comment_list;
    }
    /**
     * 通过相册album_id获取相册权限
     * @param int $album_ids
     * 
     * @return bool
     */
    private function get_grant_by_album_id($album_ids) {
        if(empty($album_ids)) {
            return false;
        }
        //获取相关相册的权限信息
        $mAlbumClassGrants = ClsFactory::Create('Model.Album.mAlbumClassGrants');
        //三维
        $grant_list = $mAlbumClassGrants->getAlbumClassGrantByAlbumId($album_ids);
        if(empty($grant_list)) {
            return false;
        }
        //二维
        $new_grant_list = array();
        foreach($grant_list as $album_id=>$grant_list) {
            list($grant_id,$grant_info) = each($grant_list);
            $grant_info['grant_name'] = $this->grant_arr($grant_info['grant']);
            $new_grant_list[$grant_id]=$grant_info;
            unset($grant_list[$album_id]);
        }
        
        return !empty($new_grant_list) ? $new_grant_list : false;
    }
    
    /**
     * 相册权限常量                                  提取到公共配置文件中*******************************************
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
        if(empty($grant_id) && $grant_id==null) {
            return $grant_arr;
        }
        
        return $grant_arr[$grant_id];
    }
    
    
    /**
     * 班级是否存在
     * @param int $class_code
     * 
     * @return bool
     */
    private function class_is_exist($class_code) {
        if(empty($class_code)) {
            return false;
        }
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $class_info = $mClassInfo->getClassInfoBaseById($class_code);
        if(empty($class_info)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 初始化班级相册关系
     * @param $datas
     * 
     * @return int 相册关系ID
     */
    private function add_rel($datas, $class_code) {
        if(empty($datas) || !is_array($datas) || empty($class_code)) {
            return false;
        }
        
        $datas = $this->format_rel_data($datas, $class_code);
        
        $mAlbumClassRelation = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        return $mAlbumClassRelation->addAlbumClassRel($datas, true);
    }
    
    /**
     * 从关系得到相册album_id
     * @param $class_code
     * @param $offset
     * @param $limit
     * 
     * @return Array
     */
    private function get_album_ids_by_rel($class_code, $offset = null, $limit = null) {
        if(empty($class_code)) {
            return false;
        }
        
        $mAlbumClassRelation = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        $rel_list = $mAlbumClassRelation->getAlbumClassRelByClassCode($class_code, $offset, $limit);
        $rel_list = reset($rel_list);
        $album_ids_list = array();
        if(empty($rel_list)) {
            return false;
        }
        foreach($rel_list as $key=>$val) {
            $album_ids_list[$val['album_id']] = $val['album_id'];
        }
        unset($rel_list);
        
        return $album_ids_list;
    }
    /**
     * 格式化相册关系信息
     * @param Array $data
     * 
     * @return Array
     */
    private function format_rel_data($data, $class_code) {
        if(empty($data) || empty($class_code)) {
            return false;
        }
        
        $rel_data = array(
            'class_code' => $class_code,
            'album_id'   => $data['album_id'],
        );
        
        return !empty($rel_data) ? $rel_data : false;
    }
    
    /**
     * 删除班级相册关系
     * @param $rel_id
     * 
     * @return bool
     */
    private function del_rel($rel_id) {
        if(empty($rel_id)) {
            return false;
        }
        
        $mAlbumClassRelation = ClsFactory::Create('Model.Album.mAlbumClassRelation');
        return $mAlbumClassRelation->delAlbumClassRelById($rel_id);
    }
    
    /**
     * 初始化班级相册权限
     * @param Array $datas
     * 
     * @return bool || int 权限ID
     */
    private function add_grant($datas, $class_code) {
        if(empty($datas) || !is_array($datas) || empty($class_code)) {
            return false;
        }
        
        $datas = $this->format_grant_data($datas, $class_code);
        
        $mAlbumClassGrants = ClsFactory::Create('Model.Album.mAlbumClassGrants');
        $album_class_grant_id = $mAlbumClassGrants->addAlbumClassGrant($datas, true);
        
        return !empty($album_class_grant_id) ? $album_class_grant_id : false;
    }
    
    /**
     * 格式化相册权限信息
     * @param $data
     * 
     * @return Array
     */
    private function format_grant_data($data, $class_code) {
        if(empty($data) || empty($class_code)) {
            return false;
        }
        
        //相册权限初始化
        $grant_data = array(
            'class_code' => $class_code,
            'album_id'   => $data['album_id'],
            'grant'      => $data['grant']
        );
        
        return $grant_data;
    }
    
    /**
     * 删除相册权限
     * @param int $grant_id
     * 
     * @return bool
     */
    private function del_grant($grant_id) {
        if(empty($grant_id)) {
            return false;
        }
        $mAlbumClassGrants = ClsFactory::Create('Model.Album.mAlbumClassGrants');
        return $mAlbumClassGrants->delAlbumClassGrantById($grant_id);
    }
    
    /**
     * 合并相册和相册权限信息
     * @param $album_list
     * @param $grant_list
     * 
     * @return Array
     */
    private function merge_album_rel_data($album_list, $grant_list) {
        if(empty($album_list) || empty($grant_list)) {
            return false;
        }
        $new_grant_list = array();
        foreach($grant_list as $grant_key=>$grant_val) {
            $new_grant_list[$grant_val['album_id']]['grant_name'] = $grant_val['grant_name'];
            $new_grant_list[$grant_val['album_id']]['grant'] = $grant_val['grant'];
            unset($grant_key);
        }
        //数据处理，将相册权限信息和相册信息合并
        foreach($album_list as $album_id=>$val) {
            $new_grant_list[$album_id]['grant'] = !empty($new_grant_list[$album_id]['grant']) ? $new_grant_list[$album_id]['grant'] : 0;
            $album_list[$album_id] = array_merge($album_list[$album_id],$new_grant_list[$album_id]);
        }
        unset($grant_list);
        
        return !empty($album_list) ? $album_list : false;
    }
    /**
     * 去空
     * @param Array $data
     * 
     * @return bool || Array
     */
    private function remove_empty($data) {
        if(empty($data)) {
            return false;
        }
        foreach($data as $key=>$val) {
            if(empty($val)) {
                unset($data[$key]);
            }
        }
        
        return !empty($data) ? $data : false;
    }
    
}