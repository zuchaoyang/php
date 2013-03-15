<?php
class PersonphotoAction extends SnsController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->display('index');
    }
    
    /**
     * 用户相册列表
     */
    public function photolist() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        
        if(empty($client_account) || empty($album_id)) {
            echo "数据错误";die;
        }
        
        $is_true = $this->check_client_account($client_account);
        if(!$is_true) {
            echo "用户不存在！";exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
       
        if($client_account == $login_account) {
            $is_edit = true;
        }
        
        import("@.Control/Api/AlbumImpl/ByPerson");
        $ByPerson = new ByPerson();
        
        $album_list = $ByPerson->getAlbumByPersonAlbumId($album_id, $client_account);
        $photo_count = $ByPerson->getPersonPhotoCountByAlbumId($album_id);
        if(empty($photo_count)) {
            $photo_count = 0;
        }
        foreach($album_list as $album_id_key=>$album_info) {
            $album_info['count'] = $photo_count;
            $album_info['add_date'] = date('Y-m-d', $album_info['add_time']);
            $album_info['upd_date'] = date('Y-m-d', $album_info['upd_time']);
            $album_list[$album_id_key] = $album_info;
        }
        $album_list = reset($album_list);
        
        $this->assign('album_list', $album_list);
        $this->assign('is_edit', $is_edit);
        $this->assign('client_account', $client_account);
        $this->assign('album_id', $album_id);
        $this->assign('login_account', $login_account);
        
        $this->display('person_list_photo');
    }
    
    //瀑布流
    public function photoPlist() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        if(empty($client_account) || empty($album_id)) {
            echo "数据错误";die;
        }
        
        $is_true = $this->check_client_account($client_account);
        if(!$is_true) {
            echo "用户不存在！";exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
       
        if($client_account == $login_account) {
            $is_edit = true;
        }
        
        $this->assign('is_edit', $is_edit);
        $this->assign('client_account', $client_account);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('person_list_photo_p');
    }
    
    
    /**
     * 单张照片
     */
    public function photo() {
        $client_account = $this->objInput->getInt('client_account');
        $album_id = $this->objInput->getInt('album_id');
        $photo_id = $this->objInput->getInt('photo_id');
        $login_account = $this->user['client_account'];
        
        if(empty($client_account) || empty($album_id)) {
            echo "数据错误";die;
        }
        
        $is_true = $this->check_client_account($client_account);
        if(!$is_true) {
            echo "用户不存在！";exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
       
        if($client_account == $login_account) {
            $is_edit = true;
        }
        import("@.Control/Api/AlbumImpl/ByPerson");
        $ByPerson = new ByPerson();
        
        $albumInfo = $ByPerson->getAlbumByPersonAlbumId($album_id,$client_account);
        
        $photolist = $ByPerson->getPersonPhotoListByAlbumId($album_id);
  
        foreach($photolist as $photo_id_key=>$val) {
            $path = Pathmanagement_sns::getAlbum($client_account);
            $photolist[$photo_id_key]['file_big'] = $path.$val['file_big'];
            $photolist[$photo_id_key]['file_middle'] = $path.$val['file_middle'];
            $photolist[$photo_id_key]['file_small'] = $path.$val['file_small'];
            $photolist[$photo_id_key]['upd_data'] = date('Y-m-d', $val['upd_time']);
            $photolist[$photo_id_key]['add_data'] = date('Y-m-d', $val['upd_time']);
        }
        
        $first_img = $photolist[$photo_id];
        
        
        //登录者头像信息
        $img_path = Pathmanagement_sns::getHeadImg($login_account);
        $head_img_url =$img_path.$this->user['client_headimg'];
        if(!file_exists( WEB_ROOT_DIR.$head_img_url)){
            $head_img_url = '/Public/uc/images/user_headpic/head_pic.jpg';
        }
        
        $this->assign('first_img', $first_img);
        $this->assign('photo_list', $photolist);
        $this->assign('album', $albumInfo[$album_id]);
        
        $this->assign('is_edit', $is_edit);
        $this->assign('photo_id', $photo_id);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        $this->assign('client_name', $this->user['client_name']);
        $this->assign('head_img', $head_img_url);
        
        $this->display('person_photo_show');
    }
	/**
     * 检测用户编号是否存在
     * @param int $client_account
     * 
     * @return  $client_account为存在，false为不存在
     */
    private function check_client_account($client_account) {
        if(empty($client_account)) {
            return false;
        }
        $mUserVm = ClsFactory::Create('RModel.mUserVm');
        $is_client = $mUserVm->getClientAccountById($client_account);
        if(empty($is_client)) {
            return false;
        }
        
        return true;
    }
}
