<?php
import('@.Control.Api.AlbumImpl.PhotoComments');
/**
 * 相片的相关数据操作
 * 
 * @author sailong
 *
 */
class PhotoInfo {
    
    protected $_mAlbumPhotos = null;
    protected $_PhotoComments = null;
    
    public function __construct() {
        $this->_mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        $this->_PhotoComments = new PhotoComments();
    }
    
    /**
     * 添加照片
     * @param array $dataarr
     * @param boolean $is_return_id 是否返回添加ID
     * 
     * @return int photo_id
     */
    public function addPhoto($photo_data) {
        if(empty($photo_data) || !is_array($photo_data)) {
            return false;
        }
        $photo_data = $this->init_photo_data($photo_data);
  
        $photo_id = $this->_mAlbumPhotos->addPhoto($photo_data, true);
        if(empty($photo_id)) {
            return false;
        }
        //相册表中添加相片数和制定最新相册封面
        $upd_data['album_auto_img'] = "{$photo_data['file_small']}";
        $this->updateAlbumPhotoCountByAlbumId($photo_data['album_id'],$upd_data);
        
        return $photo_id;
    }
    
    /**
     * 根据照片ID修改照片信息
     * @param array $dataarr
     * @param int   $photo_id
     * 
     * @return 影响行数
     */
    public function updPhoto($dataarr, $photo_id) {
        if(empty($dataarr) || empty($photo_id)) {
            return false;
        }
        //检测是否存在信息
        $photo_list = $this->getPhotoByPhotoId($photo_id);
        if(empty($photo_list)) {
            return false;
        }
        $effect = $this->_mAlbumPhotos->modifyPhotoByPhotoId($dataarr,$photo_id);
        if(!empty($dataarr['album_id']) && !empty($effect)) {
            $upd_data['album_auto_img'] = "{$photo_list[$photo_id]['file_small']}";
            $this->updateAlbumPhotoCountByAlbumId($dataarr['album_id'],$upd_data);
            $this->updateAlbumPhotoCountByAlbumId($photo_list[$photo_id]['album_id']);
        }
        return $effect;
    }
    
    
    /**
     * 根据相片photo_id删除相片信息
     * @param int $photo_id
     * 
     * @return 影响行数
     */
    public function delPhotoByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        //检测是否存在信息
        $photo_list = $this->getPhotoByPhotoId($photo_id);
        if(empty($photo_list)) {
            return false;
        }
        //检测是否有评论，有则删
        
