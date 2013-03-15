<?php
class ClassphotoAction extends SnsController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->display('index');
    }
    
    /**
     * 班级相册列表
     */
    public function photolist() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        
        if(empty($class_code) || empty($album_id)) {
            echo "数据错误";die;
        }
        if(empty($client_account)) {
            $client_account = $this->user['client_account'];
        }
        
        $class_code = $this->check_class_code($class_code);
        if(empty($class_code)) {
            echo "班级不存在！";exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
        if(in_array($class_role, array(1,2,3))) {
            $is_edit = true;
        }
        
        $login_account = $this->user['client_account'];
        if(empty($client_account)) {
            $client_account = $login_account;
        }
        
        import("@.Control/Api/AlbumImpl/ByClass");
        $ByClass = new ByClass();
        
        $album_list = $ByClass->getAlbumByClassAlbumId($album_id, $class_code);
        $photo_count = $ByClass->getClassPhotoCountByAlbumId($album_id);
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
        $this->assign('class_code', $class_code);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('class_list_photo');
    }
    
    //瀑布流
    public function photoPlist() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        
        if(empty($class_code) || empty($album_id)) {
            echo "数据错误";die;
        }
        $login_account = $this->user['client_account'];
        if(empty($client_account)) {
            $client_account = $login_account;
        }
        
        $class_code = $this->check_class_code($class_code);
        if(empty($class_code)) {
            echo "班级不存在！";exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
        if(in_array($class_role, array(1,2,3))) {
            $is_edit = true;
        }
        
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('class_list_photo_p');
    }
    
    
    /**
     * 单张照片
     */
    public function photo() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $photo_id = $this->objInput->getInt('photo_id');
        $client_account = $this->objInput->getInt('client_account');
        
        if(empty($class_code) || empty($album_id)) {
            echo "数据错误";die;
        }
        
        $login_account = $this->user['client_account'];
        if(empty($client_account)) {
            $client_account = $login_account;
        }
        
        $class_code = $this->check_class_code($class_code);
        if(empty($class_code)) {
            echo "班级不存在！";exit;
        }
        
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
        if(in_array($class_role, array(1,2,3))) {
            $is_edit = 'true';
        }
        import("@.Control/Api/AlbumImpl/ByClass");
        $ByClass = new ByClass();
        
        $albumInfo = $ByClass->getAlbumByClassAlbumId($album_id,$class_code);
        
        $photolist = $ByClass->getClassPhotoListByAlbumId($album_id);
  
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
        $img_path = Pathmanagement_sns::getHeadImg($this->user['client_account']);
        $head_img_url =$img_path.$this->user['client_headimg'];
        if(!file_exists( WEB_ROOT_DIR.$head_img_url)){
            $head_img_url = '/Public/uc/images/user_headpic/head_pic.jpg';
        }
        
        $this->assign('first_img', $first_img);
        $this->assign('photo_list', $photolist);
        $this->assign('album', $albumInfo[$album_id]);
        
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('photo_id', $photo_id);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        $this->assign('client_name', $this->user['client_name']);
        $this->assign('head_img', $head_img_url);
        
        $this->display('class_photo_show');
    }
	/**
     * 检测班级编号是否存在
     * @param int $class_code
     * 
     * @return  $class_code为存在，false为不存在
     */
    private function check_class_code($class_code) {
        //获取当前用户所有班级编号
        $class_codes = array_keys($this->user['client_class']);
        if(empty($class_codes)) {
            return false;
        }
        if(in_array($class_code, $class_codes)) {
            return $class_code;
        }else{
            //获取默认班级编号
            $class_code = reset($class_codes);
        }
        
        return $class_code;
    }
}
