<?php
/**
 * 相册的相关数据操作
 * @add 相册的添加
 * @delete 相册的删除
 * @author sailong
 *
 */
class AlbumInfo {
    
    protected $_mAlbum = null;
    
    
    public function __construct() {
        $this->_mAlbum = ClsFactory::Create('Model.Album.mAlbum');
    }
    
    /**
     * 添加相册
     * @param $datas
     * 
     * @return album_id
     */
    public function addAlbum($album_list) {
        if(empty($album_list) || !is_array($album_list)) {
            return false;
        }
        $album_list = $this->initAlbum($album_list);
        
        $album_id = $this->_mAlbum->addAlbum($album_list, true);
        
        return $album_id;
    }
    
    /**
     * 删除相册
     * @param $album_id
     * 
     * @return 影响行数
     */
    public function delAlbum($album_id) {
        if(empty($album_id)) {
            return false;
        }
        
        $affect_rows = $this->_mAlbum->delAlbumByAlbumId($album_id);
        
        return $affect_rows;
    }
    
    /**
     * 获取相册信息
     * @param int $album_id
     * 
     * @return array 
     */
    public function getAlbum($album_ids) {
        if(empty($album_ids)) {
            return false;
        }
      
        $album_list = $this->_mAlbum->getAlbumByAlbumId($album_ids);
        if(empty($album_list)) {
            return false;
        }
        //格式化相册信息
        $album_list = $this->formatAlbum($album_list);
        
        return !empty($album_list) ? $album_list : false;
    }
    
    /**
     * 根据相册ID修改相册信息
     * @param array $data
     * @param int $album_id
     * 
     * @return 影响行数
     */    
    public function updAlbum($data, $album_id) {
        if(empty($data) || empty($album_id)) {
            return false;
        }
        
        return $this->_mAlbum->modifyAlbumByAlbumId($data, $album_id);
    }
    
     /**
     * 格式化相册信息
     * 
     * @return $album_list
     */
    private function formatAlbum($album_list) {
        if(empty($album_list)) {
            return false;
        }
        
        //解析相册封面信息
        import("@.Common_wmw.Pathmanagement_sns");
        foreach($album_list as $album_id=>$album_val) {
            $img_path = Pathmanagement_sns::getAlbum($album_val['add_account']);
            $img_file_url = IMG_SERVER.'/Public/wmw_images/auto_photo_img/wfm.jpg';
            if(empty($album_val['photo_num'])) {
                //无封面
                $img_file_url = IMG_SERVER.'/Public/wmw_images/auto_photo_img/wzp.jpg';
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
            $album_val['add_date'] = date('Y-m-d',$album_val['add_time']);
            $album_val['upd_date'] = date('Y-m-d',$album_val['upd_time']);
            $album_val['album_img_path'] = $img_file_url;
            $album_list[$album_id] = $album_val;
            
            //获取相册添加人账号信息
            $client_account_list[$album_val['add_account']] = $album_val['add_account'];
        }
        
        //解析添加相册用户信息
        if(!empty($client_account_list)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $client_list = $mUser->getClientAccountById($client_account_list);
            if(empty($client_list)) {
                return false;
            }
            //解析用户头像信息
            foreach($client_list as $client_acount=>$client_info) {
                $img_path = Pathmanagement_sns::getHeadImg($client_acount);
                $head_img_url =$img_path.$client_info['client_headimg'];
                if(!file_exists( WEB_ROOT_DIR.$head_img_url)){
                    $head_img_url = IMG_SERVER.'/Public/uc/images/user_headpic/head_pic.jpg';
                }
                $client_info['head_img'] = $head_img_url;
                $client_list[$client_acount] = $client_info;
            }
            
            foreach($album_list as $album_id=>$album_val) {
                $album_list[$album_id]['client_name'] = $client_list[$album_val['add_account']]['client_name'];
            }
            unset($client_list);
        }
        
        return $album_list;
    }
    
    /**
     * 初始化相册信息
     * 
     * @return $album_list
     */
    private function initAlbum($album_list) {
        if(empty($album_list)) {
            return false;
        }
        $current_time = time();
        $data = array(
            'album_name'    => $album_list['album_name'],
            'album_explain' => $album_list['album_explain'],
            'album_img'		=> '',
            'add_account'   => $album_list['uid'],
            'add_time'      => $current_time,
            'upd_account'   => $album_list['uid'],
            'upd_time'      => $current_time,
            'album_auto_img' => '',
            'photo_num'     => 0
        );
        unset($album_list);      
        
        return $data;
    }
}