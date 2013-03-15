<?php
class ClassAction extends SnsController{
    public function _initialize() {
        parent::_initialize();
        import('@.Control.Api.AlbumImpl.ByClass');
    }
    
    public function albumlist() {
        $class_code = $this->objInput->getInt('class_code');
        $page = $this->objInput->getInt('page');
        
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
        $img_file_url = '/Public/wmw_images/auto_photo_img/wzp.jpg';
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('client_account', $this->user['client_account']);
        $this->assign('no_photo_img', $img_file_url);
       
        $this->display('class_list_album');
    }
    /**
     * 上传照片页面
     */
    public function uplaodPhoto() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $class_code = $this->check_class_code($class_code);
        if(empty($class_code)) {
            echo "班级不存在！";exit;
        }
        
        import("@.Control/Api/AlbumImpl/ByClass");
        $ByClass = new ByClass();
        
        $album_list = $ByClass->getOnlyAlbumListByClassCode($class_code);
        
        //获取上传时的密钥处理
        import('@.Control.Sns.Album.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        $secret_key = $UploadSecretKeyObject->getSecretkey($this->user['client_account']);
        
        $this->assign('secret_key', $secret_key);
        $this->assign('album_id',$album_id);      
        $this->assign('album_list',$album_list);
        $this->assign('client_account', $this->user['client_account']);
        $this->assign('class_code', $class_code);
        
        $this->display('class_upload');
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