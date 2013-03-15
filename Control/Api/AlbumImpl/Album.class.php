<?php
/**
 * 相册的相关数据操作
 * @add 相册的添加
 * @delete 相册的删除
 * @author sailong
 *
 */
class Album {
    
    protected $_mAlbum = null;
    
    protected $_mAlbumPhotoComments = null;
    
    protected $_mAlbumPhotos = null;
    
    private function initmAlbum() {
        $this->_mAlbum = ClsFactory::Create('Model.Album.mAlbum');
    }
    
    private function initmAlbumPhotoComments() {
        $this->_mAlbumPhotoComments = ClsFactory::Create('Model.Album.mAlbumPhotoComments');
    }
    
    private function initmAlbumPhotos() {
        $this->_mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
    }
    /**
     * 添加相册
     * @param $datas
     * 
     * @return album_id
     */
    public function add($datas) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        $datas = $this->format_album_data($datas);
        
        $this->initmAlbum();
        $album_id = $this->_mAlbum->addAlbum($datas, true);
        
        return $album_id;
    }
    
    /**
     * 删除相册
     * @param $album_id
     * 
     * @return 影响行数
     */
    public function delete($album_id) {
        if(empty($album_id)) {
            return false;
        }
        $this->initmAlbum();
        $rs = $this->_mAlbum->delAlbumByAlbumId($album_id);
        
        return $rs;
    }
    
    /**
     * 获取相册信息
     * @param int $album_id
     * 
     * @return array 
     */
    public function get($album_id) {
        if(empty($album_id)) {
            return false;
        }
        $this->initmAlbum();
        $album_list = $this->_mAlbum->getAlbumByAlbumId($album_id);
        import("@.Common_wmw.Pathmanagement_sns");
        foreach($album_list as $album_id=>$album_val) {
            $img_path = Pathmanagement_sns::getAlbum($album_val['add_account']);
            $img_file_url = '/Public/wmw_images/auto_photo_img/wfm.jpg';
            if(empty($album_val['photo_num'])) {
                //无封面
                $img_file_url = '/Public/wmw_images/auto_photo_img/wzp.jpg';
            }elseif(!empty($album_val['album_img'])) {
                //有封面
                $album_img = $img_path.$album_val['album_img'];
                if(file_exists(WEB_ROOT_DIR.$album_img)) {
                    $img_file_url = $album_img;
                }
            }elseif(!empty($album_val['album_auto_img'])) {
                //随机封面
                $album_auto_img = $img_path.$album_val['album_auto_img'];
                
                if(file_exists(WEB_ROOT_DIR.$album_auto_img)) {
                    $img_file_url = $album_auto_img;
                }
            }
             
            $album_val['album_img_path'] = $img_file_url;
            $album_list[$album_id] = $album_val;
        }
        return !empty($album_list) ? $album_list : false;
    }
    
    /**
     * 根据相册ID修改相册信息
     * @param array $data
     * @param int $album_id
     * 
     * @return 影响行数
     */    
    public function upd($data, $album_id) {
        if(empty($data) || empty($album_id)) {
            return false;
        }
        $this->initmAlbum();
        return $this->_mAlbum->modifyAlbumByAlbumId($data, $album_id);
    }
    
    /**
     * 添加照片
     * @param array $dataarr
     * @param boolean $is_return_id 是否返回添加ID
     * 
     * @return int photo_id
     */
    public function addPhoto($dataarr, $is_return_id) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $dataarr = $this->format_photo_data($dataarr);
  
        $this->initmAlbumPhotos();
        $rs = $this->_mAlbumPhotos->addPhoto($dataarr, true);
        if(empty($rs)) {
            return false;
        }
        //相册表中添加相片数和制定最新相册封面
        $upd_data['album_auto_img'] = "{$dataarr['file_small']}";
        $this->updateAlbumPhotoCountByAlbumId($dataarr['album_id'],$upd_data);
        
        return $rs;
    }
    
    /**
     * 根据照片ID修改照片信息
     * @param array $dataarr
     * @param int   $photo_id
     * 
     * @return 影响行数
     */
    public function updPhotoByPhotoId($dataarr, $photo_id) {
        if(empty($dataarr) || empty($photo_id)) {
            return false;
        }
        $this->initmAlbumPhotos();
        return $this->_mAlbumPhotos->modifyPhotoByPhotoId($dataarr,$photo_id);
    }
    
    /**
     * 根据相册album_id删除相片信息
     * @param int $album_id
     * 
     * @return 影响行数
     */
    /*public function delPhotoByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        $this->initmAlbumPhotos();
        $photo_list = $this->_mAlbumPhotos->getPhotoListByAlbumId($album_id);
        $photo_ids = array_keys($photo_list);
        if(!empty($photo_ids)) {
        	return $this->delPhotoByPhotoId($photo_ids);
        }
        return true;
        //return $this->_mAlbumPhotos->delByAlbumId($album_id);
    }*/
    
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
        $this->initmAlbumPhotoComments();
        $comment_list = $this->_mAlbumPhotoComments->getAlbumPhotoCommentByPhotoId($photo_id);
        if(!empty($comment_list)) {
            $this->_mAlbumPhotoComments->delByPhotoId($photo_id);
        }
        
        //删除相片记录
        $this->initmAlbumPhotos();
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
        
        $this->initmAlbumPhotos();
        
        return $this->_mAlbumPhotos->getByAlbumId($album_id, $offset, $limit);
    }
    
    /**
     * 根据相册id获取相册中相片的数量
     * @param int $album_id
     * 
     * @return int $count
     */
    public function getPhotoCountByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        $this->initmAlbumPhotos();
        
        return $this->_mAlbumPhotos->getCountByAlbumId($album_id);
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
        $this->initmAlbumPhotos();

        return $this->_mAlbumPhotos->getPhotosByPhotoId($photo_ids);
    }
    
    /**
     * 添加相册评论
     * @param array $dataarr
     * @param boolean $is_return_id 是否返回添加ID
     * 
     * @return comment_id
     */
    public function addComment($dataarr, $is_return_id) {
        if(empty($dataarr)) {
            return false;
        }
        $this->initmAlbumPhotoComments();
        
        $rs = $this->_mAlbumPhotoComments->addAlbumPhotoComment($dataarr, $is_return_id);
        if($rs) {
            $this->updatePhotoCommentCountByPhotoId($dataarr['photo_id']);
        }
        
        return $rs;
    }
    
    /**
     * 根据相片photo_id删除评论信息
     * @param int $photo_id
     * 
     * @return 影响行数
     */
    public function delCommentByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        
        $this->initmAlbumPhotoComments();
        $rs = $this->_mAlbumPhotoComments->delByPhotoId($photo_id);
        if(empty($rs)) {
            return false;
        }
        return true;
    }
    
    /**
     * 根据评论comment_id删除评论信息
     * @param int $comment_id
     * 
     * @return 影响行数
     */
    public function delCommentByCommnetId($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        $this->initmAlbumPhotoComments();
        $comment_list = $this->_mAlbumPhotoComments->getCommentByCommentId($comment_id);
        if(empty($comment_list)) {
            return false;
        }
        $comment_2nd_list = $this->_mAlbumPhotoComments->getAlbumPhotoCommentByUpId($comment_id,2);
        if(!empty($comment_2nd_list)){
            $this->_mAlbumPhotoComments->delByUpId($comment_id);
        }
        $rs = $this->_mAlbumPhotoComments->delCommentByCommentId($comment_id);
        if($rs) {
            $this->updatePhotoCommentCountByPhotoId($comment_list[$comment_id]['photo_id']);
        }
        
        return $rs;
    }
    
    /**
     * 根据相片up_id获取评论列表
     * @param int $up_id
     * 
     * @return array 
     */
    public function getCommentListByUpId($up_id,$level,$offset=null,$limit=null) {
        if(empty($up_id)) {
            return false;
        }
        if(!in_array($level,array(1,2))) {
            $level = 1;
        }
        $this->initmAlbumPhotoComments();
       
        $comment_list = $this->_mAlbumPhotoComments->getAlbumPhotoCommentByUpId($up_id,$level,$offset,$limit);
        if(empty($comment_list)) {
            return false;
        }
        $client_account_arr = array();
        import('@.Common_wmw.WmwFace');
        foreach($comment_list as $commnet_id=>$comment_info) {
            $client_account_arr[$comment_info['client_account']] = $comment_info['client_account'];
            $comment_info['add_date'] = date('Y-m-d H:i:s',$comment_info['add_time']);
            $comment_info['content'] = WmwFace::parseFace($comment_info['content']);
            $comment_list[$commnet_id] = $comment_info;
        }
        //获取评论人信息
        $client_account_list = $this->getClientInfoByClientAccount($client_account_arr);
        if(empty($client_account_list)) {
            return false;
        }
        
        foreach($comment_list as $commnet_id=>$comment_info) {
            $client_info = $client_account_list[$comment_info['client_account']];
            $comment_info['client_name'] = $client_info['client_name'];
            $comment_info['client_head_img'] = $client_info['head_img'];
            $comment_list[$commnet_id] = $comment_info;
        }
        unset($client_account_list);
        
        return $comment_list;
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
                $head_img_url = '/Public/uc/images/user_headpic/head_pic.jpg';
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
        $this->initmAlbumPhotos();
        $photo_count = $this->_mAlbumPhotos->getCountByAlbumId($album_id);
        
        //更新相册表
        $this->initmAlbum();
        $data_arr = array(
            'photo_num'=>$photo_count
        );
        if(!empty($template)) {
            $template = (array)$template;
            $data_arr = array_merge($data_arr,$template);
        }
        return $this->_mAlbum->modifyAlbumByAlbumId($data_arr, $album_id);
    }
    //更新照片评论数
    //再添加评论，删除评论时调用此方法
    public function updatePhotoCommentCountByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        //获取相片评论数
        $this->initmAlbumPhotoComments();
        $comment_count = $this->_mAlbumPhotoComments->getCountByPhotoId($photo_id);
        //更新相片评论数
        $this->initmAlbumPhotos();
        $data_arr = array(
            'comments'=>$comment_count
        );
        return $this->_mAlbumPhotos->modifyPhotoByPhotoId($data_arr,$photo_id);
    }
    //单张相片 异步删除相片实体
    private function del_photo_entity($photo_list) {
        if(empty($photo_list)) {
            return false;
        }
        
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
    private function format_photo_data($dataarr) {
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
    
    //初始化相册信息
    private function format_album_data($data) {
        if(empty($data)) {
            return false;
        }
        $current_time = time();
        $data = array(
            'album_name'    => $data['album_name'],
            'album_explain' => $data['album_explain'],
            'album_img'		=> '',
            'add_account'   => $data['uid'],
            'add_time'      => $current_time,
            'upd_account'   => $data['uid'],
            'upd_time'      => $current_time,
            'album_auto_img' => '',
            'photo_num'     => 0
        );
              
        return $data;
    }
    
    
    
}