        $comment_list = $this->_PhotoComments->getPhotoCommentsByPhotoId($photo_id, 0, 1000);
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment_val) {
                $this->_PhotoComments->delPhotoComments($comment_id);
            }
        }
        //删除相片记录
        $rs = $this->_mAlbumPhotos->delPhotosByPhotoId($photo_id);
        if($rs) {
           //删除实体
            $this->updateAlbumPhotoCountByAlbumId($photo_list[$photo_id]['album_id']);
            $this->del_photo_entity($photo_list); 
        }
        return $rs;
    }
    
    /**
     * 根据相册album_id获取相片列表
     * @param int $album_id
     * @param int $offset
     * @param int $limit
     * 
     * @return array $photo_list
     */
    
    public function getPhotoListByAlbumId($album_id, $offset=null, $limit=null) {
        if(empty($album_id)) {
            return false;
        }
        
        $photo_list = $this->_mAlbumPhotos->getByAlbumId($album_id, $offset, $limit);
        if(empty($photo_list)) {
            return false;
        } 
        $photo_list = $this->formatPhotoDate($photo_list);
        
        return $photo_list;
    }
    
    /**
     * 根据相片ID获取相片信息
     * @param int $album_id
     * 
     * @return array $photo_arr
     **/
    public function getPhotoByPhotoId($photo_ids) {
        if(empty($photo_ids)) {
            return false;
        }
        
        $photo_list = $this->_mAlbumPhotos->getPhotosByPhotoId($photo_ids);
        if(empty($photo_list)) {
            return false;
        } 
        $photo_list = $this->formatPhotoDate($photo_list);
        
        return $photo_list;
    }
    
    /**
     * 格式化相片数据
     */
    public function formatPhotoDate($photo_list) {
        if(empty($photo_list)) {
            return false;
        }
        //图片路径补全
        import("@.Common_wmw.Pathmanagement_sns");
        foreach($photo_list as $photo_id=>$photo_val) {
            $img_path = Pathmanagement_sns::getAlbum($photo_val['upd_account']);
            if(!empty($photo_val['file_big'])) {
                $photo_val['file_big_url'] = $img_path.$photo_val['file_big'];
            }
            if(!empty($photo_val['file_middle'])) {
                $photo_val['file_middle_url'] = $img_path.$photo_val['file_middle'];
            }
            if(!empty($photo_val['file_small'])) {
                $photo_val['file_small_url'] = $img_path.$photo_val['file_small'];
            }
            $photo_val['upd_date'] = date('Y-m-d', $photo_val['upd_time']);
            $photo_val['add_date'] = date('Y-m-d', $photo_val['upd_time']);
            $photo_list[$photo_id] = $photo_val;
            $uids[$photo_val['upd_account']] = $photo_val['upd_account'];
            
        }
        //补全上传照片用户信息
        $user_list = $this->getClientInfoByClientAccount($uids);
        
        if(!empty($user_list)) {
           foreach($user_list as $uid=>$user) {
               $user_list[$uid] = array(
                   'client_name' => $user['client_name'],
                   'client_client_headimg_url' => $user['client_headimg_url']
               );
           }
           foreach($photo_list as $photo_id => $photo_val) {
               $uid = $photo_val['upd_account'];
               if(isset($user_list[$uid])) {
                   $photo_list[$photo_id] = array_merge($photo_val,(array)$user_list[$uid]);
               }
           }
        }        
        
        return $photo_list;
    }
    
    //获取账号信息 
    private function getClientInfoByClientAccount($client_account_arr) {
        if(empty($client_account_arr) || !is_array($client_account_arr)) {
            return false;
        }
        $mUser = ClsFactory::Create('Model.mUser');
        $client_list = $mUser->getClientAccountById($client_account_arr);
        if(empty($client_list)) {
            return false;
        }
        import("@.Common_wmw.Pathmanagement_sns");
        foreach($client_list as $client_acount=>$client_info) {
            $img_path = Pathmanagement_sns::getHeadImg($client_acount);
            $head_img_url =$img_path.$client_info['client_headimg'];
            if(!file_exists( WEB_ROOT_DIR.$head_img_url)){
                $head_img_url = IMG_SERVER.'/Public/uc/images/user_headpic/head_pic.jpg';
            }
            $client_info['head_img'] = $head_img_url;
            $client_list[$client_acount] = $client_info;
        }
        
        return $client_list;
    }
    //更新相册中照片数 
    //再添加相片，删除相片，更新相片时都会调用此方法
    public function updateAlbumPhotoCountByAlbumId($album_id,$template = array()) {
        if(empty($album_id)) {
            return false;
        }
        //获取照片数
        $photo_count = $this->_mAlbumPhotos->getCountByAlbumId($album_id);
        
        //更新相册表
        $data_arr = array(
            'photo_num'=>$photo_count
        );
        if(!empty($template)) {
            $template = (array)$template;
            $data_arr = array_merge($data_arr,$template);
        }
        $mAlbum = ClsFactory::Create('Model.Album.mAlbum');
        
        return $mAlbum->modifyAlbumByAlbumId($data_arr, $album_id);
    }

    //单张相片 异步删除相片实体
    private function del_photo_entity($photo_list) {
        if(empty($photo_list)) {
            return false;
        }
        if(!is_array($photo_list) && is_int($photo_list)) {
            $photo_id = $photo_list;
            $photo_list = $this->getPhotoByPhotoId($photo_id);
        }
        $photo_id = key($photo_list);
        $this->updateAlbumPhotoCountByAlbumId($photo_list[$photo_id]['album_id']);

        import('@.Common_wmw.Pathmanagement_sns');
        $photo_path_list = array();
        foreach($photo_list as $photo_id=>$photo_val) {
            if(empty($photo_val['file_big'])){
                continue;
            }
            //字符串切割成数组
            $file_big_list = explode('.',$photo_val['file_big']);
            $photo_path_list[] = Pathmanagement_sns::uploadAlbum($photo_val['upd_account']).'/'.$photo_val['file_big'];
            $photo_path_list[] = Pathmanagement_sns::uploadAlbum($photo_val['upd_account']).'/'.$file_big_list[0] . '_s.' . $file_big_list[1];
            $photo_path_list[] = Pathmanagement_sns::uploadAlbum($photo_val['upd_account']).'/'.$file_big_list[0] . '_m.' . $file_big_list[1];
        }
       
        include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');
        $photo_path_list = serialize($photo_path_list);
       
        Gearman::send('del_photo_entity', $photo_path_list, PRIORITY_LOW);
    }
    
    
    //初始化相片信息
    private function init_photo_data($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        $current_time = time();
        $data = array(
            'album_id'    => $dataarr['album_id'],
            'name'        => $dataarr['name'],
            'file_big'    => $dataarr['file_big'],
            'file_middle' => $dataarr['file_middle'],
            'file_small'  => $dataarr['file_small'],
            'description' => $dataarr['description'],
            'comments'    => $dataarr['comments'],
            'upd_account' => $dataarr['upd_account'],
            'upd_time'    => $current_time
        );
        
        return $data;
    }
    
    public function getPhotos($offset, $limit) {
      $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
      return $mAlbumPhotos->getPhotos($offset, $limit);
    }
    public function getAllCount() {
      $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
      return $mAlbumPhotos->getAllCount();
    }
}